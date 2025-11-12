<?php

namespace App\Services\Interfaces;

use App\Core\Class\ServiceResponse;

interface INotificationService
{
    public function send(array $data): ServiceResponse;

    public function getByUser(int $userId): ServiceResponse;

    public function markRead(int $id, int $userId): ServiceResponse;
}
