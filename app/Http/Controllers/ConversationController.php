<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

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
}
