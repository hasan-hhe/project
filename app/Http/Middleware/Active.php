<?php

namespace App\Http\Middleware;

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
            if (Auth::user()->is_active) {
                return $next($request);
            }
            return response()->json([
                'message' => 'error',
                'body' => 'تم حظر هذا الحساب من قبل المشرف , تواصل مع المشرف لمعرفة السبب.',
            ]);;
        }
        return response()->json([
            'message' => 'error',
            'body' => 'هذا الحساب غير مسجل دخول',
        ]);
    }
}
