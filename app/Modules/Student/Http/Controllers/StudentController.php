<?php

namespace App\Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Student\Contracts\IStudentService;
use App\Modules\Student\Http\Requests\StoreStudentRequest;
use App\Modules\Student\Http\Requests\UpdateStudentRequest;
use App\Modules\Student\Http\Resources\StudentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentController extends Controller
{
    public function __construct(
        protected IStudentService $studentService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'status', 'major_id']);
        $perPage = $request->input('per_page', 15);

        return StudentResource::collection($this->studentService->list($filters, $perPage));
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = $this->studentService->create($request->validated());

        return response()->json(new StudentResource($student), 201);
    }

    public function show(int $studentId): JsonResponse
    {
        $student = $this->studentService->findById($studentId);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json(new StudentResource($student), 200);
    }

    public function update(UpdateStudentRequest $request, int $studentId): JsonResponse
    {
        try {
            $student = $this->studentService->update($studentId, $request->validated());

            return response()->json(new StudentResource($student), 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $studentId): JsonResponse
    {
        $result = $this->studentService->delete($studentId);

        if (!$result) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json(['message' => 'Student deleted successfully'], 200);
    }

    public function profile(Request $request): JsonResponse
    {
        $userId = $request->input('auth_user')['user_id'] ?? null;

        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $student = $this->studentService->findByUserId($userId);

        if (!$student) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }

        return response()->json(new StudentResource($student), 200);
    }
}
