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
    #[OA\Post(
        path: "/auth/register",
        summary: "Register a new user",
        description: "Register a new user account with personal information and documents",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["first_name", "last_name", "phone_number", "password", "identity_document_image"],
                    properties: [
                        new OA\Property(property: "first_name", type: "string", example: "أحمد"),
                        new OA\Property(property: "last_name", type: "string", example: "محمد"),
                        new OA\Property(property: "phone_number", type: "string", pattern: "^[0-9]+$", example: "0912345678"),
                        new OA\Property(property: "email", type: "string", format: "email", example: "ahmed@example.com"),
                        new OA\Property(property: "password", type: "string", format: "password", minLength: 6, example: "password123"),
                        new OA\Property(property: "date_of_birth", type: "string", format: "date", example: "1990-01-01"),
                        new OA\Property(property: "account_type", type: "string", enum: ["RENTER", "OWNER"], example: "RENTER"),
                        new OA\Property(property: "avatar_image", type: "string", format: "binary", description: "User avatar image (optional)"),
                        new OA\Property(property: "identity_document_image", type: "string", format: "binary", description: "Identity document image (required)"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Registration successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user", type: "object"),
                                new OA\Property(property: "token", type: "string", example: "1|xxxxxxxxxxxxx")
                            ]
                        ),
                        new OA\Property(property: "body", type: "string", example: "تم تسجيل الدخول بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
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
            return response()->json([
                'message' => 'error',
                'body' => $e->getMessage()
            ]);
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

    #[OA\Post(
        path: "/auth/login",
        summary: "User login",
        description: "Authenticate user and return access token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["phone_number", "password"],
                properties: [
                    new OA\Property(property: "phone_number", type: "string", pattern: "^[0-9]+$", example: "0912345678"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user", type: "object"),
                                new OA\Property(property: "token", type: "string", example: "1|xxxxxxxxxxxxx")
                            ]
                        ),
                        new OA\Property(property: "body", type: "string", example: "تم تسجيل الدخول بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Invalid credentials"),
            new OA\Response(response: 404, description: "User not found"),
        ]
    )]
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


    #[OA\Post(
        path: "/logout",
        summary: "User logout",
        description: "Revoke all user access tokens",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logout successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "null"),
                        new OA\Property(property: "body", type: "string", example: "Logout done!")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ResponseHelper::success(null, 'تم تسجيل الخروج بنجاح!');
    }
}
