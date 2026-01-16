<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\UserRecource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

use function App\Helpers\uploadImage;

class AuthController extends Controller
{
    #[OA\Post(path: "/auth/register", tags: ["Authentication"])]
    public function register(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string',
                'avatar_image' => 'nullable|image|mimes:png,jpg,gif',
                'identity_document_image' => 'required|image|mimes:png,jpg,gif',
                'identity_document_image' => 'required|image|mimes:png,jpg,gif',
                'last_name' => 'required|string',
                'phone_number' => 'required|string|unique:users|regex:/^[0-9]+$/',
                'date_of_birth' => 'nullable|date',
                'account_type' => 'nullable|in:RENTER,OWNER',
                'email' => 'nullable|string|email|unique:users',
                'password' => 'required|min:6'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        $avatar = null;
        if ($request->hasFile('avatar_image'))
            $avatar = uploadImage($request->file('avatar_image'), 'avatars', 'public');


        $identity_document = uploadImage($request->file('identity_document_image'), 'identity_documents', 'public');

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $request->date_of_birth,
                'account_type' => $request->account_type,
                'email' => $request->email,
                'avatar_url' => $avatar,
                'identity_document_url' => $identity_document,
                'password' => Hash::make($request->password)
            ]);
        } catch (Exception $e) {
            return  ResponseHelper::error($e->getMessage(), 400);
        }

        return ResponseHelper::success([
            'user'  => new UserRecource($user)
        ], 'تم تسجيل الحساب بنجاح , بانتظار الموافقة من الادمن');
    }

    #[OA\Post(path: "/auth/login", tags: ["Authentication"])]
    public function login(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => 'required|string|regex:/^[0-9]+$/',
                'password' => 'required'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        try {
            $user = User::where('phone_number', $request->phone_number)->firstorFail();
        } catch (Exception $e) {
            return ResponseHelper::error('رقم الهاتف غير موجود', 404);
        }

        if ($user->status == 'PENDING') {
            return ResponseHelper::error('لم يتم الموافقة على حسابك بعد');
        }

        if (!Hash::check($request->password, $user->password)) {
            return ResponseHelper::error('كلمة المرور غير صحيحة', 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return ResponseHelper::success([
            'user'  => new UserRecource($user),
            'token' => $token,
        ], 'تم تسجيل الدخول بنجاح');
    }


    #[OA\Post(path: "/logout", tags: ["Authentication"], security: [["bearerAuth" => []]])]
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ResponseHelper::success(null, 'تم تسجيل الخروج بنجاح!');
    }
}
