<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Models\DetailUser;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Livewire\WithPagination;

class UserCreate extends Component
{
    use WithPagination;

    public $name, $email, $password, $role;
    public $editingId = null;

    public $search = '';
    public $searchRole = '';

    protected $paginationTheme = 'tailwind'; // biar paging pakai Tailwind

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSearchRole()
    {
        $this->resetPage();
    }

    public function submit()
    {
        $this->validate();

        if ($this->editingId) {
            $user = User::find($this->editingId);
            $user->update([
                'name' => $this->name,
                'role' => $this->role,
                'password' => $this->password ? Hash::make($this->password) : $user->password,
            ]);
            session()->flash('success', 'User berhasil diperbarui.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role' => $this->role,
            ]);

            // Mengambil ID user yang sedang login sebagai ID cabang
            $id_cabang = auth()->user()->id;
            $userRole = auth()->user()->role;

            // Log ID cabang dan role
            \Log::info('ID Cabang: ' . $id_cabang);
            \Log::info('User Role: ' . $userRole);

            if ($userRole === 'CABANG') {
                // Buat atau perbarui data DetailUser dengan ID cabang dari user login
                DetailUser::updateOrCreate(
                    ['id_user' => $user->id], // Kriteria pencarian
                    [   // Data yang akan disimpan/diperbarui
                        'id_cabang' => $id_cabang,
                        'nama' => '',
                        'nik' => '',
                        'tgl_lahir' => now(),
                        'alamat' => '',
                        'no_tlp' => '',
                        'status_online' => 'online',
                        'jenis_kelamin' => 'LAINYA',
                    ]
                );
            }

            session()->flash('success', 'User berhasil ditambahkan.');
        }

        $this->resetForm();
    }


    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = '';
        $this->editingId = null;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->role = $user->role;
        $this->password = '';
        $this->email = $user->email;
    }

    public function delete($id)
    {
        User::find($id)->delete();
        session()->flash('success', 'User berhasil dihapus.');
        $this->resetForm();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => $this->editingId ? 'nullable' : 'required|email|unique:users,email',
            'password' => $this->editingId ? 'nullable|min:8' : 'required|min:8',
            'role' => 'required|in:ADMIN,CABANG,KONSELOR',
        ];
    }

    public function render()
{
    $userRole = Auth()->user()->role;
    $query = User::query();

    // Jika role CABANG, tampilkan konselor dengan id_cabang yang sama
    if ($userRole === 'CABANG') {
        $idCabang = Auth()->user()->id;

        $query->join('detail_users', 'users.id', '=', 'detail_users.id_user')
            ->where('users.role', 'KONSELOR')
            ->where('detail_users.id_cabang', $idCabang);
    }
    // Jika role KONSELOR, tampilkan pengguna biasa
    else if ($userRole === 'KONSELOR') {
        $query->where('role', 'USER');
    }
    // Jika role USER, tidak menampilkan pengguna lain
    else if ($userRole === 'USER') {
        $users = collect(); // Koleksi kosong
    }
    // Jika bukan CABANG, KONSELOR, atau USER, tampilkan semua selain pengguna saat ini
    else {
        $query->where('id', '!=', Auth()->user()->id);
    }

    // Pencarian dan pengurutan
    $users = $query
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('users.name', 'like', '%' . $this->search . '%')
                    ->orWhere('users.email', 'like', '%' . $this->search . '%');
            });
        })
        ->orderBy('users.created_at', 'desc')
        ->select('users.*') // Pilih semua kolom dari tabel users
        ->paginate(5);

    return view('livewire.dashboard.user-create', compact('users'));
}



    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->status = $user->status === 'ACTIVE' ? 'NONACTIVE' : 'ACTIVE';
        $user->save();
    }
}
