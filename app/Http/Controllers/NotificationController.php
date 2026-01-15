<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\NotificationResource;
use App\Models\UserNotification;
use App\Models\Notification;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    #[OA\Get(
        path: "/notifications",
        summary: "Get all notifications",
        description: "Retrieve all notifications for the authenticated user",
        tags: ["Notifications"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, default: 10)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Notifications retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Notifications retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();

        $perPage = min(max((int)$request->integer('per_page', 10), 1), 50);

        $notifications = UserNotification::where('user_id', $user->id)
            ->with('notification')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'notifications' => NotificationResource::collection($notifications)
        ], 'تم جلب الإشعارات بنجاح.');
    }

    #[OA\Post(
        path: "/notifications/{id}/mark-seen",
        summary: "Mark notification as seen",
        description: "Mark a specific notification as seen",
        tags: ["Notifications"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "Notification ID", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Notification marked as seen",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Notification marked as seen.")
                    ]
                )
            ),
        ]
    )]
    public function markAsSeen(Request $request, $id)
    {
        $user = $request->user();

        $userNotification = UserNotification::where('notification_id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $userNotification->update([
            'is_seen' => true,
        ]);

        return ResponseHelper::success(null, 'تم تحديد الإشعار كمقروء.');
    }

    #[OA\Get(
        path: "/notifications/unread/count",
        summary: "Get unread notifications count",
        description: "Get the count of unread notifications for the authenticated user",
        tags: ["Notifications"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Unread count retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "unread_count", type: "integer", example: 5)
                            ]
                        ),
                        new OA\Property(property: "body", type: "string", example: "Unread count retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();

        $count = UserNotification::where('user_id', $user->id)
            ->where('is_seen', false)
            ->where('is_active', true)
            ->count();

        return ResponseHelper::success([
            'unread_count' => $count,
        ], 'تم جلب عدد الإشعارات غير المقروءة بنجاح.');
    }

    #[OA\Get(
        path: "/notifications/user/list",
        summary: "Get user notifications",
        description: "Retrieve user notifications with optional filter for unread only",
        tags: ["Notifications"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, default: 10)),
            new OA\Parameter(name: "only_unread", in: "query", description: "Filter only unread notifications", schema: new OA\Schema(type: "boolean", default: false)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Notifications retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Notifications retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getUserNotifications(Request $request)
    {
        $user = $request->user();
        $perPage = min(max((int)$request->integer('per_page', 10), 1), 50);
        $onlyUnread = $request->boolean('only_unread', false);

        $query = UserNotification::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('is_seen', $onlyUnread)
            ->pluck('notification_id');

        $notifications = Notification::whereIn('id', $query)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'notifications' => NotificationResource::collection($notifications)
        ], 'تم جلب الإشعارات بنجاح.');
    }
}
