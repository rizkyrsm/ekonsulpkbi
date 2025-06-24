<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ChMessage;
use Illuminate\Support\Facades\Log;

class ExtendedMessageController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'id' => 'required|integer', // ID lawan bicara
            'message' => 'nullable|string',
            'id_order' => 'nullable|integer',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $fromId = Auth::id();
        $toId = $request->input('id');
        $messageBody = trim($request->input('message'));
        $idOrder = $request->input('id_order');
        $attachmentPath = null;

        // Handle attachment jika ada
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('public/chatify/attachments', $filename);
        }

        // Simpan pesan
        $message = ChMessage::create([
            'id' => (string) Str::uuid(),
            'from_id' => $fromId,
            'to_id' => $toId,
            'body' => $messageBody,
            'attachment' => $attachmentPath,
            'id_order' => $idOrder,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan berhasil dikirim',
            'data' => $message,
        ]);
    }

    public function fetch(Request $request)
    {
        $userId = Auth::id();
        $contactId = $request->input('id');
        $idOrder = $request->input('id_order');

        $query = ChMessage::query()->where('id_order', $idOrder);

        $messages = $query->orderBy('created_at')->get();

        $response = '';
        foreach ($messages as $msg) {
            $response .= view('vendor.Chatify.layouts.messageCard', [
                'id' => $msg->id,
                'id_order' => $msg->id_order,
                'fromId' => $msg->from_id,
                'toId' => $msg->to_id,
                'message' => $msg->body,
                'attachment' => $msg->attachment ? Chatify::parseAttachment($msg->attachment) : null,
                'created_at' => $msg->created_at,
                'timeAgo' => $msg->created_at->diffForHumans(),
                'isSender' => $msg->from_id == $userId,
                'seen' => $msg->seen,
            ])->render();
        }

        return response()->json(['messages' => $response]);
    }
}
