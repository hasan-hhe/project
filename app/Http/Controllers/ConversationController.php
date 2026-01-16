<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Events\MessageSent;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MesssageResource;
use App\Models\Conversation;
use App\Models\Message;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

use function App\Helpers\uploadImage;

class ConversationController extends Controller
{
    #[OA\Get(path: "/conversations", tags: ["Conversations"], security: [["bearerAuth" => []]])]
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 10);
        $perPage = max(1, min(50, (int)$perPage));

        $conversations = Conversation::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        })
            ->with(['owner', 'renter', 'apartment', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'conversations' => ConversationResource::collection($conversations)
        ], 'تم جلب المحادثات بنجاح.');
    }

    #[OA\Get(path: "/conversations/{id}", tags: ["Conversations"], security: [["bearerAuth" => []]])]
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $conversation = Conversation::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        })
            ->where('id', $id)
            ->with(['owner', 'renter', 'apartment'])
            ->firstOrFail();

        return ResponseHelper::success([
            'conversation' => new ConversationResource($conversation)
        ], 'تم جلب المحادثة بنجاح.');
    }

    #[OA\Post(path: "/conversations", tags: ["Conversations"], security: [["bearerAuth" => []]])]
    public function store(Request $request)
    {
        $user = $request->user();

        try {
            $request->validate([
                'owner_id' => 'required|exists:users,id',
                'apartment_id' => 'required|exists:apartments,id',
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        $existingConversation = Conversation::where(function ($query) use ($user, $request) {
            $query->where(function ($q) use ($user, $request) {
                $q->where('owner_id', $request->owner_id)
                    ->where('renter_id', $user->id);
            })->orWhere(function ($q) use ($user, $request) {
                $q->where('owner_id', $user->id)
                    ->where('renter_id', $request->owner_id);
            });
        })
            ->first();

        if ($existingConversation) {
            $existingConversation->load(['owner', 'renter', 'apartment']);
            return ResponseHelper::success([
                'conversation' => new ConversationResource($existingConversation)
            ], 'المحادثة موجودة بالفعل.');
        }

        try {
            DB::beginTransaction();

            $conversation = Conversation::create([
                'owner_id' => $request->owner_id,
                'renter_id' => $user->id,
                'apartment_id' => $request->apartment_id,
            ]);

            $conversation->load(['owner', 'renter', 'apartment']);

            DB::commit();

            return ResponseHelper::success([
                'conversation' => new ConversationResource($conversation)
            ], 'تم إنشاء المحادثة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في إنشاء المحادثة: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(path: "/conversations/{id}/messages", tags: ["Conversations"], security: [["bearerAuth" => []]])]
    public function getMessages(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('غير مصرح لك.', 401);
        }

        $conversation = Conversation::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        })
            ->where('id', $id)
            ->firstOrFail();

        $perPage = $request->get('per_page', 20);
        $perPage = max(1, min(50, (int)$perPage));

        $messages = Message::where('conversation_id', $id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'messages' => MesssageResource::collection($messages)
        ], 'تم جلب الرسائل بنجاح.');
    }

    #[OA\Post(path: "/conversations/{id}/messages", tags: ["Conversations"], security: [["bearerAuth" => []]])]
    public function sendMessage(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('غير مصرح لك.', 401);
        }

        $conversation = Conversation::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        })
            ->where('id', $id)
            ->firstOrFail();

        try {
            $request->validate([
                'content' => 'required|string|max:1000',
                'attachment_url' => 'nullable|url',
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        try {
            DB::beginTransaction();

            $message = Message::create([
                'conversation_id' => $id,
                'sender_id' => $user->id,
                'content' => $request->content,
                'attachment_url' => $request->attachment_url,
            ]);

            $conversation->touch();

            $message->load('sender');

            DB::commit();

            broadcast(new MessageSent($message, $id));

            return ResponseHelper::success([
                'message' => new MesssageResource($message)
            ], 'تم إرسال الرسالة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('فشل في إرسال الرسالة: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(path: "/conversations/{id}/mark-read", tags: ["Conversations"], security: [["bearerAuth" => []]])]
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('غير مصرح لك.', 401);
        }

        $conversation = Conversation::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        })
            ->where('id', $id)
            ->firstOrFail();

        try {
            Message::where('conversation_id', $id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return ResponseHelper::success(null, 'تم تحديد الرسائل كمقروءة.');
        } catch (\Exception $e) {
            return ResponseHelper::error('فشل في تحديد الرسائل كمقروءة: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(path: "/conversations/delete", tags: ["Conversations"], security: [["bearerAuth" => []]])]
    public function deleteConversation(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|exists:conversations,id'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }
        $user = $request->user();
        $conversationId = $request->conversation_id;

        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->renter_id != $user->id && $conversation->owner_id != $user->id) {
            return ResponseHelper::error('أنت لست مشاركاً في هذه المحادثة!', 403);
        }

        Message::where('conversation_id', $conversationId)->delete();

        $conversation->delete();

        return ResponseHelper::success(null, 'تم حذف المحادثة بنجاح.');
    }

    #[OA\Post(path: "/conversations/delete-message", tags: ["Conversations"], security: [["bearerAuth" => []]])]
    public function deleteMessage(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        $user = $request->user();
        $messageId = $request->message_id;
        $message = Message::findOrFail($messageId);

        if ($message->sender_id != $user->id) {
            return ResponseHelper::error('يمكنك حذف رسائلك فقط!', 403);
        }

        $attachment = $message->attachment_url;
        if ($attachment != null && Storage::disk('public')->exists($attachment))
            Storage::disk('public')->delete($attachment);

        $message->delete();

        return ResponseHelper::success(null, 'تم حذف الرسالة بنجاح.');
    }

    #[OA\Post(path: "/conversations/update-message", tags: ["Conversations"], security: [["bearerAuth" => []]])]
    public function updateMessage(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id',
                'content' => 'required|string',
                'attachment' => 'nullable'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        $user = $request->user();
        $messageId = $request->message_id;
        $message = Message::findOrFail($messageId);

        if ($message->sender_id != $user->id) {
            return ResponseHelper::error('يمكنك تحديث رسائلك فقط!', 403);
        }

        $attachment = $message->attachment_url;
        if ($attachment != null && Storage::disk('public')->exists($attachment))
            Storage::disk('public')->delete($attachment);

        if ($request->hasFile('attachment')) {
            $attachment = uploadImage($request->file('attachment'), 'attachments', 'public');
        } else
            $attachment = null;

        $message->update([
            'content' => $request->content,
            'attachment_url' => $attachment,
            'is_read' => false,
            'read_at' => null
        ]);

        return ResponseHelper::success([
            'message' => new MesssageResource($message)
        ], 'تم تحديث الرسالة بنجاح.');
    }

    #[OA\Post(path: "/conversations/message-info", tags: ["Conversations"], security: [["bearerAuth" => []]])]


    public function messageInfo(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        $message = Message::findorFail($request->message_id);

        return ResponseHelper::success([
            'message' => new MesssageResource($message)
        ], 'تم جلب معلومات الرسالة بنجاح.');
    }
}
