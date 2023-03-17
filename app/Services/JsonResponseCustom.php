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
    public function getApiResponse_ext( $data1, $status, $message, $code, $att, $data2)
    {
        return response()->json([
            'data' => $data1,
            'status' => $status,
            'message' => $message,
            $att => $data2,
            $code
        ]);

    }

}