<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Interfaces\IMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use ApiResponseTrait;

    protected IMessageService $messageService;

    public function __construct(IMessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Display a listing of the authenticated user's messages.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->messageService->getUserMessages($request->user()->id);

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Store a newly created message.
     *
     * @param StoreMessageRequest $request
     * @return JsonResponse
     */
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $result = $this->messageService->createMessage($data);

        return $this->successResponse($result['data'], $result['message'], 201);
    }

    /**
     * Display the specified message.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result = $this->messageService->getMessageById($id);

        if (!$result['success']) {
            return $this->notFoundResponse($result['message']);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Update the specified message.
     *
     * @param UpdateMessageRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateMessageRequest $request, int $id): JsonResponse
    {
        $result = $this->messageService->updateMessage($id, $request->validated());

        if (!$result['success']) {
            return $this->notFoundResponse($result['message']);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Remove the specified message.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->messageService->deleteMessage($id);

        if (!$result['success']) {
            return $this->notFoundResponse($result['message']);
        }

        return $this->successResponse(null, $result['message']);
    }
}
