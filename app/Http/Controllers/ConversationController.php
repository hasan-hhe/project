<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversationResource;
use App\Http\Resources\MesssageResource;
use App\Models\Apartment;
use App\Models\Conversation;
use App\Models\Message;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::where('owner_id', $user->id)
            ->orWhere('renter_id', $user->id)
            ->with(['owner', 'renter', 'apartment'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
        if ($conversations == null)
            return response()->json([
                'message' => 'no conversations!, start new conversantion!'
            ]);
        $data = ConversationResource::collection($conversations);

        return response()->json([
            'status' => true,
            'conversations' => $data,
        ]);
    }

    public function renterStartConversation(Request $request)
    {
        try {
            $request->validate([
                'apartment_id' => 'required|exists:apartments,id'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }

        $user = $request->user();
        $apartmentId = $request->apartment_id;
        $existingConversation = Conversation::where('renter_id', $user->id)
            ->where('apartment_id', $apartmentId)
            ->first();

        if ($existingConversation != null) {
            return response()->json([
                'success' => false,
                'message' => 'conversation is created acctually!',
                // 'conversation' => new ConversationResource($existingConversation)
            ]);
        }

        $apartment = Apartment::findorFail($apartmentId);

        $conversation = Conversation::create([
            'renter_id' => $user->id,
            'owner_id' => $apartment->owner_id,
            'apartment_id' => $apartmentId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'you start new conversation!',
            'conversation' => new ConversationResource($conversation),
        ], 201);
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'attachment' => 'nullable',
                'content' => 'required|string',
                'conversation_id' => 'required|exists:conversations,id',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
        $user = $request->user();
        $conversationId = $request->conversation_id;

        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->renter_id != $user->id && $conversation->owner_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'you are not practice in this conversation!'
            ], 403);
        }

        $attachment_url = null;
        if ($request->hasFile('attachment'))
            $attachment_url = $request->file('attachment')->store('attachments', 'public');

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'attachment_url' => $attachment_url,
            'content' => $request->content,
        ]);

        $conversation->touch();

        // (اختياري) إرسال إشعار في الوقت الحقيقي
        // broadcast(new NewMessage($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => new MesssageResource($message)
        ], 201);
    }

    public function getMessages(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|exists:conversations,id'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }

        $user = $request->user();
        $conversationId = $request->conversation_id;

        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->renter_id != $user->id && $conversation->owner_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'you are not practice in this conversation!'
            ], 403);
        }

        $messages = Message::where('conversation_id', $conversationId)
            ->paginate(20);

        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        $data = MesssageResource::collection($messages);

        return response()->json([
            'success' => true,
            'messages' => $data
        ]);
    }

    public function deleteConversation(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|exists:conversations,id'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
        $user = $request->user();
        $conversationId = $request->conversation_id;

        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->renter_id != $user->id && $conversation->owner_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'you are not practice in this conversation!'
            ], 403);
        }

        Message::where('conversation_id', $conversationId)->delete();

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'conversation deleted succesfully!'
        ]);
    }

    public function deleteMessage(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
        $user = $request->user();
        $messageId = $request->message_id;
        $message = Message::findOrFail($messageId);

        if ($message->sender_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'you can delete your message only!'
            ], 403);
        }

        $attachment = $message->attachment_url;
        if ($attachment != null && Storage::disk('public')->exists($attachment))
            Storage::disk('public')->delete($attachment);

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'message deleted seccesfully'
        ]);
    }

    public function updateMessage(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id',
                'content' => 'required|string',
                'attachment' => 'nullable'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }

        $user = $request->user();
        $messageId = $request->message_id;
        $message = Message::findOrFail($messageId);

        if ($message->sender_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'you can update your message only!'
            ], 403);
        }

        $attachment = $message->attachment_url;
        if ($attachment != null && Storage::disk('public')->exists($attachment))
            Storage::disk('public')->delete($attachment);

        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment')->store('attachments', 'public');
        } else
            $attachment = null;

        $message->update([
            'content' => $request->content,
            'attachment_url' => $attachment,
            'is_read' => false,
            'read_at' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'message updated seccesfully',
            'body' => new MesssageResource($message)
        ]);
    }

    public function messageInfo(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }

        $message = Message::findorFail($request->message_id);

        return response()->json([
            'success' => true,
            'body' => new MesssageResource($message),
            'read_at' => $message->read_at
        ]);
    }
}
