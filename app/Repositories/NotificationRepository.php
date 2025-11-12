<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\Interfaces\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    public function findById(int $id): ?Notification
    {
        return Notification::with(['sender', 'receiver'])->find($id);
    }

    public function getByUser(int $userId)
    {
        return Notification::where('receiver_id', $userId)->orderByDesc('created_at')->get();
    }

    public function markRead(int $id, int $userId): bool
    {
        $n = Notification::where('id', $id)->where('receiver_id', $userId)->first();
        if (!$n) return false;
        $n->read_at = now();
        return $n->save();
    }
}
