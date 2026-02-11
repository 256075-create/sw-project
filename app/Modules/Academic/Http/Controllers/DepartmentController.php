<?php

namespace App\Modules\Academic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Contracts\IAcademicStructureService;
use App\Modules\Academic\Http\Requests\StoreDepartmentRequest;
use App\Modules\Academic\Http\Requests\UpdateDepartmentRequest;
use App\Modules\Academic\Http\Resources\DepartmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentController extends Controller
{
    public function __construct(
        protected IAcademicStructureService $academicService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['college_id', 'search']);
        $perPage = $request->input('per_page', 15);

        $departments = $this->academicService->listDepartments($filters, $perPage);

        return DepartmentResource::collection($departments);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->academicService->createDepartment($request->validated());

        return response()->json(new DepartmentResource($department), 201);
    }

    public function show(int $departmentId): JsonResponse
    {
        $department = $this->academicService->findDepartmentById($departmentId);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        return response()->json(new DepartmentResource($department), 200);
    }

    public function update(UpdateDepartmentRequest $request, int $departmentId): JsonResponse
    {
        try {
            $department = $this->academicService->updateDepartment($departmentId, $request->validated());

            return response()->json(new DepartmentResource($department), 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $departmentId): JsonResponse
    {
        try {
            $this->academicService->deleteDepartment($departmentId);

            return response()->json(['message' => 'Department deleted successfully'], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
