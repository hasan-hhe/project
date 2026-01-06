<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserRecource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SignupController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string',
                'avatar_image' => 'nullable|image|mimes:png,jpg,gif',
                // 'identity_document_image' => 'required|image|mimes:png,jpg,gif',
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
            $avatar = $request->file('avatar_image')->store('avatars', 'public');

        // $identity_document = $request->file('identity_document_image')->store('identity_documents', 'public');

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $request->date_of_birth,
                'account_type' => $request->account_type,
                'email' => $request->email,
                'avatar_url' => $avatar,
                // 'identity_document_url' => $identity_document,
                'password' => Hash::make($request->password)
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $token = $user->createToken('api_token')->plainTextToken;


        return response()->json([
            'message' => 'success',
            'user'    => new UserRecource($user),
            'token'   => $token,
            'body' => 'تم تسجيل الدخول بنجاح'
        ]);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => 'required|string|regex:/^[0-9]+$/',
                'password' => 'required'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }

        try {
            $user = User::where('phone_number', $request->phone_number)->firstorFail();
        } catch (Exception $e) {
            return response()->json(['message' => 'phone number not found']);
        }


        if (!Hash::check($request->password, $user->password)) {
            return response()->json(
                [
                    'error' => 'password not correct'
                ],
                401
            );
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'login done!',
            'user'    => new UserRecource($user),
            'token'   => $token,
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'logout done!'
        ], 200);
    }
}

//