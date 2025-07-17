<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\Notif;
use Illuminate\Support\Facades\Auth;

class DashKonseling extends Component
{

    public function updateStatus($id)
    {
        $konseling = Order::findOrFail($id);

        if ($konseling->payment_status === 'LUNAS') {
            $konseling->payment_status = 'SELESAI';
            $konseling->save();
        }

        // Simpan notifikasi
        $notif = Notif::create([
            'keterangan'   => 'Konsultasi Diselesaikan #' . $konseling->id_order . ', Terimakasih ',
            'id_order'     => $konseling->id_order,
            'role'         => 'USER',
            'id_penerima'  => $konseling->id_user,
            'status'       => 'terkirim',
        ]);
        
        // Simpan notifikasi
        $notif = Notif::create([
            'keterangan'   => 'Konsultasi Diselesaikan #' . $konseling->id_order,
            'id_order'     => $konseling->id_order,
            'role'         => 'ADMIN',
            'id_penerima'  => 1,
            'status'       => 'terkirim',
        ]);

        return back()->with('success', 'Status berhasil diperbarui menjadi SELESAI.');
    }


    public function showUserMessages($selectedUserId)
    {
        $authId = Auth::id();

        // Ambil semua pesan yang terkait antara auth user dan selected user
        $messages = DB::table('ch_messages')
            ->where(function ($query) use ($authId, $selectedUserId) {
                $query->where('from_id', $authId)
                    ->where('to_id', $selectedUserId);
            })
            ->orWhere(function ($query) use ($authId, $selectedUserId) {
                $query->where('from_id', $selectedUserId)
                    ->where('to_id', $authId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Ambil info user
        $user = \App\Models\User::findOrFail($selectedUserId);

        return view('chatify.user-messages', compact('messages', 'user'));
    }

    public $perPage = 5;

    public function render()
    {
        $konselings = $this->getOrdersByRole(Auth::user()->role, Auth::id());
        return view('livewire.dashboard.Konseling', compact('konselings'));
    }

    private function getOrdersByRole($role, $userId)
    {
        $konselings = Order::with([
            'user.detailUser.user',
            'konselor.detailUser.cabang',
        ]);

        switch ($role) {
            case 'KONSELOR':
                $konselings->where('id_konselor', $userId);
                break;
            
            case 'USER':
                $konselings->where('id_user', $userId);
                break;

            case 'CABANG':
                $konselings->whereHas('konselor.detailUser', function ($q) use ($userId) {
                    $q->where('id_cabang', $userId);
                });
                break;
        }

        return $konselings
                    ->whereIn('orders.payment_status', ['LUNAS', 'SELESAI'])
                    ->paginate($this->perPage);
    }

}
