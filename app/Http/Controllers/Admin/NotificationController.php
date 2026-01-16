<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Notification;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Events\NotificationSent;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function App\Helpers\sendNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::orderBy('id', 'DESC')
            ->get();
        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::orderBy('id', 'DESC')->where('account_type', '!=', 'ADMIN')->get();
        return view('admin.notifications.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNotificationRequest $request)
    {
        try {
            DB::beginTransaction();

            $notification = Notification::create([
                'title' => $request->input('title'),
                'body' => $request->input('body'),
                'is_active' => $request->input('is_active')
            ]);

            $notification->refresh();

            if ($request->has('items')) {
                foreach ($request->input('items') as $userId) {
                    $userNotification = UserNotification::create([
                        'user_id' => $userId,
                        'notification_id' => $notification->id,
                    ]);
                    sendNotification($notification, $userId);
                }
            }
            DB::commit();
            return redirect()->route('admin.notifications.index')->with('success', 'تمت إضافة الإشعار بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء إضافة الإشعار: ' . $e->getMessage());
        }
    }

    public function markAll()
    {
        try {
            $notifications = UserNotification::where('is_seen', 0)
                ->where('user_id', Auth::id())
                ->get();
            foreach ($notifications as $notification) {
                $notification->is_seen = 1;
                $notification->save();
            }
            return redirect()->back()->with('success', 'تم تحديث حالة الإشعارات بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث حالة الإشعارات');
        }
    }

    /**
     * Mark notification as seen
     */
    public function markAsSeen($id)
    {
        try {
            $userNotification = UserNotification::where('notification_id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if ($userNotification) {
                $userNotification->is_seen = 1;
                $userNotification->save();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        try {
            $count = UserNotification::where('user_id', Auth::id())
                ->where('is_seen', 0)
                ->count();

            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['count' => 0]);
        }
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications()
    {
        try {
            $userNotifications = UserNotification::where('user_id', Auth::id())
                ->with('notification')
                ->orderBy('id', 'DESC')
                ->limit(10)
                ->get();

            return response()->json(['notifications' => $userNotifications]);
        } catch (\Exception $e) {
            return response()->json(['notifications' => []]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return redirect()->back()->with('error', 'الإشعار غير موجود');
        }
        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return redirect()->back()->with('error', 'الإشعار غير موجود');
        }
        $users = User::where('account_type', '!=', 'ADMIN')->get();
        return view('admin.notifications.edit', compact('notification', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNotificationRequest $request, string $id)
    {
        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return redirect()->back()->with('error', 'الإشعار غير موجود');
            }
            DB::beginTransaction();
            $notification->update([
                'title' => $request->input('title'),
                'body' => $request->input('body'),
                'is_active' => $request->input('is_active'),
            ]);
            if ($request->has('users')) {
                $existingUserNotifications = UserNotification::where('notification_id', $notification->id)->get();
                foreach ($existingUserNotifications as $userNotification) {
                    if (!in_array($userNotification->user_id, $request->input('users'))) {
                        $userNotification->delete();
                    }
                }

                foreach ($request->input('users') as $userId) {
                    $exists = UserNotification::where('notification_id', $notification->id)
                        ->where('user_id', $userId)
                        ->exists();

                    if (!$exists) {
                        UserNotification::create([
                            'user_id' => $userId,
                            'notification_id' => $notification->id,
                        ]);
                        sendNotification($notification, $userId);
                    }
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'تم تحديث الإشعار بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث الإشعار: ' . $e->getMessage());
        }
    }

    public function toggleActive(string $id)
    {
        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return redirect()->back()->with('error', 'الإشعار غير موجود');
            }
            $notification->is_active = $notification->is_active == 0 ? 1 : 0;
            $notification->save();
            return redirect()->route('admin.notifications.index')->with('success', 'تم تغيير حالة الإشعار بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تغيير الحالة');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $notification = Notification::find($id);
            if (!$notification) {
                return redirect()->back()->with('error', 'الإشعار غير موجود');
            }
            Notification::destroy($id);
            DB::commit();
            return redirect()->route('admin.notifications.index')->with('success', 'تم حذف الإشعار بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الإشعار: ' . $e->getMessage());
        }
    }

    public function destroyAll()
    {
        try {
            DB::beginTransaction();
            $notifications = Notification::all();
            foreach ($notifications as $notification) {
                Notification::destroy($notification->id);
            }
            DB::commit();
            DB::statement('ALTER TABLE notifications AUTO_INCREMENT = 1;');
            return redirect()->route('admin.notifications.index')->with('success', 'تم حذف جميع الإشعارات بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الإشعارات: ' . $e->getMessage());
        }
    }

    public function destroyCheck(Request $request)
    {
        $request->validate([
            'notifications' => 'required|array',
        ]);
        try {
            DB::beginTransaction();

            foreach ($request->input('notifications') as $itemId) {
                $item = Notification::find($itemId);
                if ($item) {
                    Notification::destroy($itemId);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'تم حذف الإشعارات المحددة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الإشعارات: ' . $e->getMessage());
        }
    }
}
