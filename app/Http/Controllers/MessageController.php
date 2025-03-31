<?php

namespace App\Http\Controllers;

use App\Jobs\SendMessage;
use App\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    private function generateChatId($friendId, $userId): string
    {
        return $userId < $friendId
            ? "chat.{$userId}_{$friendId}"
            : "chat.{$friendId}_{$userId}";
    }

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function createChannel(Request $request): JsonResponse{
        $channelName = $this->generateChatId($request->friend_id, $request->user()->id);
        new PrivateChannel($channelName);
        return response()->json($channelName);
    }

    public function messages(Request $request): JsonResponse {
        $friendId = $request->friend_id;
        $userId = $request->user()->id;
        $chatId = $this->generateChatId($friendId, $userId);
        $messages = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get()->append('time');

        return response()->json($messages);
    }

    public function message(Request $request): JsonResponse {
        $friendId = $request->friend_id;
        $userId = $request->user()->id;
        $channelName = $this->generateChatId($friendId, $userId);
        $message = Message::create([
            'user_id' => auth()->id(),
            'chat_id' => $channelName,
            'text' => $request->get('text'),
        ]);
        SendMessage::dispatch($message, $channelName);

        return response()->json([
            'success' => true,
            'message' => "Message created and job dispatched.",
        ]);
    }
}
