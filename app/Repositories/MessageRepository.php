<?php

namespace App\Repositories;

use App\Models\Message;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    /**
     * Get all messages.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Message::with('user')->latest()->get();
    }

    /**
     * Get all messages for a specific user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getAllByUser(int $userId): Collection
    {
        return Message::where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Find a message by ID.
     *
     * @param int $id
     * @return Message|null
     */
    public function findById(int $id): ?Message
    {
        return Message::with('user')->find($id);
    }

    /**
     * Create a new message.
     *
     * @param array $data
     * @return Message
     */
    public function create(array $data): Message
    {
        return Message::create($data);
    }

    /**
     * Update a message.
     *
     * @param int $id
     * @param array $data
     * @return Message|null
     */
    public function update(int $id, array $data): ?Message
    {
        $message = Message::find($id);
        
        if ($message) {
            $message->update($data);
            return $message->fresh('user');
        }

        return null;
    }

    /**
     * Delete a message.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $message = Message::find($id);
        
        if ($message) {
            return $message->delete();
        }

        return false;
    }
}
