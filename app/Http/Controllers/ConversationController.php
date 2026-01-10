<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Events\MessageSent;
use App\Http\Resources\MesssageResource;
use App\Models\Conversation;
use App\Models\Message;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

use function App\Helpers\uploadImage;

class ConversationController extends Controller
{
    #[OA\Get(
        path: "/conversations",
        summary: "Get all conversations",
        description: "Retrieve all conversations for the authenticated user",
        tags: ["Conversations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, default: 10)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Conversations retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Conversations retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $perPage = min(max((int)$request->integer('per_page', 10), 1), 50);

        $conversations = Conversation::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        })
            ->with(['owner', 'renter', 'apartment', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success($conversations, 'Conversations retrieved successfully.');
    }

    #[OA\Get(
        path: "/conversations/{id}",
        summary: "Get conversation by ID",
        description: "Retrieve detailed information about a specific conversation",
        tags: ["Conversations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Conversation ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Conversation retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Conversation retrieved successfully.")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Conversation not found"),
        ]
    )]
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $conversation = Conversation::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        })
            ->where('id', $id)
            ->with(['owner', 'renter', 'apartment'])
            ->firstOrFail();

        return ResponseHelper::success($conversation, 'Conversation retrieved successfully.');
    }

    #[OA\Post(
        path: "/conversations",
        summary: "Create new conversation",
        description: "Create a new conversation between renter and apartment owner",
        tags: ["Conversations"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["owner_id", "apartment_id"],
                properties: [
                    new OA\Property(property: "owner_id", type: "integer", example: 2, description: "Apartment owner ID"),
                    new OA\Property(property: "apartment_id", type: "integer", example: 1, description: "Apartment ID"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Conversation created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Conversation created successfully.")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'apartment_id' => 'nullable|exists:apartments,id',
        ]);

        // Check if conversation already exists
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
            return ResponseHelper::success($existingConversation, 'Conversation already exists.');
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

            return ResponseHelper::success($conversation, 'Conversation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to create conversation: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: "/conversations/{id}/messages",
        summary: "Get conversation messages",
        description: "Retrieve all messages in a conversation",
        tags: ["Conversations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Conversation ID", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, default: 20)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Messages retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Messages retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getMessages(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $conversation = Conversation::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        })
            ->where('id', $id)
            ->firstOrFail();

        $perPage = min(max((int)$request->integer('per_page', 20), 1), 50);

        $messages = Message::where('conversation_id', $id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        return ResponseHelper::success($messages, 'Messages retrieved successfully.');
    }

    #[OA\Post(
        path: "/conversations/{id}/messages",
        summary: "Send message",
        description: "Send a message in a conversation (real-time via Reverb)",
        tags: ["Conversations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Conversation ID", schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["content"],
                properties: [
                    new OA\Property(property: "content", type: "string", maxLength: 1000, example: "Hello, is this apartment available?"),
                    new OA\Property(property: "attachment_url", type: "string", format: "url", nullable: true, example: "https://example.com/file.pdf"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Message sent successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Message sent successfully.")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function sendMessage(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
        }

        $conversation = Conversation::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        })
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'content' => 'required|string|max:1000',
            'attachment_url' => 'nullable|url',
        ]);

        try {
            DB::beginTransaction();

            $message = Message::create([
                'conversation_id' => $id,
                'sender_id' => $user->id,
                'content' => $request->content,
                'attachment_url' => $request->attachment_url,
            ]);

            $conversation->touch(); // Update updated_at

            $message->load('sender');

            DB::commit();

            // إرسال الرسالة عبر Laravel Reverb
            broadcast(new MessageSent($message, $id))->toOthers();

            return ResponseHelper::success($message, 'Message sent successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to send message: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: "/conversations/{id}/mark-read",
        summary: "Mark messages as read",
        description: "Mark all unread messages in a conversation as read",
        tags: ["Conversations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Conversation ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Messages marked as read successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Messages marked as read successfully.")
                    ]
                )
            ),
        ]
    )]
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return ResponseHelper::error('Unauthenticated.', 401);
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

            return ResponseHelper::success(null, 'Messages marked as read.');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to mark messages as read: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: "/conversations/delete",
        summary: "Delete conversation",
        description: "Delete a conversation and all its messages. Only participants (owner or renter) can delete the conversation.",
        tags: ["Conversations"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["conversation_id"],
                properties: [
                    new OA\Property(property: "conversation_id", type: "integer", example: 1, description: "Conversation ID to delete"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Conversation deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object", nullable: true),
                        new OA\Property(property: "body", type: "string", example: "Conversation deleted successfully.")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Forbidden - User is not a participant"),
            new OA\Response(response: 404, description: "Conversation not found"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Server error"),
        ]
    )]
    public function deleteConversation(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|exists:conversations,id'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to delete conversation: ' . $e->getMessage(), 500);
        }
        $user = $request->user();
        $conversationId = $request->conversation_id;

        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->renter_id != $user->id && $conversation->owner_id != $user->id) {
            return ResponseHelper::error('you are not practice in this conversation!', 403);
        }

        Message::where('conversation_id', $conversationId)->delete();

        $conversation->delete();

        return ResponseHelper::success(null, 'Conversation deleted successfully.');
    }

    #[OA\Post(
        path: "/conversations/delete-message",
        summary: "Delete message",
        description: "Delete a message. Only the message sender can delete their own message. Attachment files are also deleted.",
        tags: ["Conversations"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["message_id"],
                properties: [
                    new OA\Property(property: "message_id", type: "integer", example: 1, description: "Message ID to delete"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Message deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object", nullable: true),
                        new OA\Property(property: "body", type: "string", example: "Message deleted successfully.")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Forbidden - User can only delete their own messages"),
            new OA\Response(response: 404, description: "Message not found"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Server error"),
        ]
    )]
    public function deleteMessage(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to delete message: ' . $e->getMessage(), 500);
        }
        $user = $request->user();
        $messageId = $request->message_id;
        $message = Message::findOrFail($messageId);

        if ($message->sender_id != $user->id) {
            return ResponseHelper::error('you can delete your message only!', 403);
        }

        $attachment = $message->attachment_url;
        if ($attachment != null && Storage::disk('public')->exists($attachment))
            Storage::disk('public')->delete($attachment);

        $message->delete();

        return ResponseHelper::success(null, 'Message deleted successfully.');
    }

    #[OA\Post(
        path: "/conversations/update-message",
        summary: "Update message",
        description: "Update a message content and/or attachment. Only the message sender can update their own message. Old attachment is deleted if a new one is provided.",
        tags: ["Conversations"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["message_id", "content"],
                    properties: [
                        new OA\Property(property: "message_id", type: "integer", example: 1, description: "Message ID to update"),
                        new OA\Property(property: "content", type: "string", example: "Updated message content", description: "New message content"),
                        new OA\Property(property: "attachment", type: "string", format: "binary", nullable: true, description: "New attachment file (optional)"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Message updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Message updated successfully.")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Forbidden - User can only update their own messages"),
            new OA\Response(response: 404, description: "Message not found"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Server error"),
        ]
    )]
    public function updateMessage(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id',
                'content' => 'required|string',
                'attachment' => 'nullable'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update message: ' . $e->getMessage(), 500);
        }

        $user = $request->user();
        $messageId = $request->message_id;
        $message = Message::findOrFail($messageId);

        if ($message->sender_id != $user->id) {
            return ResponseHelper::error('you can update your message only!', 403);
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

        return ResponseHelper::success(new MesssageResource($message), 'Message updated successfully.');
    }

    #[OA\Post(
        path: "/conversations/message-info",
        summary: "Get message information",
        description: "Retrieve detailed information about a specific message",
        tags: ["Conversations"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["message_id"],
                properties: [
                    new OA\Property(property: "message_id", type: "integer", example: 1, description: "Message ID"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Message information retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Message info retrieved successfully.")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Message not found"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Server error"),
        ]
    )]
    public function messageInfo(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:messages,id'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to get message info: ' . $e->getMessage(), 500);
        }

        $message = Message::findorFail($request->message_id);

        return ResponseHelper::success(new MesssageResource($message), 'Message info retrieved successfully.');
    }
}
