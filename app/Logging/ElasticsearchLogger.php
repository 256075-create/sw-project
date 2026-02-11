<?php

namespace App\Logging;

use Elastic\Elasticsearch\ClientBuilder;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface;

class ElasticsearchLogger
{
    public function __invoke(array $config): LoggerInterface
    {
        $handler = new ElasticsearchHandler(
            $config['host'] ?? 'localhost:9200',
            $config['index'] ?? 'ums-app-logs',
            Level::fromName($config['level'] ?? 'debug')
        );

        return new Logger('elasticsearch', [$handler]);
    }
}

class ElasticsearchHandler extends AbstractProcessingHandler
{
    protected $client;
    protected string $index;

    public function __construct(string $host, string $index, Level $level)
    {
        parent::__construct($level);
        $this->index = $index;
        $this->client = ClientBuilder::create()
            ->setHosts([$host])
            ->build();
    }

    protected function write(LogRecord $record): void
    {
        try {
            $this->client->index([
                'index' => $this->index,
                'body' => [
                    'message' => $record->message,
                    'level' => $record->level->name,
                    'channel' => $record->channel,
                    'context' => $record->context,
                    '@timestamp' => $record->datetime->format('c'),
                    'extra' => $record->extra,
                ],
            ]);
        } catch (\Exception $e) {
            // Silently fail — avoid infinite loop if ES is down
        }
    }
}
