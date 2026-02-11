<?php

namespace App\Services\Search;

use App\Modules\Registration\Models\Course;

class CourseSearchService
{
    protected string $indexName;

    public function __construct(
        protected ElasticsearchService $elasticsearch
    ) {
        $this->indexName = config('elasticsearch.indices.courses.name', 'ums_courses');
    }

    public function setupIndex(): void
    {
        $config = config('elasticsearch.indices.courses');

        if ($this->elasticsearch->indexExists($this->indexName)) {
            $this->elasticsearch->deleteIndex($this->indexName);
        }

        $this->elasticsearch->createIndex(
            $this->indexName,
            $config['settings'] ?? [],
            $config['mappings'] ?? []
        );
    }

    public function indexCourse(Course $course): void
    {
        $this->elasticsearch->indexDocument($this->indexName, $course->course_id, [
            'course_id' => $course->course_id,
            'course_code' => $course->course_code,
            'name' => $course->name,
            'description' => $course->description,
            'credit_hours' => $course->credit_hours,
            'is_active' => $course->is_active,
            'created_at' => $course->created_at?->toIso8601String(),
        ]);
    }

    public function removeCourse(int $courseId): void
    {
        $this->elasticsearch->deleteDocument($this->indexName, $courseId);
    }

    public function search(string $query, int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $must = [];
        $filter = [];

        if (!empty($query)) {
            $must[] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['name^3', 'course_code^2', 'description'],
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO',
                ],
            ];
        }

        if (isset($filters['is_active'])) {
            $filter[] = ['term' => ['is_active' => (bool) $filters['is_active']]];
        }

        if (!empty($filters['credit_hours'])) {
            $filter[] = ['term' => ['credit_hours' => (int) $filters['credit_hours']]];
        }

        $esQuery = [
            'bool' => [
                'must' => $must ?: [['match_all' => (object) []]],
                'filter' => $filter,
            ],
        ];

        $from = ($page - 1) * $perPage;

        $results = $this->elasticsearch->search($this->indexName, $esQuery, $from, $perPage);

        return [
            'total' => $results['hits']['total']['value'] ?? 0,
            'hits' => array_map(fn($hit) => $hit['_source'], $results['hits']['hits'] ?? []),
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function reindexAll(): int
    {
        $this->setupIndex();

        $count = 0;
        Course::chunk(100, function ($courses) use (&$count) {
            $documents = [];

            foreach ($courses as $course) {
                $documents[] = [
                    'id' => $course->course_id,
                    'body' => [
                        'course_id' => $course->course_id,
                        'course_code' => $course->course_code,
                        'name' => $course->name,
                        'description' => $course->description,
                        'credit_hours' => $course->credit_hours,
                        'is_active' => $course->is_active,
                        'created_at' => $course->created_at?->toIso8601String(),
                    ],
                ];
                $count++;
            }

            $this->elasticsearch->bulkIndex($this->indexName, $documents);
        });

        return $count;
    }
}
