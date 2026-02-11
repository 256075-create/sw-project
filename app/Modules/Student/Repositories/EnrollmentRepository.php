<?php

namespace App\Modules\Student\Repositories;

use App\Modules\Student\Models\Enrollment;
use Illuminate\Database\Eloquent\Collection;

class EnrollmentRepository
{
    /**
     * Create a new enrollment record.
     */
    public function create(array $data): Enrollment
    {
        $enrollment = Enrollment::create($data);

        return $enrollment->fresh([
            'section',
            'section.course',
            'section.schedules',
            'section.classroom',
        ]);
    }

    /**
     * Find an enrollment by ID with eager loaded relationships.
     */
    public function findById(int $enrollmentId): ?Enrollment
    {
        return Enrollment::with([
            'section',
            'section.course',
            'section.schedules',
            'section.classroom',
        ])->find($enrollmentId);
    }

    /**
     * Update an enrollment record.
     */
    public function update(int $enrollmentId, array $data): Enrollment
    {
        $enrollment = $this->findById($enrollmentId);

        if (!$enrollment) {
            throw new \InvalidArgumentException('Enrollment not found');
        }

        $enrollment->update($data);

        return $enrollment->fresh([
            'section',
            'section.course',
            'section.schedules',
            'section.classroom',
        ]);
    }

    /**
     * Get all enrollments for a student with optional status filter.
     */
    public function getByStudentId(int $studentId, array $filters = []): Collection
    {
        $query = Enrollment::with([
            'section',
            'section.course',
            'section.schedules',
            'section.classroom',
        ])->where('student_id', $studentId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('enrollment_date', 'desc')->get();
    }

    /**
     * Check if a student is already enrolled in a specific section (with active status).
     */
    public function existsActiveEnrollment(int $studentId, int $sectionId): bool
    {
        return Enrollment::where('student_id', $studentId)
            ->where('section_id', $sectionId)
            ->where('status', 'enrolled')
            ->exists();
    }

    /**
     * Get active enrollments for a student with section schedules loaded.
     */
    public function getActiveEnrollmentsWithSchedules(int $studentId): Collection
    {
        return Enrollment::with([
            'section',
            'section.course',
            'section.schedules',
            'section.classroom',
        ])
        ->where('student_id', $studentId)
        ->where('status', 'enrolled')
        ->get();
    }
}
