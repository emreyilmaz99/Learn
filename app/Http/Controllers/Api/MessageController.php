<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Requests\SendMessageRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Interfaces\IMessageService;
use App\Services\Interfaces\IUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Core\Class\ServiceResponse;

class MessageController extends Controller
{
    use ApiResponseTrait;

    protected IMessageService $messageService;
    protected IUserService $userService;

    public function __construct(IMessageService $messageService, IUserService $userService)
    {
        $this->messageService = $messageService;
        $this->userService = $userService;
    }

    /**
     * Display a listing of the authenticated user's messages.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $response = $this->messageService->getUserMessages($request->user()->id);

        return $this->serviceResponse($response);
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
        $data['sender_id'] = $request->user()->id;

        $response = $this->messageService->createMessage($data);

        return $this->serviceResponse($response);
    }

    /**
     * Display the specified message.
     * Accepts numeric id values (from route) and validates/casts them.
     *
     * @param mixed $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        // If a non-numeric value was passed (e.g. a string), return a 400.
        if (!is_numeric($id)) {
            $errorResponse = new ServiceResponse(400, false, 'Invalid message id.', null);
            return $this->serviceResponse($errorResponse);
        }

        $response = $this->messageService->getMessageById((int) $id);

        return $this->serviceResponse($response);
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
        $response = $this->messageService->updateMessage($id, $request->validated());

        return $this->serviceResponse($response);
    }

    /**
     * Remove the specified message.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $response = $this->messageService->deleteMessage($id);

        return $this->serviceResponse($response);
    }

    /**
     * Get messages sent by authenticated user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function sent(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $response = $this->messageService->getSentMessages($userId);

        return $this->serviceResponse($response);
    }

    /**
     * Get messages received by authenticated user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function inbox(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $response = $this->messageService->getReceivedMessages($userId);

        return $this->serviceResponse($response);
    }

    /**
     * Get conversation between authenticated user and another user.
     * 
     * @param Request $request
     * @param int $userId - Diğer kullanıcının ID'si
     * @return JsonResponse
     */
    public function conversation(Request $request, int $userId): JsonResponse
    {
        $authUserId = $request->user()->id;
        $response = $this->messageService->getConversation($authUserId, $userId);

        return $this->serviceResponse($response);
    }

    /**
     * Send a message to a specific user.
     * 
     * @param SendMessageRequest $request
     * @param int $userId - Alıcının ID'si
     * @return JsonResponse
     */
    public function sendMessage(SendMessageRequest $request, int $userId): JsonResponse
    {
        $response = $this->messageService->sendMessage(
            $request->user()->id,
            $userId,
            $request->validated()
        );

        return $this->serviceResponse($response);
    }

    /**
     * Get all users except the authenticated user (for receiver selection)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUsers(Request $request): JsonResponse
    {
        $response = $this->userService->getUsersExcept($request->user()->id);

        return $this->serviceResponse($response);
    }
}
