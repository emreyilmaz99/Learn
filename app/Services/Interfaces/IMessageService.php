<?php

namespace App\Services\Interfaces;

use App\Core\Class\ServiceResponse;

interface IMessageService
{
    /**
     * Get all messages.
     *
     */
    public function getAllMessages(): ServiceResponse;

    /**
     * Get all messages for a specific user.
     *
     * 
     */
    public function getUserMessages(int $userId): ServiceResponse;

    /**
     * Get a message by ID.
     *
     * 
     */
    public function getMessageById(int $id): ServiceResponse;

    /**
     * Create a new message.
     *
     * 
     */
    public function createMessage(array $data): ServiceResponse;

    /**
     * Update a message.
     *
     * 
     */
    public function updateMessage(int $id, array $data): ServiceResponse;

    /**
     * Delete a message.
     *
     */
    public function deleteMessage(int $id): ServiceResponse;

    /**
     * mesaj gönderme işlemi
     * 
     */
    public function getSentMessages(int $userId): ServiceResponse;

    /**
     * mesaj alma işlemi
     * 
     */
    public function getReceivedMessages(int $userId): ServiceResponse;

    /**
     * iki kullanıcı arasında ilişki 
     * 
     * 
     */
    public function getConversation(int $userId1, int $userId2): ServiceResponse;

    /**
     * başka bir kullanıcı tarafından gönderilen mesaj
     * 
     * 
     */
    public function sendMessage(int $senderid, int $receiverid, array $messagedata): ServiceResponse;
}

