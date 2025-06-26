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
            'id' => 'required|integer',
            'message' => 'nullable|string',
            'id_order' => 'nullable|integer',
            'file' => 'nullable|file|max:10240',
        ]);

        $fromId = Auth::id();
        $toId = $request->input('id');
        $messageBody = trim($request->input('message'));
        $idOrder = $request->input('id_order');
        $attachmentPath = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('public/chatify/attachments', $filename);
        }

        $message = ChMessage::create([
            'id' => (string) Str::uuid(),
            'from_id' => $fromId,
            'to_id' => $toId,
            'body' => $messageBody,
            'attachment' => $attachmentPath,
            'id_order' => $idOrder,
            'seen' => 0,
        ]);

        // Pesan untuk pengirim (diri sendiri)
        $renderedMessageSender = view('vendor.Chatify.layouts.messageCard', [
            'id' => $message->id,
            'id_order' => $message->id_order,
            'fromId' => $message->from_id,
            'toId' => $message->to_id,
            'message' => $message->body,
            'attachment' => $message->attachment ? \Chatify\Facades\ChatifyMessenger::parseAttachment($message->attachment) : null,
            'created_at' => $message->created_at,
            'timeAgo' => $message->created_at->diffForHumans(),
            'isSender' => true,
            'seen' => 0,
        ])->render();

        // Pesan untuk penerima (dikirim via Pusher)
        $renderedMessageReceiver = view('vendor.Chatify.layouts.messageCard', [
            'id' => $message->id,
            'id_order' => $message->id_order,
            'fromId' => $message->from_id,
            'toId' => $message->to_id,
            'message' => $message->body,
            'attachment' => $message->attachment ? \Chatify\Facades\ChatifyMessenger::parseAttachment($message->attachment) : null,
            'created_at' => $message->created_at,
            'timeAgo' => $message->created_at->diffForHumans(),
            'isSender' => false,
            'seen' => 0,
        ])->render();

        // Kirim ke Pusher untuk real-time muncul di penerima
        try {
            $pusher = new \Pusher\Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );

            $pusher->trigger("private-chatify.{$toId}", 'messaging', [
                'from_id' => $fromId,
                'to_id' => $toId,
                'message' => $renderedMessageReceiver,
            ]);
        } catch (\Exception $e) {
            \Log::error("Pusher trigger failed: " . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => $renderedMessageSender,
            'tempID' => $request->input('temporaryMsgId'),
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
