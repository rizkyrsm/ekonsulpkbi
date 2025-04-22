<?php

namespace App\Livewire\Dashboard;
use Livewire\Component;
use App\Models\User;
use App\Models\Diskon;

class DashController extends Component
{
    public function index()
    {
        return view('dashboard', [
            'jumlahKonselor' => User::where('role', 'KONSELOR')->count(),
            'jumlahUser' => User::where('role', 'USER')->count(),
            'jumlahDiskonAktif' => Diskon::where('status_aktiv', 'AKTIF')->count(), // pastikan nama kolom benar
        ]);
    }
}
