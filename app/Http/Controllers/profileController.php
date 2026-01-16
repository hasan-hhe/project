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

class ProfileController extends Controller
{
    #[OA\Get(path: "/my-profile", tags: ["Profile"], security: [["bearerAuth" => []]])]
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
        ], 'تم جلب الملف الشخصي بنجاح.');
    }

    #[OA\Post(path: "/update-profile-info", tags: ["Profile"], security: [["bearerAuth" => []]])]
    public function update(Request $request)
    {
        $user = $request->user();
        try {
            $request->validate([
                'first_name' => 'required|string',
                'avatar_image' => 'nullable|image|mimes:png,jpg,gif',
                'identity_document_image' => 'nullable|image|mimes:png,jpg,gif',
                'last_name' => 'required|string',
                'phone_number' => 'required|string|regex:/^[0-9]+$/',
                'date_of_birth' => 'nullable|date',
                'email' => 'nullable|string|email',
                'password' => 'nullable|min:6'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 422);
        }

        try {
            DB::beginTransaction();
            $avatar = $user->avatar_url ?? null;

            if ($request->hasFile('avatar_image')) {
                if ($avatar && Storage::disk('public')->exists($avatar))
                    Storage::disk('public')->delete($avatar);

                $avatar = uploadImage($request->file('avatar_image'), 'avatars', 'public');
            }

            $identity_document = $user->identity_document_url ?? null;
            if ($request->hasFile('identity_document_image')) {
                if ($identity_document && Storage::disk('public')->exists($identity_document))
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
                'email' => $request->email,
            ]);

            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password)
                ]);
            }

            DB::commit();
            return ResponseHelper::success([
                'user' => new UserRecource($user)
            ], 'تم تحديث الملف الشخصي بنجاح.');
        } catch (Exception $e) {
            DB::rollback();
            return ResponseHelper::error('فشل في التحديث، خطأ: ' . $e->getMessage(), 500);
        }
    }
}
