<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\ControllerBeranda;
use App\Livewire\Dashboard\DashController; // <- pastikan ini adalah controller biasa
use App\Livewire\Dashboard\UserCreate;
use App\Livewire\Dashboard\ProfileDetail;
use App\Livewire\Dashboard\LayananCreate;
use App\Livewire\Dashboard\DiskonCreate;
use App\Livewire\Dashboard\DashKeranjang;
use App\Livewire\Dashboard\DashOrder;

Route::get('/home', [ControllerBeranda::class, 'listlayanan'])->name('home');

// ✅ FIXED: pastikan hanya 1 route untuk dashboard
Route::get('/dashboard', [DashController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {    
    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('home');

    Route::get('/dashboard/users/create', UserCreate::class)
    ->name('dashboard.users.create')
    ->middleware('role:ADMIN,CABANG'); // <- pastikan ini adalah controller biasa

    Route::get('/dashboard/layanan/create', LayananCreate::class)
    ->name('dashboard.layanan.create')
    ->middleware('role:ADMIN'); // <- pastikan ini adalah controller biasa
    
    Route::get('/dashboard/diskon/create', DiskonCreate::class)
    ->name('dashboard.diskon.create')
    ->middleware('role:ADMIN'); // <- pastikan ini adalah controller biasa
    
    Route::get('/keranjang/{id?}', DashKeranjang::class)
    ->name('dashboard.keranjang')
    ->middleware('role:USER'); // <- pastikan ini adalah controller biasa
    
    Route::get('/orders', DashOrder::class)
    ->name('orders')
    ->middleware('role:USER'); // <- pastikan ini adalah controller biasa

    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile-detail', ProfileDetail::class)->name('settings.profile-detail');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Route::resource('cabang', CabangController::class)->middleware('role:ADMIN,CABANG'); CONTOH jika banyak role
    
});

Route::fallback(function () {
    abort(404);
});

require __DIR__ . '/auth.php';
