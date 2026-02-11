<?php

namespace App\Modules\Student\Repositories;

use App\Modules\Student\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentRepository
{
    /**
     * Create a new student record.
     */
    public function create(array $data): Student
    {
        return Student::create($data);
    }

    /**
     * Update an existing student record.
     */
    public function update(int $studentId, array $data): Student
    {
        $student = $this->findById($studentId);

        if (!$student) {
            throw new \InvalidArgumentException('Student not found');
        }

        $student->update($data);

        return $student->fresh(['major', 'major.department', 'major.department.college']);
    }

    /**
     * Find a student by ID with eager loaded relationships.
     */
    public function findById(int $studentId): ?Student
    {
        return Student::with(['major', 'major.department', 'major.department.college'])
            ->find($studentId);
    }

    /**
     * Find a student by student number.
     */
    public function findByStudentNumber(string $studentNumber): ?Student
    {
        return Student::with(['major', 'major.department', 'major.department.college'])
            ->where('student_number', $studentNumber)
            ->first();
    }

    /**
     * Find a student by their linked user_id.
     */
    public function findByUserId(string $userId): ?Student
    {
        return Student::with(['major', 'major.department', 'major.department.college'])
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * List students with filters and pagination.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Student::with(['major', 'major.department', 'major.department.college']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['major_id'])) {
            $query->where('major_id', $filters['major_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Delete a student record.
     */
    public function delete(int $studentId): bool
    {
        $student = $this->findById($studentId);

        if (!$student) {
            return false;
        }

        return $student->delete();
    }
}
