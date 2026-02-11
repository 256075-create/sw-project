<?php

namespace App\Modules\Registration\Contracts;

use App\Modules\Registration\Models\Course;
use Illuminate\Pagination\LengthAwarePaginator;

interface ICourseService
{
    /**
     * Get a paginated list of courses with optional filters.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a course by its ID.
     */
    public function findById(int $courseId): ?Course;

    /**
     * Find a course by its course code.
     */
    public function findByCode(string $courseCode): ?Course;

    /**
     * Create a new course.
     */
    public function create(array $data): Course;

    /**
     * Update an existing course.
     */
    public function update(int $courseId, array $data): Course;

    /**
     * Delete a course (only if no active sections exist).
     */
    public function delete(int $courseId): bool;

    /**
     * Activate a course.
     */
    public function activate(int $courseId): Course;

    /**
     * Deactivate a course.
     */
    public function deactivate(int $courseId): Course;
}
