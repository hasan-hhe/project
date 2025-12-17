<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checktoken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
            if(!$request->hasHeader('Authorization')){
                return response()->json([
                    'message' => 'Authorization header is missing'
                ]);
            }
            $auth = $request->header('Authorization');

            if (!str_starts_with($auth, 'Bearer ')) {
                return response()->json([
                    'message' => 'Authorization header must be Bearer token'
                ]);
            }

            $token = trim(substr($auth, 7));

            if ($token === '') {
                return response()->json([
                    'message' => 'Bearer token is empty',
                    'code'    => 'TOKEN_EMPTY',
                ], 400);
            }

            // إذا كل شي تمام، نكمل للـ sanctum
            return $next($request);

        } 
    }


// app/Http/Controllers/AuthController.php
// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Str;

// class AuthController extends Controller
// {
//     public function logout(Request $request)
//     {
//         // 1) التحقق من وجود رأس Authorization
//         $auth = $request->header('Authorization');

//         if (!$auth) {
//             return response()->json([
//                 'message' => 'Authorization header is missing',
//                 'code' => 'AUTH_HEADER_MISSING',
//             ], 400);
//         }

//         // 2) التحقق من صيغة Bearer
//         if (!Str::startsWith($auth, 'Bearer ')) {
//             return response()->json([
//                 'message' => 'Authorization header must be Bearer token',
//                 'code' => 'AUTH_HEADER_INVALID',
//             ], 400);
//         }

//         $plainToken = trim(Str::replaceFirst('Bearer', '', $auth));

//         if ($plainToken === '') {
//             return response()->json([
//                 'message' => 'Bearer token is empty',
//                 'code' => 'TOKEN_EMPTY',
//             ], 400);
//         }

//         // 3) محاولة جلب المستخدم عبر sanctum
//         // ملاحظة: إذا كان التوكن غير صالح، auth()->user() ستكون null تحت auth:sanctum
//         // لكننا هنا نتعامل يدويًا لإرجاع رسالة أوضح
//         $user = $request->user();

//         if (!$user) {
//             return response()->json([
//                 'message' => 'Invalid or expired token',
//                 'code' => 'TOKEN_INVALID',
//             ], 401);
//         }

//         // 4) حذف التوكن الحالي إن وُجد
//         $currentToken = $user->currentAccessToken();

//         if (!$currentToken) {
//             // قد يحدث إذا لم يكن الطلب مصادقًا على توكن شخصي
//             return response()->json([
//                 'message' => 'No active access token found for this session',
//                 'code' => 'TOKEN_NOT_BOUND',
//             ], 401);
//         }

//         $currentToken->delete();

//         return response()->json([
//             'message' => 'Logged out successfully',
//         ], 200);
//     }
// }
