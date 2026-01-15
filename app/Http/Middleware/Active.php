<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Active
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()) {
            if (Auth::user()->is_active || Auth::user()->status == 'REJECTED') {
                return $next($request);
            }
            return ResponseHelper::error('تم حظر هذا الحساب من قبل المشرف , تواصل مع المشرف لمعرفة السبب.', 403);
        }
        return ResponseHelper::error('هذا الحساب غير مسجل دخول.', 401);
    }
}
