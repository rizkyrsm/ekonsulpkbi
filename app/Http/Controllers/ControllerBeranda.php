<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // pastikan User model di-import

class ControllerBeranda extends Controller
{
    public function listuser()
    {
        $users = User::all(); // ambil semua data user
        $users = $users->where('role', '==', 'KONSELOR'); // filter user yang bukan admin
        return view('landing', compact('users')); // kirim data ke view
    }

    public function startChat($receiverId)
    {
        $conversation = Conversation::firstOrCreate(
            [
                'sender_id' => auth()->id(),
                'receiver_id' => $receiverId,
            ],
            [
                'sender_id' => auth()->id(),
                'receiver_id' => $receiverId,
            ]
        );

        return redirect()->route('chat.show', $conversation->id);
    }
}
