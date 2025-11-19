<?php

namespace App\Http\Traits;

use App\Core\Class\ServiceResponse;
use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Handle ServiceResponse object and return formatted JSON response.
     *
     * @param ServiceResponse $response
     * @return JsonResponse
     */
    protected function serviceResponse(ServiceResponse $response): JsonResponse
    {
        $data = $response->getData();

        // If the service returned a payload with both `data` and `meta`,
        // return them as separate top-level keys so frontend code that
        // expects `response.data` to be an array keeps working.
        if (is_array($data) && array_key_exists('data', $data) && array_key_exists('meta', $data)) {
            return response()->json([
                'statusCode' => $response->getStatusCode(),
                'success' => $response->isSuccess(),
                'message' => $response->getMessage(),
                'data' => $data['data'],
                'meta' => $data['meta'],
            ]);
        }

        return response()->json([
            'statusCode' => $response->getStatusCode(),
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
            'data' => $data,
        ]);
    }


}
