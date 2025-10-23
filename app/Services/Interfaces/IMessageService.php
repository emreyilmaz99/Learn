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
}
