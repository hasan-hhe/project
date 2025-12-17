<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class profileController extends Controller
{
    public function getuserinformation(Request $request){

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ]);
    }
}
