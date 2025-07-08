<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Layanan;
use App\Models\Diskon;
use App\Models\User;
use App\Models\Order;
use App\Models\Notif;
use Illuminate\Support\Facades\Auth;

class DashKeranjang extends Component
{
    public $total, $payment_status;
    public $layanans;
    public $voucher;
    public $potongan = 0;
    public $hargaSetelahDiskon = [];
    public $message;
    public $vouchernofalid;
    public $jenispotongan;
    public $konselors;
    public $konselor; // Properti yang ditambahkan

    public function mount($id = null)
    {
        // Mengambil data layanan
        $this->layanans = $id ? Layanan::where('id_layanan', $id)->get() : Layanan::all();
        $this->resetHarga();
        $this->total = array_sum($this->hargaSetelahDiskon); // Hitung total awal
    }

    public function resetHarga()
    {
        foreach ($this->layanans as $layanan) {
            $this->hargaSetelahDiskon[$layanan->id_layanan] = $layanan->harga_layanan;
        }
    }

    public function applyVoucher()
    {
        $diskon = Diskon::where('kode_voucher', $this->voucher)
            ->where('status_aktiv', 'AKTIF')
            ->first();

        if ($diskon) {
            foreach ($this->layanans as $layanan) {
                $harga = $layanan->harga_layanan;

                if ($diskon->jumlah_diskon_harga) {
                    $potongan = $diskon->jumlah_diskon_harga;
                    $this->jenispotongan = 'Rp.' . $diskon->jumlah_diskon_harga;
                } elseif ($diskon->jumlah_diskon_persen) {
                    $potongan = ($harga * $diskon->jumlah_diskon_persen) / 100;
                    $this->jenispotongan = $diskon->jumlah_diskon_persen . '%';
                } else {
                    $potongan = 0;
                    $this->jenispotongan = '';
                }

                $this->hargaSetelahDiskon[$layanan->id_layanan] = max(0, $harga - $potongan);
                $this->message = 'Voucher berhasil diterapkan!';
                $this->vouchernofalid = $diskon->kode_voucher;
            }
        } else {
            $this->resetHarga();
            $this->message = 'Voucher tidak valid!';
            $this->vouchernofalid = 'tidak valid!';
            $this->jenispotongan = '';
            $this->voucher = '';
        }

        $this->total = array_sum($this->hargaSetelahDiskon);
    }

    public function saveOrder()
    {
        if (!$this->total) {
            // Hitung total default jika belum ada
            $this->total = array_sum($this->hargaSetelahDiskon);
        }

        $this->validate([
            'konselor' => 'required',
            'voucher' => 'nullable|string',
            'total' => 'required|numeric',
        ]);

        try {
            Order::create([
                'id_user' => Auth::id(),
                'id_konselor' => $this->konselor,
                'nama_layanan' => implode(', ', array_column($this->layanans->toArray(), 'nama_layanan')),
                'voucher' => $this->voucher,
                'total' => $this->total,
                'payment_status' => 'BELUM BAYAR',
            ]);

            // Ambil id_cabang dari detail_users berdasarkan id konselor
            $idCabang = \DB::table('detail_users')
                ->where('id_user', $this->konselor)
                ->value('id_cabang');
            
            // Ambil id_order dari order terbaru
            $idOrders = \DB::table('orders')
                ->orderBy('created_at', 'desc')
                ->value('id_order');
            // Ambil order yang baru saja dibuat

            $nama = auth()->user()->name;
            // Simpan notifikasi ke tabel notif
            Notif::create([
                'keterangan' => 'Order baru telah dibuat oleh ' . $nama,
                'id_order' => $idOrders,
                'role' => 'ADMIN', // Sesuaikan jika peran penerima berbeda
                'id_penerima' => 1, // Misalnya id konselor sebagai penerima
                'status' => 'terkirim',
            ]);
            
            // Simpan notifikasi ke tabel notif cabang
            Notif::create([
                'keterangan' => 'Order baru telah dibuat oleh ' . $nama,
                'id_order' => $idOrders,
                'role' => 'CABANG', // Sesuaikan jika peran penerima berbeda
                'id_penerima' => $idCabang, // Misalnya id konselor sebagai penerima
                'status' => 'terkirim',
            ]);
            
            // Simpan notifikasi ke tabel notif konselor
            Notif::create([
                'keterangan' => 'Siap" Memulai Konseling Order baru telah dibuat ' . $nama,
                'id_order' => $idOrders,
                'role' => 'KONSELOR', // Sesuaikan jika peran penerima berbeda
                'id_penerima' => $this->konselor, // Misalnya id konselor sebagai penerima
                'status' => 'terkirim',
            ]);

            session()->flash('message', 'Pesanan berhasil disimpan, Silahkan lakukan konfirmasi pembayaran jika sudah melakukan pembayaran.');
            return redirect()->route('orders');
        } catch (\Exception $e) {
            session()->flash('message', 'Gagal menyimpan pesanan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $this->konselors = User::select('users.*', 'detail_users.*', 'cabang.name as cabang_name')
            ->join('detail_users', 'users.id', '=', 'detail_users.id_user')
            ->leftJoin('users as cabang', 'detail_users.id_cabang', '=', 'cabang.id')
            ->where('users.role', 'KONSELOR')
            ->where('users.status', 'ACTIVE')
            ->where('detail_users.status_online', 'online')
            ->get();

        return view('keranjang', [
            'konselors' => $this->konselors,
        ]);
    }
}
