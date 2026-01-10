<?php

namespace App\Http\Controllers;

// use Carbon\Carbon;

use App\Helpers\ResponseHelper;
use App\Http\Resources\UserRecource;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

use function App\Helpers\uploadImage;

class profileController extends Controller
{
    #[OA\Get(
        path: "/my-profile",
        summary: "Get user profile",
        description: "Retrieve authenticated user's profile information",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "first_name", type: "string", example: "أحمد"),
                                new OA\Property(property: "last_name", type: "string", example: "محمد"),
                                new OA\Property(property: "phone_number", type: "string", example: "0912345678"),
                                new OA\Property(property: "email", type: "string", nullable: true, example: "ahmed@example.com"),
                                new OA\Property(property: "account_type", type: "string", enum: ["RENTER", "OWNER", "ADMIN"], example: "RENTER"),
                                new OA\Property(property: "status", type: "string", enum: ["PENDING", "APPROVED", "REJECTED"], example: "APPROVED"),
                                new OA\Property(property: "date_of_birth", type: "string", format: "date", nullable: true, example: "1990-01-01"),
                                new OA\Property(property: "avatar_url", type: "string", nullable: true),
                                new OA\Property(property: "identity_document_url", type: "string", nullable: true),
                            ]
                        ),
                        new OA\Property(property: "body", type: "string", example: "Profile retrieved successfully.")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
    public function show(Request $request)
    {
        $user = $request->user();
        return ResponseHelper::success([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone_number' => $user->phone_number,
            'account_type' => $user->account_type,
            'status' => $user->status,
            'date_of_birth' => $user->date_of_birth,
            'avatar_url' => $user->avatar_url,
            'identity_document_url' => $user->identity_document_url,
            'email' => $user->email
        ], 'Profile retrieved successfully.');
    }

    #[OA\Get(
        path: "/avatar-image",
        summary: "Get user avatar",
        description: "Retrieve authenticated user's avatar image URL",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Avatar retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object", properties: [new OA\Property(property: "avatar_url", type: "string")]),
                        new OA\Property(property: "body", type: "string", example: "Avatar retrieved successfully.")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Avatar not found"),
        ]
    )]
    public function getAvatar(Request $request)
    {
        $user = $request->user();
        if ($user->avatar_url == null)
            return ResponseHelper::error('no avatar, put your avatar!', 404);
        return ResponseHelper::success(['avatar_url' => $user->avatar_url], 'Avatar retrieved successfully.');
    }

    #[OA\Get(
        path: "/identity-document-image",
        summary: "Get identity document",
        description: "Retrieve authenticated user's identity document image URL",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Identity document retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object", properties: [new OA\Property(property: "identity_document_url", type: "string")]),
                        new OA\Property(property: "body", type: "string", example: "Identity document retrieved successfully.")
                    ]
                )
            ),
        ]
    )]
    public function getIdentityDocument(Request $request)
    {
        $user = $request->user();
        return ResponseHelper::success(['identity_document_url' => $user->identity_document_url], 'Identity document retrieved successfully.');
    }

    #[OA\Post(
        path: "/update-profile-info",
        summary: "Update user profile",
        description: "Update authenticated user's profile information including images",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
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
                        new OA\Property(property: "email", type: "string", format: "email", nullable: true, example: "ahmed@example.com"),
                        new OA\Property(property: "password", type: "string", format: "password", minLength: 6, example: "password123"),
                        new OA\Property(property: "date_of_birth", type: "string", format: "date", nullable: true, example: "1990-01-01"),
                        new OA\Property(property: "account_type", type: "string", enum: ["tenant", "apartment_owner"], nullable: true),
                        new OA\Property(property: "avatar_image", type: "string", format: "binary", description: "Avatar image (optional)"),
                        new OA\Property(property: "identity_document_image", type: "string", format: "binary", description: "Identity document image (required)"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "success"),
                        new OA\Property(property: "data", type: "object"),
                        new OA\Property(property: "body", type: "string", example: "Profile updated successfully.")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function update(Request $request)
    {
        $user = $request->user();
        try {
            $request->validate([
                'first_name' => 'required|string',
                'avatar_image' => 'nullable|image|mimes:png,jpg,gif',
                'identity_document_image' => 'required|image|mimes:png,jpg,gif',
                'last_name' => 'required|string',
                'phone_number' => 'required|string|regex:/^[0-9]+$/',
                'date_of_birth' => 'nullable|date',
                'account_type' => 'nullable|in:tenant,apartment_owner',
                'email' => 'nullable|string|email|unique:users',
                'password' => 'required|min:6'
            ]);

            DB::beginTransaction();
            $avatar = $user->avatar_url;
            if ($request->hasFile('avatar_image')) {
                if (Storage::disk('public')->exists($avatar))
                    Storage::disk('public')->delete($avatar);

                $avatar = uploadImage($request->file('avatar_image'), 'avatars', 'public');
            }

            $identity_document = $user->identity_document_url;
            if ($request->hasFile('identity_document_image')) {
                if (Storage::disk('public')->exists($identity_document))
                    Storage::disk('public')->delete($identity_document);

                $identity_document = uploadImage($request->file('identity_document_image'), 'identity_documents', 'public');
            }

            $user->update([
                'first_name' => $request->first_name,
                'avatar_url' => $avatar,
                'identity_document_url' => $identity_document,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $request->date_of_birth,
                'account_type' => $request->account_type,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return ResponseHelper::error('update failed, error: ' . $e->getMessage(), 500);
        }

        return ResponseHelper::success(new UserRecource($user), 'Profile updated successfully.');
    }
}
