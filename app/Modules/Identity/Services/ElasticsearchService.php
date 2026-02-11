<?php

namespace App\Modules\Identity\Services;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\Facades\Log;

class ElasticsearchService
{
    protected ?Client $client = null;

    protected function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = ClientBuilder::create()
                ->setHosts([config('identity.elasticsearch.host', 'localhost:9200')])
                ->build();
        }

        return $this->client;
    }

    public function indexLoginEvent(string $userId, string $username, ?string $ipAddress = null): void
    {
        $this->indexAuthEvent('login', $userId, $username, $ipAddress);
    }

    public function indexLogoutEvent(string $userId, string $username, ?string $ipAddress = null): void
    {
        $this->indexAuthEvent('logout', $userId, $username, $ipAddress);
    }

    protected function indexAuthEvent(string $eventType, string $userId, string $username, ?string $ipAddress): void
    {
        try {
            $this->getClient()->index([
                'index' => 'ums-auth-events',
                'body' => [
                    'event_type' => $eventType,
                    'user_id' => $userId,
                    'username' => $username,
                    'ip_address' => $ipAddress,
                    '@timestamp' => now()->toISOString(),
                    'service' => 'ums-api',
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to index auth event to Elasticsearch', [
                'event_type' => $eventType,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
