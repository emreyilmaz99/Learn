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
    public function getAll()
    {
        return Message::with('sender', 'receiver')
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Get all messages for a specific user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getAllByUser(int $userId)
    {
        return Message::with('sender', 'receiver')
        ->where('sender_id', $userId)
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Find a message by ID.
     *
     * @param int $id
     * @return Message|null
     */
    public function findById(int $id)
    {
        return Message::with('sender' , 'receiver')
        ->find($id);
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
            return $message->fresh(['sender', 'receiver']);
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

    public function getSentMessages(int $userId)
    {
        return Message::with('sender', 'receiver')
            ->where('sender_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getReceivedMessages(int $userId)
    {
        return Message::where('receiver', 'receiver')
            ->where('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /** 
     * iki kullanıcı arasındakii konuşmayı getirme
     */
    
    public function getConversation(int $userId1, int $userId2)
    {
        return Message::where(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId1)
                  ->where('receiver_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId2)
                  ->where('receiver_id', $userId1);
        })->orderBy('created_at', 'asc')
          ->get();
    }
}
