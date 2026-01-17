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
    #[OA\Get(path: "/notifications", tags: ["Notifications"], security: [["bearerAuth" => []]])]
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 10);
        $perPage = max(1, min(50, (int)$perPage));

        $ids = UserNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->pluck('id');

        $notifications = Notification::whereIn('id', $ids)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseHelper::success([
            'notifications' => NotificationResource::collection($notifications)
        ], 'تم جلب الإشعارات بنجاح.');
    }

    #[OA\Post(path: "/notifications/{id}/mark-seen", tags: ["Notifications"], security: [["bearerAuth" => []]])]
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

    #[OA\Get(path: "/notifications/unread/count", tags: ["Notifications"], security: [["bearerAuth" => []]])]
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

    #[OA\Get(path: "/notifications/user/list", tags: ["Notifications"], security: [["bearerAuth" => []]])]
    public function getUserNotifications(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 10);
        $perPage = max(1, min(50, (int)$perPage));
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
