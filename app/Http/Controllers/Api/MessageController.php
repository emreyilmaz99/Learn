<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Interfaces\IMessageService;
use App\Models\User;
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

        return $this->successResponse($result['data'], $result['message'], 200);
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

        return $this->successResponse($result['data'], $result['message'], 200);
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

        return $this->successResponse($result['data'], $result['message'], 200);
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

        return $this->successResponse(null, $result['message'], 200);
    }

    //yeni fonksiyonlar ----------------

    /**
     * Get messages sent by authenticated user.
     * 
     * @return JsonResponse
     */
    public function sent(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $result = $this->messageService->getSentMessages($userId);

        return $this->successResponse($result['data'], $result['message'], 200);
    }

    /**
     * Get messages received by authenticated user.
     * 
     * @return JsonResponse
     */
    public function inbox(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $result = $this->messageService->getReceivedMessages($userId);

        return $this->successResponse($result['data'], $result['message'], 200);
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
        $result = $this->messageService->getConversation($authUserId, $userId);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 400);
        }

        return $this->successResponse($result['data'], $result['message'], 200);
    }

    /**
     * Send a message to a specific user.
     * 
     * @param Request $request
     * @param int $userId - Alıcının ID'si
     * @return JsonResponse
     */
    public function sendMessage(Request $request, int $userId): JsonResponse
    {
        // Validation
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $senderId = $request->user()->id;
        
        $result = $this->messageService->sendMessage(
            $senderId,
            $userId,
            [
                'title' => $request->input('title'),
                'content' => $request->input('content'),
            ]
        );

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 400);
        }

        return $this->successResponse($result['data'], $result['message'], 201);
    }

    /**
     * Get all users except the authenticated user (for receiver selection)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUsers(Request $request): JsonResponse
    {
        $users = User::where('id', '!=', $request->user()->id)
            ->select('id', 'name', 'email')
            ->get();

        return $this->successResponse($users, 'Kullanıcılar listelendi', 200);
    }
}
