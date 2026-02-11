<?php

namespace App\Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Student\Contracts\IEnrollmentService;
use App\Modules\Student\Http\Requests\EnrollRequest;
use App\Modules\Student\Http\Resources\EnrollmentResource;
use App\Modules\Student\Exceptions\SectionFullException;
use App\Modules\Student\Exceptions\DuplicateEnrollmentException;
use App\Modules\Student\Exceptions\ScheduleConflictException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function __construct(
        protected IEnrollmentService $enrollmentService
    ) {}

    public function index(int $studentId, Request $request): JsonResponse
    {
        $filters = $request->only(['status']);
        $enrollments = $this->enrollmentService->getStudentEnrollments($studentId, $filters);

        return response()->json(EnrollmentResource::collection($enrollments), 200);
    }

    public function enroll(EnrollRequest $request): JsonResponse
    {
        try {
            $enrollment = $this->enrollmentService->enroll(
                $request->validated()['student_id'],
                $request->validated()['section_id']
            );

            return response()->json(new EnrollmentResource($enrollment), 201);
        } catch (SectionFullException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (DuplicateEnrollmentException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        } catch (ScheduleConflictException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function drop(int $enrollmentId): JsonResponse
    {
        try {
            $enrollment = $this->enrollmentService->drop($enrollmentId);

            return response()->json(new EnrollmentResource($enrollment), 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
