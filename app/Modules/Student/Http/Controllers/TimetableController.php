<?php

namespace App\Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Student\Contracts\ITimetableService;
use Illuminate\Http\JsonResponse;

class TimetableController extends Controller
{
    public function __construct(
        protected ITimetableService $timetableService
    ) {}

    public function show(int $studentId): JsonResponse
    {
        $timetable = $this->timetableService->getWeeklyTimetable($studentId);

        return response()->json(['data' => $timetable], 200);
    }

    public function day(int $studentId, string $dayOfWeek): JsonResponse
    {
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        if (!in_array($dayOfWeek, $validDays)) {
            return response()->json(['error' => 'Invalid day of week.'], 422);
        }

        $timetable = $this->timetableService->getDayTimetable($studentId, $dayOfWeek);

        return response()->json(['data' => $timetable], 200);
    }

    public function export(int $studentId): JsonResponse
    {
        $data = $this->timetableService->exportTimetable($studentId);

        return response()->json(['data' => $data], 200);
    }
}
