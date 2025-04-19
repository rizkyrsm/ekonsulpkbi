<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DetailUser;

class ProfileDetail extends Component
{
    public $nama, $nik, $tgl_lahir, $alamat, $no_tlp, $status_online, $jenis_kelamin;

    public function mount()
    {
        $data = DetailUser::where('id_user', Auth::id())->first();

        if ($data) {
            $this->nama = $data->nama;
            $this->nik = $data->nik;
            $this->tgl_lahir = $data->tgl_lahir;
            $this->alamat = $data->alamat;
            $this->no_tlp = $data->no_tlp;
            $this->status_online = $data->status_online;
            $this->jenis_kelamin = $data->jenis_kelamin;
        }
    }

    public function updatedNik($value)
    {
        $this->nik = preg_replace('/\D/', '', $value);
    }

    public function updatedNoTlp($value)
    {
        $this->no_tlp = preg_replace('/\D/', '', $value);
    }

    public function save()
    {
        $this->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|numeric',
            'tgl_lahir' => 'required|date',
            'alamat' => 'required|string|max:255',
            'no_tlp' => 'required|numeric',
            'status_online' => 'required|in:online,offline',
            'jenis_kelamin' => 'required|in:LAKI-LAKI,PEREMPUAN,LAINYA',
        ]);

        DetailUser::updateOrCreate(
            ['id_user' => Auth::id()],
            [
                'nama' => $this->nama,
                'nik' => $this->nik,
                'tgl_lahir' => $this->tgl_lahir,
                'alamat' => $this->alamat,
                'no_tlp' => $this->no_tlp,
                'status_online' => $this->status_online,
                'jenis_kelamin' => $this->jenis_kelamin,
            ]
        );

        session()->flash('success', 'Profil berhasil disimpan!');
    }

    public function render()
    {
        return view('livewire.settings.profile-detail');
    }
}

