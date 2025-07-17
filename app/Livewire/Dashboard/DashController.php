<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\User;
use App\Models\Diskon;
use App\Models\Layanan;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashController extends Component
{
    public function index()
    {
        $user = Auth::user();

        // Default: tidak ada order
        $ordersLunas = collect();

        // Cek role user
        if ($user->role === 'USER') {
            $ordersLunas = Order::where('id_user', $user->id)
                                ->where('payment_status', 'LUNAS')
                                ->get();
        } elseif ($user->role === 'KONSELOR') {
            $ordersLunas = Order::where('id_konselor', $user->id)
                                ->where('payment_status', 'LUNAS')
                                ->get();
        }

        return view('dashboard', [
            'jumlahKonselor' => User::where('role', 'KONSELOR')->count(),
            'jumlahUser' => User::where('role', 'USER')->count(),
            'jumlahDiskonAktif' => Diskon::where('status_aktiv', 'AKTIF')->count(),
            'layanans' => Layanan::all(),
            'konselings' => $ordersLunas,
        ]);
    }
}
