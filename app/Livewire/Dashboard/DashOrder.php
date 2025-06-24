<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\Notif;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class DashOrder extends Component
{
    use WithPagination, WithFileUploads;

    public $perPage = 5;
    public $bukti_transfer;
    public $uploadingOrderId = null;

    protected $rules = [
        'bukti_transfer' => 'image|max:2048', // maksimal 2MB
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showUploadForm($orderId)
    {
        $this->uploadingOrderId = $orderId;
        $this->bukti_transfer = null;
    }

    public function cancelUpload()
    {
        $this->uploadingOrderId = null;
        $this->bukti_transfer = null;
    }

    public function uploadBukti()
    {
        $this->validate();

        $order = Order::where('id_order', $this->uploadingOrderId)
            ->where('id_user', Auth::id())
            ->firstOrFail();

        $filename = $this->bukti_transfer->store('bukti-transfer', 'public');
        $order->bukti_transfer = $filename;
        $order->save();

        // Ambil id_cabang dari detail_users berdasarkan id konselor
        $idCabang = Order::join('users', 'orders.id_user', '=', 'users.id')
                ->leftJoin('detail_users', 'orders.id_konselor', '=', 'detail_users.id_user')
                ->where('orders.id_order', $this->uploadingOrderId)
                ->value('detail_users.id_cabang');

        $nama = auth()->user()->name;
        // Simpan notifikasi ke tabel notif
        Notif::create([
            'keterangan' => 'Bukti bayar di kirim oleh ' . $nama,
            'id_order' => $this->uploadingOrderId,
            'role' => 'ADMIN', // Sesuaikan jika peran penerima berbeda
            'id_penerima' => 1, // Misalnya id konselor sebagai penerima
            'status' => 'terkirim',
        ]);

        // Simpan notifikasi ke tabel notif cabang
        Notif::create([
            'keterangan' => 'Bukti bayar di kirim oleh ' . $nama,
            'id_order' => $this->uploadingOrderId,
            'role' => 'CABANG', // Sesuaikan jika peran penerima berbeda
            'id_penerima' => $idCabang, // Misalnya id konselor sebagai penerima
            'status' => 'terkirim',
        ]);

        session()->flash('success', 'Bukti transfer berhasil diupload.');
        $this->reset(['bukti_transfer', 'uploadingOrderId']);
    }

    public $editingStatusId = null;
    public $newStatus = null;

    public function editStatus($id)
    {
        $this->editingStatusId = $id;
        $order = Order::findOrFail($id);
        $this->newStatus = $order->payment_status;
    }

    public function cancelEditStatus()
    {
        $this->editingStatusId = null;
        $this->newStatus = null;
    }

    public function updateStatus($id)
    {
        $this->validate([
            'newStatus' => 'required|in:BELUM BAYAR,LUNAS,SELESAI',
        ]);

        $order = Order::findOrFail($id);
        $order->payment_status = $this->newStatus;
        $order->save();

        // Ambil id penerima dari order
        $idusernotif = Order::where('orders.id_order', $id)
                ->value('orders.id_user');
        $idkonselornotif = Order::where('orders.id_order', $id)
                ->value('orders.id_konselor');

        if($this->newStatus === 'SELESAI') {
            // Jika status diubah menjadi SELESAI, set tanggal selesai
            $keterangannotif = 'Konseling telah berakhir dan status pesanan diubah menjadi SELESAI';
        }else if($this->newStatus === 'LUNAS') {
            // Jika status diubah menjadi LUNAS, set tanggal lunas
            $keterangannotif = 'Pembayaran telah diterima, Mulai Konseling';
        }

        // Simpan notifikasi ke tabel notif
        Notif::create([
            'keterangan' => $keterangannotif,
            'id_order' => $id,
            'role' => 'USER', // Sesuaikan jika peran penerima berbeda
            'id_penerima' => $idusernotif, // Misalnya id konselor sebagai penerima
            'status' => 'terkirim',
        ]);
        
        Notif::create([
            'keterangan' => $keterangannotif,
            'id_order' => $id,
            'role' => 'KONSELOR', // Sesuaikan jika peran penerima berbeda
            'id_penerima' => $idkonselornotif, // Misalnya id konselor sebagai penerima
            'status' => 'terkirim',
        ]);

        session()->flash('success', 'Status pembayaran berhasil diperbarui.');
        $this->cancelEditStatus();
    }

    public function deleteBuktiTransfer($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Hanya ADMIN dan CABANG yang boleh hapus
        if (!in_array(Auth::user()->role, ['ADMIN', 'CABANG'])) {
            abort(403, 'Unauthorized');
        }

        // Hapus file dari storage
        if ($order->bukti_transfer && Storage::disk('public')->exists($order->bukti_transfer)) {
            Storage::disk('public')->delete($order->bukti_transfer);
        }

        // Set kolom bukti_transfer jadi null
        $order->bukti_transfer = null;
        $order->save();

        // Ambil id_cabang dari detail_users
        $idusernotif = Order::where('orders.id_order', $orderId)
                ->value('orders.id_user');

        // Simpan notifikasi ke tabel notif
        Notif::create([
            'keterangan' => 'Bukti bayar anda ditolak ',
            'id_order' => $orderId,
            'role' => 'USER', // Sesuaikan jika peran penerima berbeda
            'id_penerima' => $idusernotif, // Misalnya id konselor sebagai penerima
            'status' => 'terkirim',
        ]);

        session()->flash('success', 'Bukti transfer berhasil dihapus.');
    }

    public function render()
    {
        if (Auth::user()->role === 'ADMIN') {
            $orders = Order::join('users', 'orders.id_user', '=', 'users.id')
                ->leftJoin('detail_users', 'orders.id_konselor', '=', 'detail_users.id_user')
                ->select('orders.*', 'users.name as user_name', 'users.email as user_email', 'detail_users.nama as konselor_name')
                ->orderBy('orders.created_at', 'desc')
                ->paginate($this->perPage);
        } else if (Auth::user()->role === 'CABANG') {
            $orders = Order::join('users', 'orders.id_user', '=', 'users.id')
                ->leftJoin('detail_users', 'orders.id_konselor', '=', 'detail_users.id_user')
                ->where('detail_users.id_cabang', Auth::user()->id)
                ->select('orders.*', 'users.name as user_name', 'users.email as user_email', 'detail_users.nama as konselor_name')
                ->orderBy('orders.created_at', 'desc')
                ->paginate($this->perPage);
        } else {
            $orders = Order::join('users', 'orders.id_user', '=', 'users.id')
                ->where('orders.id_user', Auth::id())
                ->join('detail_users', 'orders.id_konselor', '=', 'detail_users.id_user')
                ->select('orders.*', 'users.name as user_name', 'users.email as user_email', 'detail_users.nama as konselor_name')
                ->orderBy('orders.created_at', 'desc')
                ->paginate($this->perPage);
        }

        return view('livewire.dashboard.order', compact('orders'));
    }

    private function getOrdersByRole($role, $userId)
    {
        $orders = Order::with([
            'user.detailUser.user',
            'konselor.detailUser.cabang',
        ]);

        switch ($role) {
            case 'USER':
                $orders->where('id_user', $userId);
                break;

            case 'CABANG':
                $orders->whereHas('konselor.detailUser', function ($q) use ($userId) {
                    $q->where('id_cabang', $userId);
                });
                break;
        }

        return $orders->paginate($this->perPage);
    }
}
