<?php
namespace App\Services;

class JsonResponseCustom
{
    public static function getApiResponse($data, $status, $message, $http_code)
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => $message
        ], $http_code);
    }
    
    public static function getApiResponseExtention($data, $status, $message, $extra_key, $extra_key_data, $http_code)
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => $message,
            $extra_key => $extra_key_data   
        ], $http_code);
    }
    public static function getWebResponse($message, $status){
        if($status){
        flash($message)->success();
        }else if(empty($status)){
            flash($message)->error();
        }
    }
}