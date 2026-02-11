<?php

namespace App\Services\Search;

use App\Modules\Student\Models\Student;

class StudentSearchService
{
    protected string $indexName;

    public function __construct(
        protected ElasticsearchService $elasticsearch
    ) {
        $this->indexName = config('elasticsearch.indices.students.name', 'ums_students');
    }

    public function setupIndex(): void
    {
        $config = config('elasticsearch.indices.students');

        if ($this->elasticsearch->indexExists($this->indexName)) {
            $this->elasticsearch->deleteIndex($this->indexName);
        }

        $this->elasticsearch->createIndex(
            $this->indexName,
            $config['settings'] ?? [],
            $config['mappings'] ?? []
        );
    }

    public function indexStudent(Student $student): void
    {
        $student->loadMissing(['major', 'major.department', 'major.department.college']);

        $this->elasticsearch->indexDocument($this->indexName, $student->student_id, [
            'student_id' => $student->student_id,
            'student_number' => $student->student_number,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'full_name' => $student->first_name . ' ' . $student->last_name,
            'email' => $student->email,
            'status' => $student->status,
            'major_name' => $student->major?->name,
            'department_name' => $student->major?->department?->name,
            'college_name' => $student->major?->department?->college?->name,
            'enrollment_date' => $student->enrollment_date?->toIso8601String(),
            'created_at' => $student->created_at?->toIso8601String(),
        ]);
    }

    public function removeStudent(int $studentId): void
    {
        $this->elasticsearch->deleteDocument($this->indexName, $studentId);
    }

    public function search(string $query, int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $must = [];
        $filter = [];

        if (!empty($query)) {
            $must[] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['full_name^3', 'student_number^2', 'email', 'major_name', 'department_name'],
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO',
                ],
            ];
        }

        if (!empty($filters['status'])) {
            $filter[] = ['term' => ['status' => $filters['status']]];
        }

        if (!empty($filters['major_name'])) {
            $filter[] = ['match' => ['major_name' => $filters['major_name']]];
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
        Student::with(['major', 'major.department', 'major.department.college'])
            ->chunk(100, function ($students) use (&$count) {
                $documents = [];

                foreach ($students as $student) {
                    $documents[] = [
                        'id' => $student->student_id,
                        'body' => [
                            'student_id' => $student->student_id,
                            'student_number' => $student->student_number,
                            'first_name' => $student->first_name,
                            'last_name' => $student->last_name,
                            'full_name' => $student->first_name . ' ' . $student->last_name,
                            'email' => $student->email,
                            'status' => $student->status,
                            'major_name' => $student->major?->name,
                            'department_name' => $student->major?->department?->name,
                            'college_name' => $student->major?->department?->college?->name,
                            'enrollment_date' => $student->enrollment_date?->toIso8601String(),
                            'created_at' => $student->created_at?->toIso8601String(),
                        ],
                    ];
                    $count++;
                }

                $this->elasticsearch->bulkIndex($this->indexName, $documents);
            });

        return $count;
    }
}
