<?php

namespace App\Modules\Registration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Registration\Contracts\ICourseService;
use App\Modules\Registration\Http\Requests\StoreCourseRequest;
use App\Modules\Registration\Http\Requests\UpdateCourseRequest;
use App\Modules\Registration\Http\Resources\CourseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseController extends Controller
{
    public function __construct(
        protected ICourseService $courseService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'is_active', 'course_code', 'sort_by', 'sort_dir']);
        $perPage = $request->input('per_page', 15);

        return CourseResource::collection($this->courseService->list($filters, $perPage));
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->create($request->validated());

        return response()->json(new CourseResource($course), 201);
    }

    public function show(int $courseId): JsonResponse
    {
        $course = $this->courseService->findById($courseId);

        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        return response()->json(new CourseResource($course), 200);
    }

    public function update(UpdateCourseRequest $request, int $courseId): JsonResponse
    {
        try {
            $course = $this->courseService->update($courseId, $request->validated());

            return response()->json(new CourseResource($course), 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $courseId): JsonResponse
    {
        try {
            $this->courseService->delete($courseId);

            return response()->json(['message' => 'Course deleted successfully'], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function activate(int $courseId): JsonResponse
    {
        $course = $this->courseService->activate($courseId);

        return response()->json(new CourseResource($course), 200);
    }

    public function deactivate(int $courseId): JsonResponse
    {
        $course = $this->courseService->deactivate($courseId);

        return response()->json(new CourseResource($course), 200);
    }
}
