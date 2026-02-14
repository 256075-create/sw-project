<?php

namespace App\Modules\Student\Contracts;

interface ITimetableService
{
    /**
     * Get the weekly timetable for a student.
     * Returns an array keyed by day of the week, each containing sorted schedule slots.
     */
    public function getWeeklyTimetable(int $studentId): array;

    /**
     * Get the timetable for a specific day.
     */
    public function getDayTimetable(int $studentId, string $dayOfWeek): array;

    /**
     * Export timetable data in a structured format suitable for rendering/printing.
     */
    public function exportTimetable(int $studentId): array;
}
