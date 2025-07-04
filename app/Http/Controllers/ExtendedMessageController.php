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
        $newName = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $oldName = $file->getClientOriginalName();
            $newName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('attachments', $newName, 'public');
        }

        $message = ChMessage::create([
            'id' => (string) Str::uuid(),
            'from_id' => $fromId,
            'to_id' => $toId,
            'body' => $messageBody,
            'attachment' => $newName,
            'id_order' => $idOrder,
            'seen' => 0,
        ]);

        $renderedMessageSender = view('vendor.Chatify.layouts.messageCard', [
            'id' => $message->id,
            'id_order' => $message->id_order,
            'fromId' => $message->from_id,
            'toId' => $message->to_id,
            'message' => $message->body,
            'attachment' => $newName,
            'created_at' => $message->created_at,
            'timeAgo' => $message->created_at->diffForHumans(),
            'isSender' => true,
            'seen' => 0,
        ])->render();

        $renderedMessageReceiver = view('vendor.Chatify.layouts.messageCard', [
            'id' => $message->id,
            'id_order' => $message->id_order,
            'fromId' => $message->from_id,
            'toId' => $message->to_id,
            'message' => $message->body,
            'attachment' => $newName,
            'created_at' => $message->created_at,
            'timeAgo' => $message->created_at->diffForHumans(),
            'isSender' => false,
            'seen' => 0,
        ])->render();

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

        if (!$contactId || !$idOrder) {
            return response()->json(['messages' => '']);
        }

        $messages = ChMessage::where('id_order', $idOrder)
            ->where(function ($q) use ($userId, $contactId) {
                $q->where(function ($query) use ($userId, $contactId) {
                    $query->where('from_id', $userId)->where('to_id', $contactId);
                })->orWhere(function ($query) use ($userId, $contactId) {
                    $query->where('from_id', $contactId)->where('to_id', $userId);
                });
            })
            ->orderBy('created_at')
            ->get();

        $response = '';
        foreach ($messages as $msg) {
            $response .= view('vendor.Chatify.layouts.messageCard', [
                'id' => $msg->id,
                'id_order' => $msg->id_order,
                'fromId' => $msg->from_id,
                'toId' => $msg->to_id,
                'message' => $msg->body,
                'attachment' => $msg->attachment,
                'created_at' => $msg->created_at,
                'timeAgo' => $msg->created_at->diffForHumans(),
                'isSender' => $msg->from_id == $userId,
                'seen' => $msg->seen,
            ])->render();
        }

        return response()->json(['messages' => $response]);
    }
}
