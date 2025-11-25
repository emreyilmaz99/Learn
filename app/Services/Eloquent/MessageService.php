<?php

namespace App\Services\Eloquent;

use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\IMessageService;
use App\Services\Interfaces\INotificationService;
use App\Services\Interfaces\IMessageCacheService;
use App\Core\Class\ServiceResponse;
use App\Jobs\IndexMessageJob;

class MessageService implements IMessageService
{
    protected MessageRepositoryInterface $messageRepository;
    protected UserRepositoryInterface $userRepository;
    protected INotificationService $notificationService;
    protected IMessageCacheService $messageCacheService;

    public function __construct(MessageRepositoryInterface $messageRepository, UserRepositoryInterface $userRepository, INotificationService $notificationService, IMessageCacheService $messageCacheService)
    {
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
        $this->notificationService = $notificationService;
        $this->messageCacheService = $messageCacheService;
    }


    /**
     * Get all messages.
     *
     * @return ServiceResponse
     */
    public function getAllMessages(): ServiceResponse
    {
        $messages = $this->messageRepository->getAll();
        return new ServiceResponse(200, true, 'Mesajlar başarıyla getirildi', $messages);
    }

    /**
     * Get user messages.
     *
     * @return ServiceResponse
     */
    public function getUserMessages(int $userId): ServiceResponse
    {
        $messages = $this->messageRepository->getAllByUser($userId);
        return new ServiceResponse(200, true, 'Mesajlar başarıyla getirildi', $messages);
    }
    

    /**
     * Get a message by ID.
     *
     * @param int $id
     * @return ServiceResponse
     */
    public function getMessageById(int $id): ServiceResponse
    {
        // Try cache first
        $cached = $this->messageCacheService->get($id);
        if ($cached) {
            return new ServiceResponse(200, true, 'Mesaj başarıyla getirildi (cache)', $cached);
        }

        $message = $this->messageRepository->findById($id);
        if ($message) {
            // warm cache
            $this->messageCacheService->set($message->toArray());
        }

        return new ServiceResponse(200, true, 'Mesaj başarıyla getirildi', $message);
    }

    /**
     * Create a new message.
     *
     * @param array $data
     * @return ServiceResponse
     */
    public function createMessage(array $data): ServiceResponse
    {
        // Eğer receiver_email varsa receiver_id'yi bul
        if (isset($data['receiver_email'])) {
            $receiver = $this->userRepository->findByEmail($data['receiver_email']);
            
            if (!$receiver) {
                return new ServiceResponse(404, false, 'Alıcı kullanıcı bulunamadı', null);
            }
            
            // Kendi kendine mesaj gönderme kontrolü
            if ($receiver->id == $data['sender_id']) {
                return new ServiceResponse(400, false, 'Kendinize mesaj gönderemezsiniz', null);
            }
            
            $data['receiver_id'] = $receiver->id;
            unset($data['receiver_email']);
        }

        $message = $this->messageRepository->create($data);

        try {
            $this->notificationService->send([
                'receiver_id' => $message->receiver_id,
                'title' => $message->title ?? 'Yeni mesaj',
                'content' => $message->content ?? '',
                'sender_id' => $message->sender_id,
                'data' => ['message_id' => $message->id],
            ]);
        } catch (\Throwable $e) {
            // swallow to avoid breaking message creation; job dispatch failure shouldn't block
        }

        // warm cache for this new message
        try {
            $this->messageCacheService->set($message->toArray());
        } catch (\Throwable $e) {
            // non-blocking: cache failures should not break creation
        }

        // dispatch indexing job
        try {
            dispatch(new IndexMessageJob($message->id));
        } catch (\Throwable $e) {
            // ignore
        }

        return new ServiceResponse(201, true, 'Mesaj başarıyla oluşturuldu', $message);
    }

    /**
     * Update a message.
     *
     * @param int $id
     * @param array $data
     * @return ServiceResponse
     */
    public function updateMessage(int $id, array $data): ServiceResponse
    {
        $message = $this->messageRepository->update($id, $data);
        // update cache
        try {
            if ($message) $this->messageCacheService->set($message->toArray());
        } catch (\Throwable $e) {
            // ignore cache errors
        }

        try {
            if ($message) dispatch(new IndexMessageJob($message->id));
        } catch (\Throwable $e) {
            // ignore
        }

        return new ServiceResponse(200, true, 'Mesaj başarıyla güncellendi', $message);
    }

    /**
     * Delete a message.
     *
     * @param int $id
     * @return ServiceResponse
     */
    public function deleteMessage(int $id): ServiceResponse
    {
        $deleted = $this->messageRepository->delete($id);
        // remove from cache
        try {
            $this->messageCacheService->delete($id);
        } catch (\Throwable $e) {
            // ignore
        }

        // dispatch index deletion (send a job that will delete from ES)
        try {
            dispatch(new IndexMessageJob($id));
        } catch (\Throwable $e) {
            // non-blocking
        }

        return new ServiceResponse(200, true, 'Mesaj başarıyla silindi', $deleted);
    }

    public function getSentMessages(int $userId): ServiceResponse
    {
        $messages = $this->messageRepository->getSentMessages($userId);

        return new ServiceResponse(200, true, 'Gönderilen mesajlar başarıyla getirildi', $messages);
    }

    public function getReceivedMessages(int $userId): ServiceResponse
    {
        $messages = $this->messageRepository->getReceivedMessages($userId);

        return new ServiceResponse(200, true, 'Alınan mesajlar başarıyla getirildi', $messages);
    }

    public function getConversation(int $userId1, int $userId2): ServiceResponse
    {
        if ($userId1 === $userId2) {
            return new ServiceResponse(400, false, 'İki kullanıcı aynı olamaz konuşma başlatılamaz');
        }

        $messages = $this->messageRepository->getConversation($userId1, $userId2);
        return new ServiceResponse(200, true, 'Konuşma başarıyla getirildi', $messages);
    }

    public function sendMessage(int $senderId, int $receiverId, array $messageData): ServiceResponse
    {
        if ($senderId === $receiverId) {
            return new ServiceResponse(400, false, 'Kendinize mesaj gönderemezsiniz');
        }

        $receiver = \App\Models\User::find($receiverId);
        if (!$receiver) {
            return new ServiceResponse(404, false, 'Alıcı bulunamadı');
        }

        $message = $this->messageRepository->create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'title' => $messageData['title'],
            'content' => $messageData['content'],
        ]);

        $message = $this->messageRepository->findById($message->id);

        // create notification for receiver
        try {
            $this->notificationService->send([
                'receiver_id' => $message->receiver_id,
                'title' => $message->title ?? 'Yeni mesaj',
                'content' => $message->content ?? '',
                'sender_id' => $message->sender_id,
                'data' => ['message_id' => $message->id],
            ]);
        } catch (\Throwable $e) {
            // ignore notification failures to keep message delivery stable
        }

        // warm cache
        try {
            $this->messageCacheService->set($message->toArray());
        } catch (\Throwable $e) {
            // ignore
        }

        return new ServiceResponse(200, true, 'Mesaj başarıyla gönderildi', $message);
    }
}
