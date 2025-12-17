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
        try{
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number' => 'required|string|unique:users|regex:/^[0-9]+$/',
            'date_of_birth' => 'nullable|date',
            'account_type' => 'required|in:tenat, apartment_owner',
            'email' => 'nullable|string|email|unique:users',
            'password' => 'required|min:6'
        ]);
        }catch(Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
            
        $user = User::create([
           'first_name' => $request->first_name,
           'last_name' => $request->last_name,
           'phone_number' => $request->phone_number,
           'date_of_birth' => $request->date_of_birth,
           'account_type' => $request->account_type,
           'email' => $request->email,
           'password' => Hash::make($request->password)
       ]);

        $token = $user->createToken('api_token')->plainTextToken;


        return response()->json([
            'message' => 'register done!',
            'user'    => new UserRecource($user),
            'token'   => $token
        ]);
    }

    public function login(Request $request)
    {
        try{
    $request->validate([
        'phone_number' => 'required|string|regex:/^[0-9]+$/',
        'password' => 'required'
    ]);
    }catch(Exception $e){
        return response()->json([
                'message' => $e->getMessage()
            ]);
    }

    try{
    $user = User::where('phone_number', $request->phone_number)->firstorFail();
    }catch(Exception $e){return response()->json(['message'=>'phone number not found']);}


    if (!Hash::check($request->password, $user->password)) {
        return response()->json([
            'error' => 'password not correct'],
             401);
    }

    $token = $user->createToken('api_token')->plainTextToken;

    return response()->json([
            'message' => 'login done!',
            'user'    => new UserRecource($user),
            'token'   => $token,
    ]);
    }

    
    // public function logout(Request $request)
    // {

    //     $auth = $request->header('Authorization');

    //     if (!$auth) {
    //         return response()->json([
    //             'message' => 'Token missing',
    //         ], 400);
    //     }

    //     if (!Str::startsWith($auth, 'Bearer')) {
    //         return response()->json([
    //             'message' => 'Authorization header must be Bearer token',
    //         ], 400);
    //     }

    //     $plainToken = trim(Str::replaceFirst('Bearer', '', $auth));

    //     if ($plainToken === '') {
    //         return response()->json([
    //             'message' => 'Bearer token is empty',
    //         ], 400);
    //     }

    //     $user = $request->user();

    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'Invalid token',
    //         ], 401);
    //     }

    //     $currentToken = $user->currentAccessToken();

    //     if (!$currentToken) {
    //         return response()->json([
    //             'message' => 'No active access token found for this session',
    //         ], 401);
    //     }

    //     $currentToken->delete();
    //     return response()->json([
    //         'message' => 'logout done!'
    //     ], 200);
    // }
}

//