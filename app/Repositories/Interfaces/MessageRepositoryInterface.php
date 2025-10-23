<?php

namespace App\Repositories\Interfaces;

interface MessageRepositoryInterface
{
    /**
     * Get all messages.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll();

    /**
     * Get all messages for a specific user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllByUser(int $userId);

    /**
     * Find a message by ID.
     *
     * @param int $id
     * @return \App\Models\Message|null
     */
    public function findById(int $id);

    /**
     * Create a new message.
     *
     * @param array $data
     * @return \App\Models\Message
     */
    public function create(array $data);

    /**
     * Update a message.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\Message|null
     */
    public function update(int $id, array $data);

    /**
     * Delete a message.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get messages sent by a user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSentMessages(int $userId);

    /**
     * Get messages received by a user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReceivedMessages(int $userId);

    /**
     * Get conversation between two users.
     *
     * @param int $userId1 - First user ID
     * @param int $userId2 - Second user ID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConversation(int $userId1, int $userId2);

}
