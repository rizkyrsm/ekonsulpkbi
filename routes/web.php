<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\ControllerBeranda;
use App\Livewire\Dashboard\DashController; // <- pastikan ini adalah controller biasa
use App\Livewire\Dashboard\UserCreate;
use App\Livewire\Dashboard\ProfileDetail;
use App\Livewire\Dashboard\LayananCreate;
use App\Livewire\Dashboard\DiskonCreate;

Route::get('/', [ControllerBeranda::class, 'listlayanan'])->name('home');

// ✅ FIXED: pastikan hanya 1 route untuk dashboard
Route::get('/dashboard', [DashController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('/dashboard/users/create', UserCreate::class)->name('dashboard.users.create');
    Volt::route('settings/profile-detail', ProfileDetail::class)->name('settings.profile-detail');
    Route::get('/dashboard/layanan/create', LayananCreate::class)->name('dashboard.layanan.create');
    Route::get('/dashboard/diskon/create', DiskonCreate::class)->name('dashboard.diskon.create');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
