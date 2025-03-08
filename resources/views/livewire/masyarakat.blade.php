<?php

use Livewire\Volt\Component;
use App\Models\Alternatif;
use App\Models\Kriteriaalternatif;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $paginate = 10;

    public $nik, $no_kk, $alamat, $nama, $no_hp, $alternatif_id;
    public $selectedSubkriterias = [];

    public function mount()
    {
        // Initialize the array with empty values
        foreach (\App\Models\Kriteria::all() as $kriteria) {
            $this->selectedSubkriterias[$kriteria->id] = '';
        }
    }

    public function with(): array
    {
        return [
            'alternatifs' => Alternatif::paginate($this->paginate),
            'kriterias' => \App\Models\Kriteria::with('subkriteria')->get(),
        ];
    }

    // 

    protected $rules = [
        'nik' => 'required',
        'no_kk' => 'required',
        'alamat' => 'required',
        'nama' => 'required',
        'no_hp' => 'required'
    ];

    protected $messages = [
        'nik.required' => 'NIK tidak boleh kosong',
        'no_kk.required' => 'No KK tidak boleh kosong',
        'alamat.required' => 'Alamat tidak boleh kosong',
        'nama.required' => 'Nama tidak boleh kosong',
        'no_hp.required' => 'No HP tidak boleh kosong'
    ];

    public function store()
    {
        $this->validate();

        // Validate subkriteria selections
        $validationRules = [];
        $validationMessages = [];

        foreach ($this->selectedSubkriterias as $kriteriaId => $_) {
            $kriteria = \App\Models\Kriteria::find($kriteriaId);
            $validationRules["selectedSubkriterias.$kriteriaId"] = 'required';
            $validationMessages["selectedSubkriterias.$kriteriaId.required"] = "Pilihan untuk {$kriteria->nama} harus diisi";
        }

        $this->validate($validationRules, $validationMessages);

        try {
            DB::beginTransaction();
            try {
                $alternatif = Alternatif::create([
                    'nik' => $this->nik,
                    'no_kk' => $this->no_kk,
                    'alamat' => $this->alamat,
                    'nama' => $this->nama,
                    'no_hp' => $this->no_hp
                ]);

                // Save subkriteria selections
                foreach ($this->selectedSubkriterias as $kriteriaId => $subkriteriaId) {
                    $subkriteria = \App\Models\Subkriteria::find($subkriteriaId);

                    Kriteriaalternatif::create([
                        'alternatif_id' => $alternatif->id,
                        'subkriteria_id' => $subkriteriaId,
                        'nilai' => $subkriteria->bobot // Assuming bobot is the value
                    ]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $this->reset(['nik', 'no_kk', 'alamat', 'nama', 'no_hp']);
            $this->selectedSubkriterias = array_fill_keys(array_keys($this->selectedSubkriterias), '');
            $this->dispatch('tambahAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    // delete
    public function delete($id)
    {
        // tidak bisa di delete jika ada relasi dengan tabel hasil atau kriteriaalternatif
        $alternatif = Alternatif::find($id);
        if ($alternatif->hasil()->exists() || $alternatif->subkriteria()->exists()) {
            $this->dispatch('errorAlertToast', 'Data tidak bisa dihapus karena memiliki relasi dengan data lain');
            return;
        }

        try {
            $alternatif->delete();
            $this->dispatch('deleteAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $alternatif = Alternatif::find($id);
        $this->nik = $alternatif->nik;
        $this->no_kk = $alternatif->no_kk;
        $this->alamat = $alternatif->alamat;
        $this->nama = $alternatif->nama;
        $this->no_hp = $alternatif->no_hp;

        $this->alternatif_id = $id;
    }

    // update
    public function update()
    {
        try {
            Alternatif::find($this->alternatif_id)->update([
                'nik' => $this->nik,
                'no_kk' => $this->no_kk,
                'alamat' => $this->alamat,
                'nama' => $this->nama,
                'no_hp' => $this->no_hp
            ]);

            $this->reset();
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
                    <div class="card-title">Masyarakat</div>
                    <div class="card-tools">
                        <a href="#" class="btn btn-info btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <span class="btn-label">
                                <i class="fa fa-plus"></i>
                            </span>
                            Tambah Data
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table table-responsive">
                    <table class="table table-hover table-borderless">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NIK</th>
                                <th>No KK</th>
                                <th>Alamat</th>
                                <th>Nama</th>
                                <th>No HP</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($alternatifs as $alternatif)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $alternatif->nik }}</td>
                                <td>{{ $alternatif->no_kk }}</td>
                                <td>{{ $alternatif->alamat }}</td>
                                <td>{{ $alternatif->nama }}</td>
                                <td>{{ $alternatif->no_hp }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-primary m-1" data-bs-toggle="modal" data-bs-target="#modalEdit" wire:click="edit({{ $alternatif->id }})">Edit</a>
                                    <a href="#" class="btn btn-sm btn-danger m-1" wire:click="delete({{ $alternatif->id }})">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="justify-content-between mt-4">
                    {{ $alternatifs->links() }}
                </div>
            </div>
        </div>
    </div>
    <div wire:ignore class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahLabel">Tambah Alternatif</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="store()">
                        @csrf
                        <div class="mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" placeholder="NIK" wire:model="nik">
                            @error('nik') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="no_kk" class="form-label">No KK</label>
                            <input type="text" class="form-control @error('no_kk') is-invalid @enderror" id="no_kk" placeholder="No KK" wire:model="no_kk">
                            @error('no_kk') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <input type="text" class="form-control @error('alamat') is-invalid @enderror" id="alamat" placeholder="Alamat" wire:model="alamat">
                            @error('alamat') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" placeholder="Nama" wire:model="nama">
                            @error('nama') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="no_hp" class="form-label">No HP</label>
                            <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" placeholder="No HP" wire:model="no_hp">
                            @error('no_hp') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <!-- menampilkan semua kriteria untuk memilih subkriteria -->
                        @foreach($kriterias as $kriteria)
                        <div class="mb-3">
                            <label class="form-label">{{ $kriteria->nama }}</label>
                            <select class="form-select @error('selectedSubkriterias.'.$kriteria->id) is-invalid @enderror" wire:model="selectedSubkriterias.{{ $kriteria->id }}">
                                <option value="">--Pilih--</option>
                                @foreach($kriteria->subkriteria as $subkriteria)
                                <option value="{{ $subkriteria->id }}">{{ $subkriteria->nama }}</option>
                                @endforeach
                            </select>
                            @error('selectedSubkriterias.'.$kriteria->id) <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        @endforeach

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div wire:ignore class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel">Edit Alternatif</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="update()">
                        @csrf
                        <div class="mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" placeholder="NIK" wire:model="nik">
                            @error('nik') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="no_kk" class="form-label">No KK</label>
                            <input type="text" class="form-control @error('no_kk') is-invalid @enderror" id="no_kk" placeholder="No KK" wire:model="no_kk">
                            @error('no_kk') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <input type="text" class="form-control @error('alamat') is-invalid @enderror" id="alamat" placeholder="Alamat" wire:model="alamat">
                            @error('alamat') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" placeholder="Nama" wire:model="nama">
                            @error('nama') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="no_hp" class="form-label">No HP</label>
                            <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" placeholder="No HP" wire:model="no_hp">
                            @error('no_hp') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn
                            btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <livewire:_alert />
</div>