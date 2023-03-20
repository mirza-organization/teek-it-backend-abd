<?php
namespace App\Services;

class JsonResponseCustom
{
    public function getApiResponse($data, $status, $message, $http_code)
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => $message
        ], $http_code);
    }
    
    public function getApiResponseExtention($data, $status, $message, $extra_key, $extra_data, $http_code)
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => $message,
            $extra_key => $extra_data   
        ], $http_code);
    }
}