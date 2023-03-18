<?php
namespace App\Services;

class JsonResponseCustom
{
    public function getApiResponse($data, $status, $message, $code)
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => $message,
            $code
        ]);
    }
    
    public function getApiResponseExtention($data, $status, $message, $http_code, $extra_key, $extra_data)
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => $message,
            $extra_key => $extra_data,
            $http_code
        ]);
    }

}