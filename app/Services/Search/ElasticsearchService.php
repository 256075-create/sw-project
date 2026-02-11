<?php

namespace App\Services\Search;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;

class ElasticsearchService
{
    protected ?Client $client = null;

    public function getClient(): Client
    {
        if ($this->client === null) {
            $hosts = config('elasticsearch.hosts');
            $builder = ClientBuilder::create();

            foreach ($hosts as $host) {
                $url = $host['scheme'] . '://' . $host['host'] . ':' . $host['port'];
                $builder->setHosts([$url]);

                if (!empty($host['user']) && !empty($host['pass'])) {
                    $builder->setBasicAuthentication($host['user'], $host['pass']);
                }
            }

            $this->client = $builder->build();
        }

        return $this->client;
    }

    public function createIndex(string $indexName, array $settings = [], array $mappings = []): void
    {
        $params = [
            'index' => $indexName,
            'body' => [],
        ];

        if (!empty($settings)) {
            $params['body']['settings'] = $settings;
        }

        if (!empty($mappings)) {
            $params['body']['mappings'] = $mappings;
        }

        $this->getClient()->indices()->create($params);
    }

    public function deleteIndex(string $indexName): void
    {
        if ($this->indexExists($indexName)) {
            $this->getClient()->indices()->delete(['index' => $indexName]);
        }
    }

    public function indexExists(string $indexName): bool
    {
        return $this->getClient()->indices()->exists(['index' => $indexName])->asBool();
    }

    public function indexDocument(string $indexName, int|string $id, array $body): void
    {
        $this->getClient()->index([
            'index' => $indexName,
            'id' => $id,
            'body' => $body,
        ]);
    }

    public function deleteDocument(string $indexName, int|string $id): void
    {
        try {
            $this->getClient()->delete([
                'index' => $indexName,
                'id' => $id,
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to delete document {$id} from index {$indexName}: " . $e->getMessage());
        }
    }

    public function search(string $indexName, array $query, int $from = 0, int $size = 15): array
    {
        $params = [
            'index' => $indexName,
            'body' => [
                'query' => $query,
                'from' => $from,
                'size' => $size,
            ],
        ];

        $response = $this->getClient()->search($params);

        return $response->asArray();
    }

    public function bulkIndex(string $indexName, array $documents): void
    {
        if (empty($documents)) {
            return;
        }

        $params = ['body' => []];

        foreach ($documents as $doc) {
            $params['body'][] = [
                'index' => [
                    '_index' => $indexName,
                    '_id' => $doc['id'],
                ],
            ];
            $params['body'][] = $doc['body'];
        }

        $this->getClient()->bulk($params);
    }
}
