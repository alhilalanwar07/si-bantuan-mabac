<?php

use Livewire\Volt\Component;
use App\Models\Hasil;
use App\Models\Alternatif;
use App\Models\Periode;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $paginate = 10;

    public $alternatif_id, $nilai, $keterangan, $status, $periode_id;
    public $selectedAlternatif, $selectedPeriode;
    public $selectedPeriodePenerima;

    public function mount()
    {
        $this->selectedAlternatif = '';
        $this->selectedPeriode = Periode::where('status', 'aktif')->first()->id ?? '';
        $this->selectedPeriodePenerima = Periode::where('status', 'aktif')->first()->id ?? '';
    }

    public function with(): array
    {
        return [
            'hasils' => Hasil::where('periode_id', $this->selectedPeriode)->where('status', 'belum')->paginate($this->paginate),
            'alternatifs' => Alternatif::all(),
            'periodes' => Periode::all(),
            'periode_penerima' => Periode::all(),
            'hasil_penerima' => Hasil::where('periode_id', $this->selectedPeriodePenerima)->where('status', 'penerima')->paginate($this->paginate)
        ];
    }

    protected $rules = [
        'alternatif_id' => 'required',
        'nilai' => 'required',
        'keterangan' => 'required',
        'status' => 'required',
        'periode_id' => 'required'
    ];

    protected $messages = [
        'alternatif_id.required' => 'Alternatif tidak boleh kosong',
        'nilai.required' => 'Nilai tidak boleh kosong',
        'keterangan.required' => 'Keterangan tidak boleh kosong',
        'status.required' => 'Status tidak boleh kosong',
        'periode_id.required' => 'Periode tidak boleh kosong'
    ];

    public function store()
    {
        $this->validate();

        try {
            Hasil::create([
                'alternatif_id' => $this->alternatif_id,
                'nilai' => $this->nilai,
                'keterangan' => $this->keterangan,
                'status' => $this->status,
                'periode_id' => $this->periode_id
            ]);

            $this->reset(['alternatif_id', 'nilai', 'keterangan', 'status', 'periode_id']);
            $this->dispatch('tambahAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            Hasil::find($id)->delete();
            $this->dispatch('deleteAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $hasil = Hasil::find($id);
        $this->alternatif_id = $hasil->alternatif_id;
        $this->nilai = $hasil->nilai;
        $this->keterangan = $hasil->keterangan;
        $this->status = $hasil->status;
        $this->periode_id = $hasil->periode_id;
    }

    public function update()
    {
        try {
            Hasil::find($this->alternatif_id)->update([
                'nilai' => $this->nilai,
                'keterangan' => $this->keterangan,
                'status' => $this->status,
                'periode_id' => $this->periode_id
            ]);

            $this->reset();
            $this->dispatch('updateAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    public function terimaBantuan($id)
    {
        try {
            Hasil::find($id)->update([
                'status' => 'penerima'
            ]);

            $this->dispatch('updateAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }
}; ?>

<div>
    <div class="col-md-12">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-head-row">
                    <div class="card-title">Hasil</div>
                    <div class="card-tools">
                        <!-- periode -->
                        <select wire:model.live="selectedPeriode" class="form-control" id="periode">
                            @foreach($periodes as $periode)
                            <option value="{{ $periode->id }}">{{ $periode->nama }} - {{ $periode->tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table table-responsive">
                    <table class="table table-hover table-borderless">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Alternatif</th>
                                <th>Nilai</th>
                                <th>Status</th>
                                <th>Ranking</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($hasils as $hasil)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $hasil->alternatif->nama }}</td>
                                <td>{{ $hasil->nilai }}</td>
                                <td>{{ $hasil->status }}</td>
                                <td>{{ $hasils->firstItem() + $loop->index }}</td>
                                <td>
                                    <!-- update status jadi sebagai penerima bantuan -->
                                    <button wire:click="terimaBantuan({{ $hasil->id }})" class="btn btn-warning btn-sm">
                                    <i class="fas fa-check"></i>    
                                    Terima Bantuan</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="justify-content-between mt-4">
                    {{ $hasils->links() }}
                </div>
            </div>
        </div>
        <div class="card card-round">
            <div class="card-header">
                <div class="card-head-row">
                    <div class="card-title">Penerima</div>
                    <div class="card-tools">
                        <!-- periode -->
                        <select wire:model.live="selectedPeriodePenerima" class="form-control" id="periodePenerima">
                            @foreach($periodes as $periode)
                            <option value="{{ $periode->id }}">{{ $periode->nama }} - {{ $periode->tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table table-responsive">
                    <table class="table table-hover table-borderless">
                        <thead>
                            <tr>
                                <th width="10%">#</th>
                                <th>NIK/KK</th>
                                <th>Nama/No HP</th>
                                <th>Nilai</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($hasil_penerima as $hasil)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $hasil->alternatif->nik }} 
                                    <br>
                                    {{ $hasil->alternatif->no_kk }}
                                </td>
                                <td>{{ $hasil->alternatif->nama }}
                                    <br>
                                    {{ $hasil->alternatif->no_hp }}
                                </td>
                                <td>{{ $hasil->nilai }}</td>
                                <td>{{ $hasil->status }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="justify-content-between mt-4">
                    {{ $hasil_penerima->links() }}
                </div>
            </div>
        </div>
    </div>
</div>