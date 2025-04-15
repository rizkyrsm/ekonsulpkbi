<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\ControllerBeranda;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\KonselorController;

Route::get('/', [ControllerBeranda::class, 'listuser'])->name('home');
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    
    // Route::get('cabang', [CabangController::class, 'ListCabang'])
    // ->name('cabang')
    // ->middleware(\App\Http\Middleware\CheckAdminRole::class);
    // Route::post('/cabang', [CabangController::class, 'tambah'])->name('cabang.add');

    Route::resource('cabang', CabangController::class);
    Route::resource('konselor', KonselorController::class);


});

require __DIR__.'/auth.php';
