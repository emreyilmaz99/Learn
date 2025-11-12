<?php

namespace App\Providers;

use App\Services\Eloquent\AuthService;
use App\Services\Eloquent\MessageService;
use App\Services\Eloquent\NotificationService;
use App\Services\Eloquent\MessageCacheService;
use App\Services\Eloquent\UserService;
use App\Services\Interfaces\IAuthService;
use App\Services\Interfaces\IMessageService;
use App\Services\Interfaces\INotificationService;
use App\Services\Interfaces\IMessageCacheService;
use App\Services\Interfaces\IUserService;
use Illuminate\Support\ServiceProvider;

class InterfaceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Service Interfaces to Implementations
        $this->app->bind(IAuthService::class, AuthService::class);
        $this->app->bind(IUserService::class, UserService::class);
        $this->app->bind(IMessageService::class, MessageService::class);
    $this->app->bind(INotificationService::class, NotificationService::class);
        $this->app->bind(IMessageCacheService::class, MessageCacheService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
