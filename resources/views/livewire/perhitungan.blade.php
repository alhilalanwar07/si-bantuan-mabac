<?php

use App\Models\Alternatif;
use App\Models\Hasil;
use App\Models\Kriteria;
use App\Models\Kriteriaalternatif;
use App\Models\Periode;
use App\Models\Subkriteria;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component {

    public $alternatif;
    public $kriteria;
    public $kriteriaalternatif;
    public $subkriteria;
    public $results = [
        'decisionMatrix' => [],
        'normalizedMatrix' => [],
        'weightedMatrix' => [],
        'borderAreaMatrix' => [],
        'distanceMatrix' => [],
        'ranking' => []
    ];

    public function mount()
    {
        $penerima_ids = Hasil::where('status', 'penerima')->pluck('alternatif_id')->toArray();
        $this->alternatif = Alternatif::whereNotIn('id', $penerima_ids)
            ->get();
        $this->kriteriaalternatif = Kriteriaalternatif::all();
        $this->kriteria = Kriteria::all();
        $this->subkriteria = Subkriteria::all();
    }

    public function with(): array
    {
        // dd($this->getMabacResults());
        return [
            'alternatif' => $this->alternatif,
            'kriteriaalternatif' => $this->kriteriaalternatif,
            'kriteria' => $this->kriteria,
            'subkriteria' => $this->subkriteria,
            'results' => $this->getMabacResults(),
            'decisionMatrix' => $this->getMabacResults()['decisionMatrix'],
            'normalizedMatrix' => $this->getMabacResults()['normalizedMatrix'],
            'weightedMatrix' => $this->getMabacResults()['weightedMatrix'],
            'borderAreaMatrix' => $this->getMabacResults()['borderAreaMatrix'],
            'distanceMatrix' => $this->getMabacResults()['distanceMatrix'],
            'ranking' => $this->getMabacResults()['ranking']
        ];
    }

    public function calculateDecisionMatrix()
    {
        $matrix = [];
        if ($this->alternatif->isEmpty() || $this->kriteria->isEmpty()) {
            return $matrix;
        }

        // Initialize the matrix with zeros for all alternatif-kriteria combinations
        foreach ($this->alternatif as $alternatif) {
            foreach ($this->kriteria as $kriteria) {
                $matrix[$alternatif->id][$kriteria->id] = 0;
            }
        }

        // Then fill in actual values where they exist
        foreach ($this->alternatif as $alternatif) {
            foreach ($this->kriteria as $kriteria) {
                $nilai = Kriteriaalternatif::join('subkriterias', 'kriteriaalternatifs.subkriteria_id', '=', 'subkriterias.id')
                    ->where('kriteriaalternatifs.alternatif_id', $alternatif->id)
                    ->where('subkriterias.kriteria_id', $kriteria->id)
                    ->select('subkriterias.bobot as nilai')  // Use the subkriteria's bobot as nilai
                    ->first();

                if ($nilai) {
                    $matrix[$alternatif->id][$kriteria->id] = $nilai->nilai;
                }
            }
        }

        return $matrix;
    }

    public function calculateNormalizedMatrix($matrix)
    {
        $normalized = [];
        if (empty($matrix) || $this->alternatif->isEmpty() || $this->kriteria->isEmpty()) {
            return $normalized;
        }

        foreach ($this->kriteria as $kriteria) {
            // Properly collect values for this criteria from each alternative
            $values = [];
            foreach ($this->alternatif as $alternatif) {
                if (isset($matrix[$alternatif->id][$kriteria->id])) {
                    $values[] = $matrix[$alternatif->id][$kriteria->id];
                }
            }

            if (empty($values)) {
                continue;
            }

            $maxValue = max($values);
            $minValue = min($values);

            foreach ($this->alternatif as $alternatif) {
                if (!isset($matrix[$alternatif->id][$kriteria->id])) {
                    $normalized[$alternatif->id][$kriteria->id] = 0;
                    continue;
                }

                if ($maxValue == $minValue) {
                    $normalized[$alternatif->id][$kriteria->id] = 0;
                } else {
                    if ($kriteria->tipe == 'benefit') {
                        $normalized[$alternatif->id][$kriteria->id] =
                            ($matrix[$alternatif->id][$kriteria->id] - $minValue) / ($maxValue - $minValue);
                    } else {
                        $normalized[$alternatif->id][$kriteria->id] =
                            ($maxValue - $matrix[$alternatif->id][$kriteria->id]) / ($maxValue - $minValue);
                    }
                }
            }
        }
        return $normalized;
    }

    public function calculateWeightedMatrix($normalized)
    {
        $weighted = [];
        if (empty($normalized) || $this->alternatif->isEmpty() || $this->kriteria->isEmpty()) {
            return $weighted;
        }

        foreach ($this->alternatif as $alternatif) {
            foreach ($this->kriteria as $kriteria) {
                if (!isset($normalized[$alternatif->id][$kriteria->id])) {
                    $weighted[$alternatif->id][$kriteria->id] = 0;
                    continue;
                }

                $weighted[$alternatif->id][$kriteria->id] =
                    $kriteria->bobot * $normalized[$alternatif->id][$kriteria->id] + $kriteria->bobot;
            }
        }
        return $weighted;
    }

    public function calculateBorderAreaMatrix($weighted)
    {
        $borderArea = [];
        if (empty($weighted) || $this->alternatif->isEmpty() || $this->kriteria->isEmpty()) {
            return $borderArea;
        }

        $altCount = count($this->alternatif);
        if ($altCount == 0) {
            return $borderArea;
        }

        foreach ($this->kriteria as $kriteria) {
            $sum = 0;
            $validValues = 0;

            foreach ($this->alternatif as $alternatif) {
                if (isset($weighted[$alternatif->id][$kriteria->id])) {
                    $sum += $weighted[$alternatif->id][$kriteria->id];
                    $validValues++;
                }
            }

            if ($validValues > 0) {
                $borderArea[$kriteria->id] = pow($sum, 1 / $validValues);
            } else {
                $borderArea[$kriteria->id] = 0;
            }
        }
        return $borderArea;
    }

    public function calculateDistanceMatrix($weighted, $borderArea)
    {
        $distance = [];
        if (
            empty($weighted) || empty($borderArea) ||
            $this->alternatif->isEmpty() || $this->kriteria->isEmpty()
        ) {
            return $distance;
        }

        foreach ($this->alternatif as $alternatif) {
            foreach ($this->kriteria as $kriteria) {
                if (
                    !isset($weighted[$alternatif->id][$kriteria->id]) ||
                    !isset($borderArea[$kriteria->id])
                ) {
                    $distance[$alternatif->id][$kriteria->id] = 0;
                    continue;
                }

                $distance[$alternatif->id][$kriteria->id] =
                    $weighted[$alternatif->id][$kriteria->id] - $borderArea[$kriteria->id];
            }
        }
        return $distance;
    }

    public function calculateRanking($distance)
    {
        $ranking = [];
        if (empty($distance) || $this->alternatif->isEmpty() || $this->kriteria->isEmpty()) {
            return $ranking;
        }

        foreach ($this->alternatif as $alternatif) {
            $sum = 0;
            foreach ($this->kriteria as $kriteria) {
                if (isset($distance[$alternatif->id][$kriteria->id])) {
                    $sum += $distance[$alternatif->id][$kriteria->id];
                }
            }
            $ranking[$alternatif->id] = $sum;
        }

        if (!empty($ranking)) {
            arsort($ranking);
        }
        return $ranking;
    }

    public function getMabacResults()
    {
        // Check if we have data to process
        if ($this->alternatif->isEmpty() || $this->kriteria->isEmpty()) {
            return [
                'decisionMatrix' => [],
                'normalizedMatrix' => [],
                'weightedMatrix' => [],
                'borderAreaMatrix' => [],
                'distanceMatrix' => [],
                'ranking' => []
            ];
        }

        $decisionMatrix = $this->calculateDecisionMatrix();
        $normalizedMatrix = $this->calculateNormalizedMatrix($decisionMatrix);
        $weightedMatrix = $this->calculateWeightedMatrix($normalizedMatrix);
        $borderAreaMatrix = $this->calculateBorderAreaMatrix($weightedMatrix);
        $distanceMatrix = $this->calculateDistanceMatrix($weightedMatrix, $borderAreaMatrix);
        $ranking = $this->calculateRanking($distanceMatrix);

        return [
            'decisionMatrix' => $decisionMatrix,
            'normalizedMatrix' => $normalizedMatrix,
            'weightedMatrix' => $weightedMatrix,
            'borderAreaMatrix' => $borderAreaMatrix,
            'distanceMatrix' => $distanceMatrix,
            'ranking' => $ranking
        ];
    }

    public function simpanPerhitungan()
    {
        // simpan ranking ke tabel hasil = ['alternatif_id', 'nilai', 'keterangan', 'status', 'periode_id'];
        // periode_id diambil dari periode yang aktif
        $periode = Periode::where('status', 'aktif')->first();

        try {
            DB::transaction(function () use ($periode) {
                // kosongkan dulu tabel hasil dengan status 'belum' untuk periode yang aktif
                Hasil::where('periode_id', $periode->id)
                    ->where('status', 'belum')
                    ->delete();

                $results = $this->getMabacResults();
                foreach ($results['ranking'] as $alternatifId => $nilai) {
                    Hasil::create([
                        'alternatif_id' => $alternatifId,
                        'nilai' => $nilai,
                        'keterangan' => '-',
                        'status' => 'belum',
                        'periode_id' => $periode->id
                    ]);
                }
            });

            $this->dispatch('tambahAlertToast', ['message' => 'Perhitungan berhasil disimpan.']);
            return redirect()->route('hasil');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', ['message' => 'Terjadi kesalahan saat menyimpan perhitungan: ' . $e->getMessage()]);
        }
    }
}; ?>

<div>
    <!-- 1. Matriks Keputusan -->
    <!-- 2. Normalisasi Matriks -->
    <!-- 3. Matriks Tertimbang (V) -->
    <!-- 4. Matriks Area Perkiraan Batas (G) -->
    <!-- 5. Matriks Jarak Alternatif dari Daerah Perkiraan Perbatasan (Q) -->
    <!-- 6. Perankingan Alternatif (S) -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Perhitungan Metode MABAC</div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 1. Matriks Keputusan -->
                    <h4 class="mb-3">1. Matriks Keputusan</h4>
                    <p class="text-muted">Matriks keputusan menunjukkan nilai dari setiap alternatif terhadap setiap kriteria.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th>Alternatif</th>
                                    @foreach ($kriteria as $k)
                                    <th>{{ $k->nama }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatif as $a)
                                <tr>
                                    <td>{{ $a->nama }}</td>
                                    @foreach ($kriteria as $k)
                                    <td>
                                        @php
                                        $nilai = $decisionMatrix[$a->id][$k->id] ?? 0;
                                        @endphp
                                        {{ number_format($nilai, 4) }}
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 2. Normalisasi Matriks -->
                    <h4 class="mt-5 mb-3">2. Normalisasi Matriks</h4>
                    <p class="text-muted">Normalisasi matriks untuk menyeragamkan skala dari nilai-nilai kriteria.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th>Alternatif</th>
                                    @foreach ($kriteria as $k)
                                    <th>{{ $k->nama }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatif as $a)
                                <tr>
                                    <td>{{ $a->nama }}</td>
                                    @foreach ($kriteria as $k)
                                    <td>
                                        @php
                                        $nilai = $normalizedMatrix[$a->id][$k->id] ?? 0;
                                        @endphp
                                        {{ number_format($nilai, 4) }}
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 3. Matriks Tertimbang (V) -->
                    <h4 class="mt-5 mb-3">3. Matriks Tertimbang (V)</h4>
                    <p class="text-muted">Matriks tertimbang diperoleh dari perkalian bobot kriteria dengan matriks yang telah dinormalisasi.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th>Alternatif</th>
                                    @foreach ($kriteria as $k)
                                    <th>{{ $k->nama }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatif as $a)
                                <tr>
                                    <td>{{ $a->nama }}</td>
                                    @foreach ($kriteria as $k)
                                    <td>
                                        @php
                                        $nilai = $weightedMatrix[$a->id][$k->id] ?? 0;
                                        @endphp
                                        {{ number_format($nilai, 4) }}
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 4. Matriks Area Perkiraan Batas (G) -->
                    <h4 class="mt-5 mb-3">4. Matriks Area Perkiraan Batas (G)</h4>
                    <p class="text-muted">Nilai area perkiraan batas untuk setiap kriteria.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-light">
                                    @foreach ($kriteria as $k)
                                    <th>{{ $k->nama }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach ($kriteria as $k)
                                    <td>
                                        @php
                                        $nilai = $borderAreaMatrix[$k->id] ?? 0;
                                        @endphp
                                        {{ number_format($nilai, 4) }}
                                    </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 5. Matriks Jarak Alternatif dari Daerah Perkiraan Perbatasan (Q) -->
                    <h4 class="mt-5 mb-3">5. Matriks Jarak Alternatif dari Daerah Perkiraan Perbatasan (Q)</h4>
                    <p class="text-muted">Jarak nilai alternatif dari nilai area perkiraan batas.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th>Alternatif</th>
                                    @foreach ($kriteria as $k)
                                    <th>{{ $k->nama }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alternatif as $a)
                                <tr>
                                    <td>{{ $a->nama }}</td>
                                    @foreach ($kriteria as $k)
                                    <td>
                                        @php
                                        $nilai = $distanceMatrix[$a->id][$k->id] ?? 0;
                                        @endphp
                                        {{ number_format($nilai, 4) }}
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 6. Perankingan Alternatif (S) -->
                    <h4 class="mt-5 mb-3">6. Perankingan Alternatif (S)</h4>
                    <p class="text-muted">Hasil perankingan alternatif berdasarkan nilai total dari matriks jarak.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th>Ranking</th>
                                    <th>Alternatif</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rank = 1; @endphp
                                @foreach ($ranking as $altId => $value)
                                <tr>
                                    <td>{{ $rank++ }}</td>
                                    <td>
                                        @php
                                        $alt = $alternatif->firstWhere('id', $altId);
                                        @endphp
                                        {{ $alt ? $alt->nama : 'Alternatif #' . $altId }}
                                    </td>
                                    <td>{{ number_format($value, 4) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">

                        <button type="button" class="btn btn-primary" wire:click.prevent="simpanPerhitungan">
                            <i class="fas fa-save me-2"></i>
                            Simpan Perhitungan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- alert -->
    <livewire:_alert />
</div>