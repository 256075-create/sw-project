<?php

namespace App\Modules\Registration\Services;

use App\Modules\Registration\Models\Schedule;
use App\Modules\Registration\Models\Section;

class ClassroomConflictDetector
{
    /**
     * Detect if a classroom has a scheduling conflict at the given day/time.
     *
     * Checks whether the specified classroom is already booked for another section
     * at overlapping times on the same day. Returns the conflicting schedule if
     * a conflict exists, or null if the time slot is available.
     *
     * @param int         $classroomId   The classroom to check for conflicts.
     * @param string      $dayOfWeek     The day of the week (e.g., 'Monday').
     * @param string      $startTime     The proposed start time (HH:MM or HH:MM:SS).
     * @param string      $endTime       The proposed end time (HH:MM or HH:MM:SS).
     * @param int|null    $excludeScheduleId  A schedule ID to exclude from conflict checking (for updates).
     * @return Schedule|null The conflicting schedule, or null if no conflict.
     */
    public function detect(
        int $classroomId,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $excludeScheduleId = null
    ): ?Schedule {
        // Find all section IDs that use this classroom
        $sectionIds = Section::where('classroom_id', $classroomId)
            ->pluck('section_id');

        if ($sectionIds->isEmpty()) {
            return null;
        }

        $query = Schedule::whereIn('section_id', $sectionIds)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($q) use ($startTime, $endTime) {
                // Overlap condition: existing.start < proposed.end AND existing.end > proposed.start
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($excludeScheduleId !== null) {
            $query->where('schedule_id', '!=', $excludeScheduleId);
        }

        return $query->first();
    }

    /**
     * Get all conflicts for a classroom on a specific day.
     *
     * @param int    $classroomId The classroom to check.
     * @param string $dayOfWeek   The day of the week.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConflictsForDay(int $classroomId, string $dayOfWeek): \Illuminate\Database\Eloquent\Collection
    {
        $sectionIds = Section::where('classroom_id', $classroomId)
            ->pluck('section_id');

        if ($sectionIds->isEmpty()) {
            return Schedule::query()->whereRaw('1 = 0')->get();
        }

        return Schedule::with('section.course')
            ->whereIn('section_id', $sectionIds)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Check if a classroom is available at a specific day/time slot.
     *
     * @param int    $classroomId
     * @param string $dayOfWeek
     * @param string $startTime
     * @param string $endTime
     * @return bool True if the classroom is available, false otherwise.
     */
    public function isAvailable(
        int $classroomId,
        string $dayOfWeek,
        string $startTime,
        string $endTime
    ): bool {
        return $this->detect($classroomId, $dayOfWeek, $startTime, $endTime) === null;
    }
}
