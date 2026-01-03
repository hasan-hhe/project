<?php

namespace App\Http\Controllers;

// use Carbon\Carbon;

use App\Http\Resources\UserRecource;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class profileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        return response()->json([
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
        ]);
    }

    public function getAvatar(Request $request)
    {
        $user = $request->user();
        if ($user->avatar_url == null)
            return response()->json([
                'message' => 'no avatar, put your avatar!'
            ]);
        return response()->json([
            'message' => $user->avatar_url
        ]);
    }

    public function getIdentityDoucument(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'message' => $user->identity_document_url
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        try {
            $request->validate([
                'first_name' => 'required|string',
                'avatar_image' => 'nullable|image|mimes:png,jpg,gif',
                'identity_document_image' => 'required|image|mimes:png,jpg,gif',
                'last_name' => 'required|string',
                'phone_number' => 'required|string|unique:users|regex:/^[0-9]+$/',
                'date_of_birth' => 'nullable|date',
                'account_type' => 'nullable|in:tenant,apartment_owner',
                'email' => 'nullable|string|email|unique:users',
                'password' => 'required|min:6'
            ]);

            DB::beginTransaction();

            $avatar = $user->avatar_url;
            if ($request->hasFile('avatar_image')) {
                if (!$avatar)
                    Storage::delete('public/avatars/' . $user->identity_doucument);
                $avatar = $request->file('avatar_image')->store('avatars', 'public');
            }

            Storage::delete('public/identity_documents/' . $user->identity_doucument);
            $identity_document = $request->file('identity_document_image')->store('identity_documents', 'public');

            $user->update([
                'first_name' => $request->firstname,
                'avatar_url' => $avatar,
                'identity_document_url' => $identity_document,
                'last_name' => $request->lastname,
                'phone_number' => $request->phonenumber,
                'date_of_birth' => $request->date_of_birth,
                'account_type' => $request->account_type,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'update failed, erorr: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'message' => 'update done!',
            'data' => new UserRecource($user),
        ], 200);
    }
}
