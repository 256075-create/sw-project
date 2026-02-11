<?php

namespace App\Console\Commands;

use App\Services\Search\StudentSearchService;
use App\Services\Search\CourseSearchService;
use Illuminate\Console\Command;

class ElasticsearchReindex extends Command
{
    protected $signature = 'elasticsearch:reindex {--index=all : The index to reindex (students, courses, or all)}';

    protected $description = 'Reindex data into Elasticsearch';

    public function handle(StudentSearchService $studentSearch, CourseSearchService $courseSearch): int
    {
        $index = $this->option('index');

        if ($index === 'all' || $index === 'students') {
            $this->info('Reindexing students...');
            $count = $studentSearch->reindexAll();
            $this->info("Indexed {$count} students.");
        }

        if ($index === 'all' || $index === 'courses') {
            $this->info('Reindexing courses...');
            $count = $courseSearch->reindexAll();
            $this->info("Indexed {$count} courses.");
        }

        if (!in_array($index, ['all', 'students', 'courses'])) {
            $this->error("Unknown index: {$index}. Available: students, courses, all");
            return 1;
        }

        $this->info('Reindexing complete.');
        return 0;
    }
}
