<?php

namespace App\Modules\Student\Services;

use App\Modules\Student\Contracts\ITimetableService;
use App\Modules\Student\Repositories\EnrollmentRepository;

class TimetableService implements ITimetableService
{
    protected const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function __construct(
        protected EnrollmentRepository $enrollmentRepository
    ) {}

    public function getWeeklyTimetable(int $studentId): array
    {
        $enrollments = $this->enrollmentRepository->getActiveEnrollmentsWithSchedules($studentId);

        $timetable = array_fill_keys(self::DAYS, []);

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
        foreach ($timetable as &$slots) {
            usort($slots, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));
        }

        return $timetable;
    }

    public function getDayTimetable(int $studentId, string $dayOfWeek): array
    {
        $timetable = $this->getWeeklyTimetable($studentId);

        return $timetable[$dayOfWeek] ?? [];
    }

    public function exportTimetable(int $studentId): array
    {
        $timetable = $this->getWeeklyTimetable($studentId);

        $totalCredits = 0;
        $courseSet = [];

        foreach ($timetable as $slots) {
            foreach ($slots as $slot) {
                if (!isset($courseSet[$slot['course_code']])) {
                    $courseSet[$slot['course_code']] = $slot['course_name'];
                }
            }
        }

        // Count enrolled courses via enrollments
        $enrollments = $this->enrollmentRepository->getActiveEnrollmentsWithSchedules($studentId);
        foreach ($enrollments as $enrollment) {
            if ($enrollment->section && $enrollment->section->course) {
                $totalCredits += $enrollment->section->course->credit_hours ?? 0;
            }
        }

        return [
            'student_id' => $studentId,
            'total_courses' => count($courseSet),
            'total_credits' => $totalCredits,
            'courses' => $courseSet,
            'timetable' => $timetable,
        ];
    }
}
