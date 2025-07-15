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

        // Simpan notifikasi
        $notif = Notif::create([
            'keterangan'   => 'User mengupload bukti bayar untuk order #' . $order->id_order,
            'id_order'     => $order->id_order,
            'role'         => 'ADMIN',
            'id_penerima'  => 1, // ganti 1 dengan id admin sebenarnya
            'status'       => 'terkirim',
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

        // Simpan notifikasi
        $notif = Notif::create([
            'keterangan'   => 'Status pembayaran order #' . $order->id_order . ' diubah menjadi ' . $order->payment_status,
            'id_order'     => $order->id_order,
            'role'         => 'USER',
            'id_penerima'  => $order->id_user,
            'status'       => 'terkirim',
        ]);

        if($this->newStatus === 'LUNAS') {
            // Simpan notifikasi untuk konselor jika statusnya LUNAS
            $notif = Notif::create([
                'keterangan'   => 'Segera Melakukan Konseling order #' . $order->id_order . ' telah disetujui.',
                'id_order'     => $order->id_order,
                'role'         => 'KONSELOR',
                'id_penerima'  => $order->id_konselor, // ganti dengan id konselor sebenarnya
                'status'       => 'terkirim',
            ]);

            $notif = Notif::create([
                'keterangan'   => 'Segera Info Konselor Untuk Konseling order #' . $order->id_order,
                'id_order'     => $order->id_order,
                'role'         => 'CABANG',
                'id_penerima'  => $order->konselor->detailUser->id_cabang, // ganti dengan id cabang konselor
                'status'       => 'terkirim',
            ]);
        }
        
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

        session()->flash('success', 'Bukti transfer berhasil dihapus.');
    }

    public function render()
    {
        $orders = $this->getOrdersByRole(Auth::user()->role, Auth::id());
        return view('livewire.dashboard.Order', compact('orders'));
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
