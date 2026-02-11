<?php

namespace App\Modules\Student\Contracts;

use App\Modules\Student\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;

interface IStudentService
{
    /**
     * Create a new student record.
     */
    public function create(array $data): Student;

    /**
     * Update an existing student record.
     */
    public function update(int $studentId, array $data): Student;

    /**
     * Find a student by their ID.
     */
    public function findById(int $studentId): ?Student;

    /**
     * Find a student by their student number.
     */
    public function findByStudentNumber(string $studentNumber): ?Student;

    /**
     * Find a student by their linked user_id.
     */
    public function findByUserId(string $userId): ?Student;

    /**
     * List students with optional filters and pagination.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Delete a student record.
     */
    public function delete(int $studentId): bool;
}
