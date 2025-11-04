<?php

namespace App\Services\Eloquent;

use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Services\Interfaces\IMessageService;
use App\Core\Class\ServiceResponse;

class MessageService implements IMessageService
{
    protected MessageRepositoryInterface $messageRepository;

    public function __construct(MessageRepositoryInterface $messageRepository)
    {
        $this->messageRepository = $messageRepository;
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
        $message = $this->messageRepository->findById($id);

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
        $message = $this->messageRepository->create($data);

        return new ServiceResponse(200, true, 'Mesaj başarıyla oluşturuldu', $message);
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
        return new ServiceResponse(200, true, 'Mesaj başarıyla gönderildi', $message);
    }
}
