<?php

namespace App\Modules\Registration\Services;

use App\Modules\Registration\Models\Schedule;
use App\Modules\Registration\Models\Section;
use Illuminate\Database\Eloquent\Collection;

class ScheduleService
{
    public function __construct(
        protected ClassroomConflictDetector $conflictDetector
    ) {}

    /**
     * Get all schedules for a given section.
     */
    public function getBySection(int $sectionId): Collection
    {
        return Schedule::where('section_id', $sectionId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Find a schedule by its ID.
     */
    public function findById(int $scheduleId): ?Schedule
    {
        return Schedule::with('section')->find($scheduleId);
    }

    /**
     * Create a new schedule entry for a section.
     */
    public function create(array $data): Schedule
    {
        $this->validateTimeRange($data['start_time'], $data['end_time']);

        $section = Section::findOrFail($data['section_id']);

        // Check for classroom conflicts
        $conflict = $this->conflictDetector->detect(
            $section->classroom_id,
            $data['day_of_week'],
            $data['start_time'],
            $data['end_time'],
            null // No schedule to exclude (new schedule)
        );

        if ($conflict) {
            throw new \InvalidArgumentException(
                "Classroom scheduling conflict detected: the classroom is already booked on {$data['day_of_week']} " .
                "from {$conflict->start_time} to {$conflict->end_time} for section ID {$conflict->section_id}."
            );
        }

        return Schedule::create($data);
    }

    /**
     * Update an existing schedule entry.
     */
    public function update(int $scheduleId, array $data): Schedule
    {
        $schedule = Schedule::findOrFail($scheduleId);

        $startTime = $data['start_time'] ?? $schedule->start_time;
        $endTime = $data['end_time'] ?? $schedule->end_time;
        $this->validateTimeRange($startTime, $endTime);

        $section = Section::findOrFail($schedule->section_id);
        $dayOfWeek = $data['day_of_week'] ?? $schedule->day_of_week;

        // Check for classroom conflicts, excluding the current schedule
        $conflict = $this->conflictDetector->detect(
            $section->classroom_id,
            $dayOfWeek,
            $startTime,
            $endTime,
            $scheduleId
        );

        if ($conflict) {
            throw new \InvalidArgumentException(
                "Classroom scheduling conflict detected: the classroom is already booked on {$dayOfWeek} " .
                "from {$conflict->start_time} to {$conflict->end_time} for section ID {$conflict->section_id}."
            );
        }

        $schedule->update($data);

        return $schedule->fresh('section');
    }

    /**
     * Delete a schedule entry.
     */
    public function delete(int $scheduleId): bool
    {
        $schedule = Schedule::findOrFail($scheduleId);

        return (bool) $schedule->delete();
    }

    /**
     * Validate that start_time is before end_time.
     */
    protected function validateTimeRange(string $startTime, string $endTime): void
    {
        if (strtotime($startTime) >= strtotime($endTime)) {
            throw new \InvalidArgumentException(
                "Start time ({$startTime}) must be before end time ({$endTime})."
            );
        }
    }
}
