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
            'role' => $validated['role'],
        ]);

        return redirect()->back()->with('success', 'User berhasil dibuat.');
    }

    public function create() { /* show form to create product */ }
    public function show($id) { /* show one product */ }
    public function edit($id) { /* show form to edit product */ }
    public function update(Request $request, $id) { /* update product */ }
    public function destroy($id) { /* delete product */ }

}

