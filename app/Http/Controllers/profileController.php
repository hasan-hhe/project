<?php

namespace App\Http\Controllers;

// use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class profileController extends Controller
{
    public function getUserInformation(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone_number' => $user->phone_number,
            'account_type' => $user->account_type,
            'owner_status' => $user->owner_status,
            'date_of_birth' => $user->date_of_birth,
            'email' => $user->email
        ]);
    }

    public function getAvatar() {}

    public function getIdentityDoucument() {}

    public function updateUserInformation() {}
}
