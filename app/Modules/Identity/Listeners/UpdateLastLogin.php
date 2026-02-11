<?php

namespace App\Modules\Identity\Listeners;

use App\Modules\Identity\Events\UserLoggedIn;
use App\Modules\Identity\Services\ElasticsearchService;
use Illuminate\Support\Facades\Log;

class UpdateLastLogin
{
    public function __construct(
        protected ElasticsearchService $elasticsearch
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        Log::info('User login recorded', [
            'user_id' => $event->user->user_id,
            'username' => $event->user->username,
            'timestamp' => now()->toISOString(),
        ]);

        $this->elasticsearch->indexLoginEvent(
            $event->user->user_id,
            $event->user->username,
            request()?->ip()
        );
    }
}
