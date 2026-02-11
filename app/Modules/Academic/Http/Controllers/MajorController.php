<?php

namespace App\Modules\Academic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Contracts\IAcademicStructureService;
use App\Modules\Academic\Http\Requests\StoreMajorRequest;
use App\Modules\Academic\Http\Requests\UpdateMajorRequest;
use App\Modules\Academic\Http\Resources\MajorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MajorController extends Controller
{
    public function __construct(
        protected IAcademicStructureService $academicService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['department_id', 'search']);
        $perPage = $request->input('per_page', 15);

        $majors = $this->academicService->listMajors($filters, $perPage);

        return MajorResource::collection($majors);
    }

    public function store(StoreMajorRequest $request): JsonResponse
    {
        try {
            $major = $this->academicService->createMajor($request->validated());

            return response()->json(new MajorResource($major), 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(int $majorId): JsonResponse
    {
        $major = $this->academicService->findMajorById($majorId);

        if (!$major) {
            return response()->json(['error' => 'Major not found'], 404);
        }

        return response()->json(new MajorResource($major), 200);
    }

    public function update(UpdateMajorRequest $request, int $majorId): JsonResponse
    {
        try {
            $major = $this->academicService->updateMajor($majorId, $request->validated());

            return response()->json(new MajorResource($major), 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $majorId): JsonResponse
    {
        try {
            $this->academicService->deleteMajor($majorId);

            return response()->json(['message' => 'Major deleted successfully'], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
