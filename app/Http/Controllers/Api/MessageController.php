<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Get conversations list for the authenticated user
     */
    public function conversations(Request $request)
    {
        $user = $request->user();

        // Get all unique conversations (users who sent or received messages)
        $conversations = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver', 'commande'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($message) use ($user) {
                // Group by the other user in the conversation
                return $message->sender_id == $user->id 
                    ? $message->receiver_id 
                    : $message->sender_id;
            })
            ->map(function ($messages, $otherUserId) use ($user) {
                $lastMessage = $messages->first();
                $otherUser = $lastMessage->sender_id == $user->id 
                    ? $lastMessage->receiver 
                    : $lastMessage->sender;
                
                $unreadCount = Message::where('sender_id', $otherUserId)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();

                return [
                    'user_id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'photo' => $otherUser->photo,
                    'last_message' => $lastMessage->message,
                    'last_message_time' => $lastMessage->created_at->format('Y-m-d H:i:s'),
                    'unread_count' => $unreadCount,
                    'commande_id' => $lastMessage->commande_id,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    /**
     * Get messages between authenticated user and another user
     */
    public function messages(Request $request, $userId)
    {
        $user = $request->user();

        $messages = Message::where(function ($query) use ($user, $userId) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', $userId);
        })
        ->orWhere(function ($query) use ($user, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $user->id);
        })
        ->with(['sender', 'receiver', 'commande'])
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($message) {
            return [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'sender_name' => $message->sender->name,
                'sender_photo' => $message->sender->photo,
                'message' => $message->message,
                'type' => $message->type,
                'attachment' => $message->attachment,
                'is_read' => $message->is_read,
                'read_at' => $message->read_at?->format('Y-m-d H:i:s'),
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'commande_id' => $message->commande_id,
            ];
        });

        // Mark messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Send a message
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'type' => 'nullable|in:text,image,location,file',
            'attachment' => 'nullable|string',
            'commande_id' => 'nullable|exists:commandes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'commande_id' => $request->commande_id,
            'message' => $request->message,
            'type' => $request->type ?? 'text',
            'attachment' => $request->attachment,
        ]);

        $message->load(['sender', 'receiver', 'commande']);

        // Send push notification to receiver
        try {
            $fcmService = new \App\Services\Notifications\FCMService();
            $conversationId = min($user->id, $request->receiver_id) . '_' . max($user->id, $request->receiver_id);
            $fcmService->sendNewMessageNotification(
                $request->receiver_id,
                $user->name,
                $request->message,
                (int) $conversationId
            );
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Error sending FCM notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Message envoyé',
            'data' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'sender_name' => $message->sender->name,
                'sender_photo' => $message->sender->photo,
                'message' => $message->message,
                'type' => $message->type,
                'attachment' => $message->attachment,
                'is_read' => $message->is_read,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'commande_id' => $message->commande_id,
            ],
        ], 201);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, $userId)
    {
        $user = $request->user();

        $updated = Message::where('sender_id', $userId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Messages marqués comme lus',
            'updated_count' => $updated,
        ]);
    }
}

