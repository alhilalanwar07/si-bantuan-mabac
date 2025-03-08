<?php

use Livewire\Volt\Component;
use App\Models\Periode;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $paginate = 10;

    public function with(): array
    {
        return [
            'periodes' => Periode::paginate($this->paginate)
        ];
    }

    // create periode
    public $nama, $tahun, $periode_id;

    public function store()
    {
        $this->validate([
            'nama' => 'required',
            'tahun' => 'required'
        ], [
            'nama.required' => 'Nama tidak boleh kosong',
            'tahun.required' => 'Tahun tidak boleh kosong'
        ]);

        try {
            Periode::create([
                'nama' => $this->nama,
                'tahun' => $this->tahun
            ]);

            $this->reset();
            $this->dispatch('tambahAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    // delete
    public function delete($id)
    {
        $periode = Periode::find($id);
        if ($periode->hasil()->exists()) {
            $this->dispatch('errorAlertToast', 'Data tidak bisa dihapus karena memiliki relasi dengan data lain');
            return;
        }

        try {
            $periode->delete();
            $this->dispatch('deleteAlertToast');
        } catch (\Exception $e) {
            $this->dispatch('errorAlertToast', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $periode = Periode::find($id);
        $this->nama = $periode->nama;
        $this->tahun = $periode->tahun;

        $this->periode_id = $id;
    }

    // update
    public function update()
    {
        try {
            Periode::find($this->periode_id)->update([
                'nama' => $this->nama,
                'tahun' => $this->tahun
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
                    <div class="card-title">Periode</div>
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
                                <th>Nama</th>
                                <th>Tahun</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($periodes as $periode)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $periode->nama }}</td>
                                <td>{{ $periode->tahun }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-primary m-1" data-bs-toggle="modal" data-bs-target="#modalEdit" wire:click="edit({{ $periode->id }})">Edit</a>
                                    <a href="#" class="btn btn-sm btn-danger m-1" wire:click="delete({{ $periode->id }})">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="justify-content-between mt-4">
                    {{ $periodes->links() }}
                </div>
            </div>
        </div>
    </div>
    <div wire:ignore class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahLabel">Tambah Periode</h5>
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
                            <label for="tahun" class="form-label">Tahun</label>
                            <select class="form-select @error('tahun') is-invalid @enderror" id="tahun" wire:model="tahun">
                                <option value="">Pilih Tahun</option>
                                @for($i = date('Y'); $i >= 2024; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            @error('tahun') <span class="text-danger">{{ $message }}</span> @enderror
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
    <div wire:ignore class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel">Edit Periode</h5>
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
                            <label for="tahun" class="form-label">Tahun</label>
                            <select class="form-select @error('tahun') is-invalid @enderror" id="tahun" wire:model="tahun">
                                <option value="">Pilih Tahun</option>
                                @for($i = date('Y'); $i >= 2024; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            @error('tahun') <span class="text-danger">{{ $message }}</span> @enderror
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