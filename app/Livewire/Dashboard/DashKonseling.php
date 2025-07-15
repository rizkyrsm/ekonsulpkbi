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
        /* if (Auth::user()->role === 'ADMIN') {
            $konselings = Order::join('users', 'orders.id_user', '=', 'users.id')
                ->leftJoin('detail_users', 'orders.id_konselor', '=', 'detail_users.id_user')
                ->select('orders.*', 'users.name as user_name', 'users.email as user_email', 'detail_users.nama as konselor_name')
                ->whereIn('orders.payment_status', ['LUNAS', 'SELESAI'])
                ->orderBy('orders.created_at', 'desc')
                ->paginate($this->perPage);
        } else if (Auth::user()->role === 'CABANG') {
            $konselings = Order::join('users', 'orders.id_user', '=', 'users.id')
                ->leftJoin('detail_users', 'orders.id_konselor', '=', 'detail_users.id_user')
                ->where('detail_users.id_cabang', Auth::user()->id)
                ->whereIn('orders.payment_status', ['LUNAS', 'SELESAI'])
                ->select('orders.*', 'users.name as user_name', 'users.email as user_email', 'detail_users.nama as konselor_name')
                ->orderBy('orders.created_at', 'desc')
                ->paginate($this->perPage);
        } else if (Auth::user()->role === 'KONSELOR') {
            $konselings = Order::join('users', 'orders.id_user', '=', 'users.id')
                ->leftJoin('detail_users', 'orders.id_konselor', '=', 'detail_users.id_user')
                ->where('orders.id_konselor', Auth::id())
                ->whereIn('orders.payment_status', ['LUNAS', 'SELESAI'])
                ->select('orders.*', 'users.name as user_name', 'users.email as user_email', 'detail_users.nama as konselor_name')
                ->orderBy('orders.created_at', 'desc')
                ->paginate($this->perPage);
        } else {
            $konselings = Order::join('users', 'orders.id_user', '=', 'users.id')
                ->join('detail_users', 'orders.id_konselor', '=', 'detail_users.id_user')
                ->where('orders.id_user', Auth::id())
                ->whereIn('orders.payment_status', ['LUNAS', 'SELESAI'])
                ->select('orders.*', 'users.name as user_name', 'users.email as user_email', 'detail_users.nama as konselor_name')
                ->orderBy('orders.created_at', 'desc')
                ->paginate($this->perPage);
        } */

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
