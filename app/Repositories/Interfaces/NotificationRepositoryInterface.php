<?php

namespace App\Repositories\Interfaces;

use App\Models\Notification;

interface NotificationRepositoryInterface
{
    public function create(array $data): Notification;

    public function findById(int $id): ?Notification;

    public function getByUser(int $userId);

    public function markRead(int $id, int $userId): bool;
}
