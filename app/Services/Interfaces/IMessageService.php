<?php

namespace App\Services\Interfaces;

interface IMessageService
{
    /**
     * Get all messages.
     *
     * @return array
     */
    public function getAllMessages(): array;

    /**
     * Get all messages for a specific user.
     *
     * @param int $userId
     * @return array
     */
    public function getUserMessages(int $userId): array;

    /**
     * Get a message by ID.
     *
     * @param int $id
     * @return array
     */
    public function getMessageById(int $id): array;

    /**
     * Create a new message.
     *
     * @param array $data
     * @return array
     */
    public function createMessage(array $data): array;

    /**
     * Update a message.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateMessage(int $id, array $data): array;

    /**
     * Delete a message.
     *
     * @param int $id
     * @return array
     */
    public function deleteMessage(int $id): array;

    /**
     * mesaj gönderme işlemi
     * @param int $userId
     * @return array
     */
    public function getSentMessages(int $userId): array;

    /**
     * mesaj alma işlemi
     * @param int $userId
     * @return array
     */
    public function getReceivedMessages(int $userId): array;

    /**
     * iki kullanıcı arasında ilişki 
     * 
     * @param int $userId1
     * @param int $userId2
     * @return array
     */
    public function getConversation(int $userId1, int $userId2): array;

    /**
     * başka bir kullanıcı tarafından gönderilen mesaj
     * 
     * @param int $senderid
     * @param int $receiverid
     * @param array $messagedata
     * @return array
     */
    public function sendMessage(int $senderid, int $receiverid, array $messagedata): array;
}

