<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Interfaces\INotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    protected INotificationService $notificationService;

    public function __construct(INotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->notificationService->getByUser($request->user()->id);
        return $this->serviceResponse($response);
    }

    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['sender_id'] = $request->user()->id;

        $response = $this->notificationService->send($payload);
        return $this->serviceResponse($response);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $response = $this->notificationService->markRead($id, $request->user()->id);
        return $this->serviceResponse($response);
    }
}
