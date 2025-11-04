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
        return response()->json([
            'statusCode' => $response->getStatusCode(),
            'success' => $response->isSuccess(),
            'message' => $response->getMessage(),
            'data' => $response->getData(),
        ]);
    }


}
