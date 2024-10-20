<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($message = 'Operation successful', $data = null, $code = 200)
    {
        return response()->json([
            'status' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error($message = 'Operation failed', $data = null, $code = 400)
    {
        return response()->json([
            'status' => false,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
