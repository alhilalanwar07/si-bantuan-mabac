<?php

use App\Models\Alternatif;
use App\Models\Hasil;
use App\Models\User;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return [
            'jumlah_masyarakat' => Alternatif::count(),
            'jumlah_penerima_bantuan' => Hasil::where('status', 'penerima')->count(),
            'jumlah_user' => User::count(),
        ];
    }
}; ?>

<div>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card bg-warning-gradient border-0 shadow text-white">
                <div class="card-header d-sm-flex flex-row align-items-center flex-0">
                    <div class="d-block mb-3 mb-sm-0">
                        <div class="fs-5 fw-normal mb-2">Hi, {{auth()->user()->name ?? 'User'}}</div>
                        <h2 class="fs-3 fw-extrabold">
                            Selamat datang di Sistem Pendukung Keputusan Desa Ranoteta
                        </h2>
                        <div class="small mt-2">
                            <span class="fw-normal me-2">
                                <i class="fas fa-circle text-success me-1"></i>
                                Online
                            </span>
                        </div>

                    </div>
                    <div class="d-flex ms-auto d-none d-sm-block">
                        <img src="{{ url('/') }}/assets/img/favicon/web-app-manifest-192x192.png"
                            alt="Logo" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow  bg-danger-gradient text-white py-3">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">
                        <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                            <div class="icon-shape icon-shape-primary rounded me-4 me-sm-0" style="font-size: 2rem;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="d-sm-none">
                                <h2 class="h5">Masyarakat</h2>
                                <h3 class="fw-extrabold mb-1">{{$jumlah_masyarakat}}</h3>
                            </div>
                        </div>
                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-none d-sm-block">
                                <h2 class="h6 text-gray-400 mb-0">Masyarakat</h2>
                                <h3 class="fw-extrabold mb-2">{{$jumlah_masyarakat}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow  bg-success-gradient text-white py-3">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">
                        <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                            <div class="icon-shape icon-shape-secondary rounded me-4 me-sm-0" style="font-size: 2rem;">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="d-sm-none">
                                <h2 class="fw-extrabold h5">Penerima Bantuan</h2>
                                <h3 class="mb-1">{{$jumlah_penerima_bantuan}}</h3>
                            </div>
                        </div>
                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-none d-sm-block">
                                <h2 class="h6 text-gray-400 mb-0">Penerima Bantuan</h2>
                                <h3 class="fw-extrabold mb-2">{{$jumlah_penerima_bantuan}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow bg-primary-gradient text-white py-3">
                <div class="card-body">
                    <div class="row d-block d-xl-flex align-items-center">
                        <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                            <div class="icon-shape icon-shape-tertiary rounded me-4 me-sm-0" style="font-size: 2rem;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="d-sm-none">
                                <h2 class="fw-extrabold h5"> User</h2>
                                <h3 class="mb-1">{{$jumlah_user}}</h3>
                            </div>
                        </div>
                        <div class="col-12 col-xl-7 px-xl-0">
                            <div class="d-none d-sm-block">
                                <h2 class="h6 text-gray-400 mb-0"> User</h2>
                                <h3 class="fw-extrabold mb-2">{{$jumlah_user}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- foto kepala desa, nama dan sambutan -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #E28624FF, #475C5AFF);">
                <div class="card-body p-4">
                    <div class="row d-flex align-items-center">
                        <!-- Kolom untuk foto dan nama Kepala Desa -->
                        <div class="col-12 col-xl-5 text-center mb-4 mb-xl-0">
                            <!-- Foto -->
                            <img src="{{ url('/') }}/assets/img/favicon/web-app-manifest-192x192.png"
                                alt="Logo" class="img-fluid rounded-circle shadow-sm mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                            <!-- Nama Kepala Desa -->
                            <h3 class="fw-bold text-white mb-0">Juliadi, S.Pd</h3>
                            <h2 class="h6 text-white">Kepala Desa Ranoteta</h2>
                        </div>

                        <!-- Kolom untuk kutipan -->
                        <div class="col-12 col-xl-7 px-xl-4">
                            <blockquote class="blockquote text-white mb-0 text-justify fs-3">
                                <p class="fs-4 font-italic fw-italic text-justify" style="text-align: justify;">
                                    "Selamat datang di Sistem Pendukung Keputusan Bantuan Desa Ranoteta. Kami hadir untuk memudahkan akses informasi dan layanan bantuan bagi warga. Dengan sistem ini, kami berkomitmen memberikan pelayanan terbaik demi kesejahteraan bersama. Terima kasih telah berkunjung, semoga Anda mendapatkan manfaat dari layanan kami."
                                </p>
                                <footer class="blockquote-footer mt-2" style="color: white;">
                                    <cite title="Kepala Desa">Juliadi, S.Pd</cite>
                                </footer>
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>