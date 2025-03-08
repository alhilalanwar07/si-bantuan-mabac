<?php

use Livewire\Volt\Component;
use App\Models\Kriteria;
use App\Models\Subkriteria;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $nama_kriteria, $bobot_kriteria, $deskripsi_kriteria, $kriteria_id;
    public $selectedKriteriaId;
    public $isSubkriteriaMode = false;
    public $paginate = 10;
    public $search;
    public $subkriteria_id, $nama, $bobot, $deskripsi;
    public $subkriterias;

    public function with(): array
    {
        return [
            'kriterias' => Kriteria::with('subkriteria')->where('nama', 'like', '%' . $this->search . '%')->paginate($this->paginate)
        ];
    }

    public function storeKriteria()
    {
        $this->validate([
            'nama_kriteria' => 'required',
            'bobot_kriteria' => 'required',
            'deskripsi_kriteria' => 'required'
        ], [
            'nama_kriteria.required' => 'Nama tidak boleh kosong',
            'bobot_kriteria.required' => 'Bobot tidak boleh kosong',
            'deskripsi_kriteria.required' => 'Deskripsi tidak boleh kosong'
        ]);

        try {
            Kriteria::create([
                'nama' => $this->nama_kriteria,
                'bobot' => $this->bobot_kriteria,
                'deskripsi' => $this->deskripsi_kriteria
            ]);

            $this->reset();
            $this->dispatch('tambahAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    public function editKriteria($id)
    {
        $kriteria = Kriteria::find($id);
        $this->kriteria_id = $kriteria->id;
        $this->nama_kriteria = $kriteria->nama;
        $this->bobot_kriteria = $kriteria->bobot;
        $this->deskripsi_kriteria = $kriteria->deskripsi;
    }   

    public function updateKriteria()
    {
        $this->validate([
            'nama_kriteria' => 'required',
            'bobot_kriteria' => 'required',
            'deskripsi_kriteria' => 'required'
        ], [
            'nama_kriteria.required' => 'Nama tidak boleh kosong',
            'bobot_kriteria.required' => 'Bobot tidak boleh kosong',
            'deskripsi_kriteria.required' => 'Deskripsi tidak boleh kosong'
        ]);

        try {
            $kriteria = Kriteria::find($this->kriteria_id);
            $kriteria->update([
                'nama' => $this->nama_kriteria,
                'bobot' => $this->bobot_kriteria,
                'deskripsi' => $this->deskripsi_kriteria
            ]);

            $this->reset();
            $this->dispatch('updateAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    public function deleteKriteria($id)
    {
        $kriteria = Kriteria::find($id);
        if ($kriteria->subkriteria()->exists()) {
            $this->dispatch('errorAlertToast', 'Data tidak bisa dihapus karena memiliki relasi dengan data lain');
            return;
        }

        try {
            $kriteria->delete();
            $this->dispatch('deleteAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    public function showSubkriteria($id)
    {
        $this->selectedKriteriaId = $id;
        $this->isSubkriteriaMode = true;
        $this->subkriterias = Subkriteria::where('kriteria_id', $id)->get();
    }

    public function backToKriteria()
    {
        $this->isSubkriteriaMode = false;
    }

    public function store()
    {
        $this->validate([
            'nama' => 'required',
            'bobot' => 'required',
            'deskripsi' => 'required'
        ], [
            'nama.required' => 'Nama tidak boleh kosong',
            'bobot.required' => 'Bobot tidak boleh kosong',
            'deskripsi.required' => 'Deskripsi tidak boleh kosong'
        ]);

        try {
            Subkriteria::create([
                'kriteria_id' => $this->selectedKriteriaId,
                'nama' => $this->nama,
                'bobot' => $this->bobot,
                'deskripsi' => $this->deskripsi
            ]);

            $this->reset();
            $this->dispatch('tambahAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $subkriteria = Subkriteria::find($id);
        $this->subkriteria_id = $subkriteria->id;
        $this->nama = $subkriteria->nama;
        $this->bobot = $subkriteria->bobot;
        $this->deskripsi = $subkriteria->deskripsi;
    }

    public function update()
    {
        $this->validate([
            'nama' => 'required',
            'bobot' => 'required',
            'deskripsi' => 'required'
        ], [
            'nama.required' => 'Nama tidak boleh kosong',
            'bobot.required' => 'Bobot tidak boleh kosong',
            'deskripsi.required' => 'Deskripsi tidak boleh kosong'
        ]);

        try {
            $subkriteria = Subkriteria::find($this->subkriteria_id);
            $subkriteria->update([
                'nama' => $this->nama,
                'bobot' => $this->bobot,
                'deskripsi' => $this->deskripsi
            ]);

            $this->reset();
            $this->dispatch('updateAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    public function delete($id)
    {
        $subkriteria = Subkriteria::find($id);
        if ($subkriteria->alternatif()->exists()) {
            $this->dispatch('errorAlertToast', 'Data tidak bisa dihapus karena memiliki relasi dengan data lain');
            return;
        }

        try {
            $subkriteria->delete();
            $this->dispatch('deleteAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());   
        }

        $this->subkriterias = Subkriteria::where('kriteria_id', $this->selectedKriteriaId)->get();

    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
};
?>

<div>
    <div class="col-md-12">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-head-row">
                    <div class="card-title">Kriteria</div>
                    <div class="card-tools">
                        <a href="#" class="btn btn-info btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalTambahKriteria">
                            <span class="btn-label">
                                <i class="fa fa-plus"></i>
                            </span>
                            Tambah Kriteria
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
                                <th>Nama Kriteria</th>
                                <th>Bobot</th>
                                <th>Deskripsi</th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($kriterias as $kriteria)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $kriteria->nama }}</td>
                                <td>{{ $kriteria->bobot }}</td>
                                <td>{{ $kriteria->deskripsi }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-primary m-1" data-bs-toggle="modal" data-bs-target="#modalEditKriteria" wire:click="editKriteria({{ $kriteria->id }})">Edit</a>
                                    <a href="#" class="btn btn-sm btn-danger m-1" wire:click="deleteKriteria({{ $kriteria->id }})">Delete</a>
                                    <a href="#" class="btn btn-sm btn-secondary m-1" wire:click="showSubkriteria({{ $kriteria->id }})">Subkriteria</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="justify-content-between mt-4">
                    {{ $kriterias->links() }}
                </div>
            </div>
        </div>
    </div>

    @if($isSubkriteriaMode)
    <div class="col-md-12 mt-4">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-head-row">
                    <div class="card-title">Subkriteria</div>
                    <div class="card-tools">
                        <a href="#" class="btn btn-info btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalTambahSubkriteria">
                            <span class="btn-label">
                                <i class="fa fa-plus"></i>
                            </span>
                            Tambah Subkriteria
                        </a>
                        <a href="#" class="btn btn-secondary btn-sm me-2" wire:click="backToKriteria">
                            <span class="btn-label">
                                <i class="fa fa-arrow-left"></i>
                            </span>
                            Kembali
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
                                <th>Nama</th>
                                <th>Bobot</th>
                                <th>Deskripsi</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($subkriterias as $subkriteria)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $subkriteria->nama }}</td>
                                <td>{{ $subkriteria->bobot }}</td>
                                <td>{{ $subkriteria->deskripsi }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-primary m-1" data-bs-toggle="modal" data-bs-target="#modalEditSubkriteria" wire:click="edit({{ $subkriteria->id }})">Edit</a>
                                    <a href="#" class="btn btn-sm btn-danger m-1" wire:click="delete({{ $subkriteria->id }})">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div wire:ignore class="modal fade" id="modalTambahKriteria" tabindex="-1" aria-labelledby="modalTambahKriteriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahKriteriaLabel">Tambah Kriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="storeKriteria()">
                        @csrf
                        <div class="mb-3">
                            <label for="nama_kriteria" class="form-label">Nama Kriteria</label>
                            <input type="text" class="form-control @error('nama_kriteria') is-invalid @enderror" id="nama_kriteria" placeholder="Nama Kriteria" wire:model="nama_kriteria">
                            @error('nama_kriteria') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="bobot_kriteria" class="form-label">Bobot Kriteria</label>
                            <input type="text" class="form-control @error('bobot_kriteria') is-invalid @enderror" id="bobot_kriteria" placeholder="Bobot Kriteria" wire:model="bobot_kriteria">
                            @error('bobot_kriteria') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi_kriteria" class="form-label">Deskripsi Kriteria</label>
                            <input type="text" class="form-control @error('deskripsi_kriteria') is-invalid @enderror" id="deskripsi_kriteria" placeholder="Deskripsi Kriteria" wire:model="deskripsi_kriteria">
                            @error('deskripsi_kriteria') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore class="modal fade" id="modalEditKriteria" tabindex="-1" aria-labelledby="modalEditKriteriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditKriteriaLabel">Edit Kriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="updateKriteria()">
                        @csrf
                        <div class="mb-3">
                            <label for="nama_kriteria" class="form-label">Nama Kriteria</label>
                            <input type="text" class="form-control @error('nama_kriteria') is-invalid @enderror" id="nama_kriteria" placeholder="Nama Kriteria" wire:model="nama_kriteria">
                            @error('nama_kriteria') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="bobot_kriteria" class="form-label">Bobot Kriteria</label>
                            <input type="text" class="form-control @error('bobot_kriteria') is-invalid @enderror" id="bobot_kriteria" placeholder="Bobot Kriteria" wire:model="bobot_kriteria">
                            @error('bobot_kriteria') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi_kriteria" class="form-label">Deskripsi Kriteria</label>
                            <input type="text" class="form-control @error('deskripsi_kriteria') is-invalid @enderror" id="deskripsi_kriteria" placeholder="Deskripsi Kriteria" wire:model="deskripsi_kriteria">
                            @error('deskripsi_kriteria') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore class="modal fade" id="modalTambahSubkriteria" tabindex="-1" aria-labelledby="modalTambahSubkriteriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahSubkriteriaLabel">Tambah Subkriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="store()">
                        @csrf
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" placeholder="Nama" wire:model="nama">
                            @error('nama') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="bobot" class="form-label">Bobot</label>
                            <input type="text" class="form-control @error('bobot') is-invalid @enderror" id="bobot" placeholder="Bobot" wire:model="bobot">
                            @error('bobot') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <input type="text" class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" placeholder="Deskripsi" wire:model="deskripsi">
                            @error('deskripsi') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore class="modal fade" id="modalEditSubkriteria" tabindex="-1" aria-labelledby="modalEditSubkriteriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditSubkriteriaLabel">Edit Subkriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="update()">
                        @csrf
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" placeholder="Nama" wire:model="nama">
                            @error('nama') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="bobot" class="form-label">Bobot</label>
                            <input type="text" class="form-control @error('bobot') is-invalid @enderror" id="bobot" placeholder="Bobot" wire:model="bobot">
                            @error('bobot') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <input type="text" class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" placeholder="Deskripsi" wire:model="deskripsi">
                            @error('deskripsi') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <livewire:_alert />
</div>