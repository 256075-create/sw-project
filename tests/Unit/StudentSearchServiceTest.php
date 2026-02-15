<?php

namespace Tests\Unit;

use App\Services\Search\ElasticsearchService;
use App\Services\Search\StudentSearchService;
use Mockery;
use Tests\TestCase;

class StudentSearchServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_search_builds_query_and_normalizes_response(): void
    {
        config(['elasticsearch.indices.students.name' => 'students-test']);

        $elasticsearch = Mockery::mock(ElasticsearchService::class);
        $elasticsearch
            ->shouldReceive('search')
            ->once()
            ->with(
                'students-test',
                Mockery::on(function (array $query) {
                    $must = $query['bool']['must'] ?? [];
                    $filter = $query['bool']['filter'] ?? [];

                    $hasQuery = false;
                    foreach ($must as $clause) {
                        if (($clause['multi_match']['query'] ?? null) === 'doe') {
                            $hasQuery = true;
                            break;
                        }
                    }

                    $hasStatus = false;
                    foreach ($filter as $clause) {
                        if (($clause['term']['status'] ?? null) === 'active') {
                            $hasStatus = true;
                            break;
                        }
                    }

                    return $hasQuery && $hasStatus;
                }),
                10,
                10
            )
            ->andReturn([
                'hits' => [
                    'total' => ['value' => 2],
                    'hits' => [
                        ['_source' => ['student_id' => 1, 'full_name' => 'John Doe']],
                        ['_source' => ['student_id' => 2, 'full_name' => 'Jane Doe']],
                    ],
                ],
            ]);

        $service = new StudentSearchService($elasticsearch);
        $result = $service->search('doe', 2, 10, ['status' => 'active']);

        $this->assertSame(2, $result['total']);
        $this->assertSame(2, $result['page']);
        $this->assertSame(10, $result['per_page']);
        $this->assertSame(
            [
                ['student_id' => 1, 'full_name' => 'John Doe'],
                ['student_id' => 2, 'full_name' => 'Jane Doe'],
            ],
            $result['hits']
        );
    }
}
