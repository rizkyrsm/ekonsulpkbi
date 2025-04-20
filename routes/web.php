<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\KonselorController;

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['web', 'auth'])->group(function () {    
    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('home');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::resource('cabang', CabangController::class)->middleware('role:ADMIN,CABANG');
    Route::resource('konselor', KonselorController::class)->middleware('role:ADMIN,CABANG');
});

require __DIR__ . '/auth.php';
