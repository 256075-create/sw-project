<?php

namespace App\Modules\Student\Contracts;

use App\Modules\Student\Models\Enrollment;
use Illuminate\Database\Eloquent\Collection;

interface IEnrollmentService
{
    /**
     * Enroll a student in a section.
     * Checks capacity, duplicate enrollment, and schedule conflicts.
     *
     * @throws \App\Modules\Student\Exceptions\SectionFullException
     * @throws \App\Modules\Student\Exceptions\DuplicateEnrollmentException
     * @throws \App\Modules\Student\Exceptions\ScheduleConflictException
     */
    public function enroll(int $studentId, int $sectionId): Enrollment;

    /**
     * Drop a student from an enrollment.
     * Updates status to 'dropped' and decrements section current_enrollment.
     */
    public function drop(int $enrollmentId): Enrollment;

    /**
     * Get all enrollments for a student.
     */
    public function getStudentEnrollments(int $studentId, array $filters = []): Collection;

    /**
     * Get the weekly timetable for a student.
     */
    public function getStudentTimetable(int $studentId): array;
}
