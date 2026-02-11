<?php

namespace App\Modules\Student\Services;

use App\Modules\Registration\Models\Schedule;

class ScheduleConflictDetector
{
    public function hasConflict(Schedule $scheduleA, Schedule $scheduleB): bool
    {
        if ($scheduleA->day_of_week !== $scheduleB->day_of_week) {
            return false;
        }

        $startA = strtotime($scheduleA->start_time);
        $endA = strtotime($scheduleA->end_time);
        $startB = strtotime($scheduleB->start_time);
        $endB = strtotime($scheduleB->end_time);

        return $startA < $endB && $endA > $startB;
    }
}
