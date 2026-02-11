<?php

namespace App\Modules\Student\Services;

use App\Modules\Student\Contracts\IEnrollmentService;
use App\Modules\Student\Models\Enrollment;
use App\Modules\Student\Repositories\EnrollmentRepository;
use App\Modules\Student\Exceptions\SectionFullException;
use App\Modules\Student\Exceptions\DuplicateEnrollmentException;
use App\Modules\Student\Exceptions\ScheduleConflictException;
use App\Modules\Registration\Contracts\ISectionService;
use Illuminate\Database\Eloquent\Collection;

class EnrollmentService implements IEnrollmentService
{
    public function __construct(
        protected EnrollmentRepository $enrollmentRepository,
        protected ISectionService $sectionService,
        protected ScheduleConflictDetector $scheduleConflictDetector
    ) {}

    public function enroll(int $studentId, int $sectionId): Enrollment
    {
        // Check for duplicate enrollment
        if ($this->enrollmentRepository->existsActiveEnrollment($studentId, $sectionId)) {
            throw new DuplicateEnrollmentException($studentId, $sectionId);
        }

        // Check section capacity
        $section = $this->sectionService->findById($sectionId);

        if (!$section) {
            throw new \InvalidArgumentException("Section #{$sectionId} not found.");
        }

        if (!$section->hasAvailableCapacity()) {
            throw new SectionFullException($sectionId);
        }

        // Check for schedule conflicts
        $sectionWithSchedules = $this->sectionService->getSectionWithSchedules($sectionId);
        $existingEnrollments = $this->enrollmentRepository->getActiveEnrollmentsWithSchedules($studentId);

        foreach ($sectionWithSchedules->schedules as $newSchedule) {
            foreach ($existingEnrollments as $enrollment) {
                foreach ($enrollment->section->schedules as $existingSchedule) {
                    if ($this->scheduleConflictDetector->hasConflict($newSchedule, $existingSchedule)) {
                        throw new ScheduleConflictException([
                            'day_of_week' => $newSchedule->day_of_week,
                            'new_start_time' => $newSchedule->start_time,
                            'new_end_time' => $newSchedule->end_time,
                            'existing_course' => $existingSchedule->section->course->name ?? 'Unknown',
                            'existing_start_time' => $existingSchedule->start_time,
                            'existing_end_time' => $existingSchedule->end_time,
                        ]);
                    }
                }
            }
        }

        // Create enrollment
        $enrollment = $this->enrollmentRepository->create([
            'student_id' => $studentId,
            'section_id' => $sectionId,
            'enrollment_date' => now(),
            'status' => 'enrolled',
        ]);

        // Increment section enrollment
        $this->sectionService->incrementEnrollment($sectionId);

        return $enrollment;
    }

    public function drop(int $enrollmentId): Enrollment
    {
        $enrollment = $this->enrollmentRepository->findById($enrollmentId);

        if (!$enrollment) {
            throw new \InvalidArgumentException("Enrollment #{$enrollmentId} not found.");
        }

        if ($enrollment->status !== 'enrolled') {
            throw new \InvalidArgumentException("Enrollment #{$enrollmentId} is not active (status: {$enrollment->status}).");
        }

        // Update enrollment status
        $updated = $this->enrollmentRepository->update($enrollmentId, [
            'status' => 'dropped',
        ]);

        // Decrement section enrollment
        $this->sectionService->decrementEnrollment($enrollment->section_id);

        return $updated;
    }

    public function getStudentEnrollments(int $studentId, array $filters = []): Collection
    {
        return $this->enrollmentRepository->getByStudentId($studentId, $filters);
    }

    public function getStudentTimetable(int $studentId): array
    {
        $enrollments = $this->enrollmentRepository->getActiveEnrollmentsWithSchedules($studentId);

        $timetable = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($days as $day) {
            $timetable[$day] = [];
        }

        foreach ($enrollments as $enrollment) {
            if (!$enrollment->section || !$enrollment->section->schedules) {
                continue;
            }

            foreach ($enrollment->section->schedules as $schedule) {
                $timetable[$schedule->day_of_week][] = [
                    'enrollment_id' => $enrollment->enrollment_id,
                    'course_name' => $enrollment->section->course->name ?? '',
                    'course_code' => $enrollment->section->course->course_code ?? '',
                    'section_number' => $enrollment->section->section_number,
                    'instructor_name' => $enrollment->section->instructor_name,
                    'classroom' => $enrollment->section->classroom
                        ? $enrollment->section->classroom->building . ' ' . $enrollment->section->classroom->room_number
                        : '',
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                ];
            }
        }

        // Sort each day by start time
        foreach ($timetable as $day => &$slots) {
            usort($slots, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));
        }

        return $timetable;
    }
}
