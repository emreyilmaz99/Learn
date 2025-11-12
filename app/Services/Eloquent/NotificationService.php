<?php

namespace App\Services\Eloquent;

use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Services\Interfaces\INotificationService;
use App\Core\Class\ServiceResponse;
use App\Jobs\ProcessNotificationJob;

class NotificationService implements INotificationService
{
    protected NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function send(array $data): ServiceResponse
    {
        // create notification record
        $notification = $this->notificationRepository->create($data);

        // dispatch background job to process (send) the notification
        dispatch(new ProcessNotificationJob($notification->id))->onQueue('notifications');

        return new ServiceResponse(201, true, 'Bildirim oluşturuldu', $notification);
    }

    public function getByUser(int $userId): ServiceResponse
    {
        $items = $this->notificationRepository->getByUser($userId);
        return new ServiceResponse(200, true, 'Bildirimler getirildi', $items);
    }

    public function markRead(int $id, int $userId): ServiceResponse
    {
        $ok = $this->notificationRepository->markRead($id, $userId);
        if (!$ok) return new ServiceResponse(404, false, 'Bildirim bulunamadı veya yetkiniz yok');
        return new ServiceResponse(200, true, 'Bildirim okundu');
    }
}
