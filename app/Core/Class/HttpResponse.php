<?php

namespace App\Core\Class;

trait HttpResponse
{
   
    public function httpResponse(bool $isSuccess, string $message, mixed $data, int $statusCode)
    {
        if (!$message) {
            return response()->json(['message' => 'Message is required'], 500);
        }

        return response()->json([
            'isSuccess' => $isSuccess,
            'message' => $message,
            'data' => $data,
            'statusCode' => $statusCode
        ], $statusCode);
    }
}