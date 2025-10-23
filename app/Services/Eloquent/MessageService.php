<?php

namespace App\Services\Eloquent;

use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Services\Interfaces\IMessageService;

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
     * @return array
     */
    public function getAllMessages(): array
    {
        $messages = $this->messageRepository->getAll();

        return [
            'success' => true,
            'message' => 'Mesajlar başarıyla getirildi',
            'data' => $messages,
        ];
    }

    /**
     * Get all messages for a specific user.
     *
     * @param int $userId
     * @return array
     */
    public function getUserMessages(int $userId): array
    {
        $messages = $this->messageRepository->getAllByUser($userId);

        return [
            'success' => true,
            'message' => 'Kullanıcı mesajları başarıyla getirildi',
            'data' => $messages,
        ];
    }

    /**
     * Get a message by ID.
     *
     * @param int $id
     * @return array
     */
    public function getMessageById(int $id): array
    {
        $message = $this->messageRepository->findById($id);

        if (!$message) {
            return [
                'success' => false,
                'message' => 'Mesaj bulunamadı',
            ];
        }

        return [
            'success' => true,
            'message' => 'Mesaj başarıyla getirildi',
            'data' => $message,
        ];
    }

    /**
     * Create a new message.
     *
     * @param array $data
     * @return array
     */
    public function createMessage(array $data): array
    {
        $message = $this->messageRepository->create($data);

        return [
            'success' => true,
            'message' => 'Mesaj başarıyla oluşturuldu',
            'data' => $message,
        ];
    }

    /**
     * Update a message.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateMessage(int $id, array $data): array
    {
        $message = $this->messageRepository->update($id, $data);

        if (!$message) {
            return [
                'success' => false,
                'message' => 'Mesaj bulunamadı',
            ];
        }

        return [
            'success' => true,
            'message' => 'Mesaj başarıyla güncellendi',
            'data' => $message,
        ];
    }

    /**
     * Delete a message.
     *
     * @param int $id
     * @return array
     */
    public function deleteMessage(int $id): array
    {
        $deleted = $this->messageRepository->delete($id);

        if (!$deleted) {
            return [
                'success' => false,
                'message' => 'Mesaj bulunamadı',
            ];
        }

        return [
            'success' => true,
            'message' => 'Mesaj başarıyla silindi',
        ];
    }
}
