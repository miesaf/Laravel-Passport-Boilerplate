<?php

namespace App\Http\Traits;

use Illuminate\Support\Collection;

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

    protected function successWithCount($message, $data = [], $status = 200)
    {
        $count = 0;

        if($data instanceof Collection) {
            $count = $data->count();
        } else if(is_array($data)) {
            $count = count($data);
        } else if(is_object($data)) {
            $count = 1;
        }

        $resData = [
            'status' => true,
            'message' => $message,
            'total' => $count,
            'data' => $data
        ];

        return response()->json($resData, $status);
    }

    protected function successWithID($message, $id, $status = 200)
    {
        $resData = [
            'status' => true,
            'message' => $message,
            'id' => $id
        ];

        return response()->json($resData, $status);
    }

    protected function failure($message, $status = 200)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $status);
    }

    protected function failedValidation($message, $errors, $status = 422)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    protected function forbidden()
    {
        return response()->json([
            'status' => false,
            'message' => "Forbidden request due to insufficient permission"
        ], 403);
    }
}
