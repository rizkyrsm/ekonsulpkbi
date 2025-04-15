<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // pastikan User model di-import
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class KonselorController extends Controller
{   
    public function index()
    {
        $users = User::all(); // ambil semua data user
        $users = $users->where('role', '==', 'KONSELOR'); // filter user yang bukan admin
        return view('DashKonselor', compact('users')); // kirim data ke view
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
        ]);

        // Simpan ke database
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'KONSELOR',
        ]);

        return redirect()->back()->with('success', 'User berhasil dibuat.');
    }

    public function create() { /* show form to create product */ }
    public function show($id) { /* show one product */ }

    public function edit($id) { 
        $users = User::all()->where('role', '==', 'KONSELOR');
        $detail = User::findOrFail($id); // ambil data user berdasarkan id
        return view('DashKonselor', compact('users', 'detail')); // kirim data ke view
    }

    public function update(Request $request, $id) { 
        // Validasi input
        $requestParameter = [
            'name' => 'required|string|max:255',
        ];

        if ($request->filled('password')) {
            $requestParameter['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($requestParameter);

        // Update ke database
        $user = User::findOrFail($id);
        $user->name = $validated['name'];
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        $users = User::all()->where('role', '==', 'KONSELOR');

        return redirect('/konselor')->with('success', 'User berhasil diperbarui.');
    }
    public function destroy($id) { /* delete product */ }

}

