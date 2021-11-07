<?php

namespace App\Http\Traits;

trait ResponseTrait {
    protected function success($message, $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message
        ], $status);
    }

    protected function successWithData($message, $data = [], $status = 200)
    {
        $resData = [
            'status' => true,
            'message' => $message,
            'data' => $data
        ];

        return response()->json($resData, $status);
    }

    protected function failure($message, $status = 422)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $status);
    }
}
