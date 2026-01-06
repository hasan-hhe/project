<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success($data = [], $message = 'success')
    {
        return response()->json([
            'message' => 'success',
            'data'    => $data,
            'body'    => $message,
        ], 200);
    }

    public static function error($message = 'error', $statusCode = 400)
    {
        return response()->json([
            'message' => 'error',
            'body'    => $message,
        ], $statusCode);
    }
}
