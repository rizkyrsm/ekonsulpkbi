<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
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
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role' => $this->role,
            ]);
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

        if ($userRole === 'CABANG') {
            $query->where('role', 'KONSELOR');
        } else if ($userRole === 'KONSELOR') {
            $query->where('role', 'USER');
        } else if ($userRole === 'USER') {
            // Tidak menampilkan data pengguna lain
            $users = collect(); // Koleksi kosong
        } else {
            $users = $query->where('id', '!=', Auth()->user()->id)
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(5);
        }

        return view('livewire.dashboard.user-create', compact('users'));
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->status = $user->status === 'ACTIVE' ? 'NONACTIVE' : 'ACTIVE';
        $user->save();
    }

}
