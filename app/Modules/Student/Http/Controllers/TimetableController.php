<?php

namespace App\Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Student\Contracts\IEnrollmentService;
use Illuminate\Http\JsonResponse;

class TimetableController extends Controller
{
    public function __construct(
        protected IEnrollmentService $enrollmentService
    ) {}

    public function show(int $studentId): JsonResponse
    {
        $timetable = $this->enrollmentService->getStudentTimetable($studentId);

        return response()->json(['data' => $timetable], 200);
    }
}
