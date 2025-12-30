<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use App\Models\UserNotification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()) {
            if (Auth::user()->account_type == 'ADMIN') {
                $notificationsIds = UserNotification::where('user_id', Auth::id())
                    ->where('is_seen', 0)
                    ->get()->map->notification_id;
                $notifications = Notification::whereIn('id', $notificationsIds)->get();
                View::share('notificationsNav', $notifications);
                return $next($request);
            }
            return redirect()->route('admin.auth.login');
        }
        return redirect()->route('admin.auth.login');
    }
}
