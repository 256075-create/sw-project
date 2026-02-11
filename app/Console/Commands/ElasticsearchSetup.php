<?php

namespace App\Console\Commands;

use App\Services\Search\ElasticsearchService;
use Illuminate\Console\Command;

class ElasticsearchSetup extends Command
{
    protected $signature = 'elasticsearch:setup {--force : Force recreate indices}';

    protected $description = 'Set up Elasticsearch indices with proper mappings';

    public function handle(ElasticsearchService $elasticsearch): int
    {
        $indices = config('elasticsearch.indices', []);
        $force = $this->option('force');

        foreach ($indices as $key => $config) {
            $indexName = $config['name'];

            if ($elasticsearch->indexExists($indexName)) {
                if ($force) {
                    $this->warn("Deleting existing index: {$indexName}");
                    $elasticsearch->deleteIndex($indexName);
                } else {
                    $this->info("Index {$indexName} already exists. Use --force to recreate.");
                    continue;
                }
            }

            $this->info("Creating index: {$indexName}");
            $elasticsearch->createIndex(
                $indexName,
                $config['settings'] ?? [],
                $config['mappings'] ?? []
            );
            $this->info("Index {$indexName} created successfully.");
        }

        $this->info('Elasticsearch setup complete.');
        return 0;
    }
}
