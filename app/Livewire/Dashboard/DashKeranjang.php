<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Layanan;
use App\Models\Diskon;
use App\Models\User;

class DashKeranjang extends Component
{
    public $layanans;
    public $voucher;
    public $potongan = 0;
    public $hargaSetelahDiskon = [];
    public $message;
    public $vouchernofalid;
    public $jenispotongan;
    public $konselors;

    public function mount($id = null)
    {
        // Mengambil data layanan
        $this->layanans = $id ? Layanan::where('id_layanan', $id)->get() : Layanan::all();
        $this->resetHarga();

        // Mengambil data semua user dengan role "konselor" dan status "active"
        $this->konselors = User::select('users.*', 'detail_users.*')
            ->join('detail_users', 'users.id', '=', 'detail_users.id_user')
            ->where('users.role', 'KONSELOR')
            ->where('users.status', 'ACTIVE')
            ->get();
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
        }
    }

    public function render()
    {
        return view('keranjang', [
            'konselors' => $this->konselors,
        ]);
    }
}
