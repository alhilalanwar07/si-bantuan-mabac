<?php

use Livewire\Volt\Component;
use App\Livewire\Actions\Logout;

new class extends Component {

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
        <div class="container-fluid">
            <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
                <h4 class="">
                    Sistem Pendukung Keputusan Bantuan Desa Ranoteta
                </h4>
            </nav>

            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" aria-haspopup="true">
                        <i class="fa fa-search"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-search animated fadeIn">
                        <form class="navbar-left navbar-form nav-search">
                            <div class="input-group">
                                <input type="text" placeholder="Search ..." class="form-control" />
                            </div>
                        </form>
                    </ul>
                </li>
                <li class="nav-item topbar-icon dropdown hidden-caret">
                    <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    </a>
                </li>

                <li class="nav-item topbar-user dropdown hidden-caret">
                    <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                        <div class="avatar-sm">
                            <img src="{{ url('/') }}/assets/img/favicon/web-app-manifest-192x192.png" alt="Desa Ranoteta" class="avatar-img rounded-circle" />
                        </div>
                        <span class="profile-username">
                            <span class="op-7">Hi,</span>
                            <span class="fw-bold">
                                {{ auth()->check() ? auth()->user()->name : 'Guest' }}
                            </span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-user animated fadeIn">
                        <div class="dropdown-user-scroll scrollbar-outer">
                            <li>
                                <div class="user-box">
                                    <div class="avatar-lg">
                                        <img src="{{ url('/') }}/assets/img/favicon/web-app-manifest-192x192.png" alt="Desa Ranoteta" class="avatar-img rounded" />
                                    </div>
                                    <div class="u-text">
                                        <h4>
                                            {{ auth()->check() ? auth()->user()->name : 'Guest' }}
                                        </h4>
                                        <p class="text-muted">
                                            {{ auth()->check() ? auth()->user()->email : ' ' }}
                                        </p>
                                        <a href="/profil" class="btn btn-xs btn-secondary btn-sm">Lihat Profile</a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" wire:click="logout">
                                    <i class="fa fa-sign-out-alt"></i> Keluar
                                </a>
                            </li>
                        </div>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</div>
