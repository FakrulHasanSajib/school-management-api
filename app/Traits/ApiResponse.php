<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * সফল রেসপন্সের জন্য (Success Response)
     */
    protected function success($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * এরর রেসপন্সের জন্য (Error Response)
     */
    protected function error($message = null, $code = 400, $data = null)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}