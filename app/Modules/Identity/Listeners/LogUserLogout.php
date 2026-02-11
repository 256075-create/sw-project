<?php

namespace App\Modules\Identity\Listeners;

use App\Modules\Identity\Events\UserLoggedOut;
use App\Modules\Identity\Services\ElasticsearchService;
use Illuminate\Support\Facades\Log;

class LogUserLogout
{
    public function __construct(
        protected ElasticsearchService $elasticsearch
    ) {}

    public function handle(UserLoggedOut $event): void
    {
        Log::info('User logout recorded', [
            'user_id' => $event->user->user_id,
            'username' => $event->user->username,
            'timestamp' => now()->toISOString(),
        ]);

        $this->elasticsearch->indexLogoutEvent(
            $event->user->user_id,
            $event->user->username,
            request()?->ip()
        );
    }
}
