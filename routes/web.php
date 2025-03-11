<?php

use Illuminate\Support\Facades\{Route, Auth};

// disable register, reset password
// disable register, reset password
Auth::routes(['register' => false, 'reset' => false]);

// jika ke /, redirect ke /login
Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::view('dashboard', 'dashboard')->name('home');
    Route::view('profil', 'profil')->name('profil');
    Route::view('masyarakat', 'masyarakat')->name('masyarakat');
    Route::view('perhitungan', 'perhitungan')->name('perhitungan');
    Route::view('hasil', 'hasil')->name('hasil');
});

Route::middleware('admin')->group(function () {
    Route::view('periode', 'periode')->name('periode');
    Route::view('kriteria', 'kriteria')->name('kriteria');
    Route::view('manajemen-user', 'manajemen-user')->name('admin.manajemen-user');
});