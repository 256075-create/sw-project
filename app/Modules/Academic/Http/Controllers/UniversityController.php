<?php

namespace App\Modules\Academic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Contracts\IAcademicStructureService;
use App\Modules\Academic\Http\Requests\StoreUniversityRequest;
use App\Modules\Academic\Http\Requests\UpdateUniversityRequest;
use App\Modules\Academic\Http\Resources\UniversityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UniversityController extends Controller
{
    public function __construct(
        protected IAcademicStructureService $academicService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search']);
        $perPage = $request->input('per_page', 15);

        $universities = $this->academicService->listUniversities($filters, $perPage);

        return UniversityResource::collection($universities);
    }

    public function store(StoreUniversityRequest $request): JsonResponse
    {
        $university = $this->academicService->createUniversity($request->validated());

        return response()->json(new UniversityResource($university), 201);
    }

    public function show(int $universityId): JsonResponse
    {
        $university = $this->academicService->findUniversityById($universityId);

        if (!$university) {
            return response()->json(['error' => 'University not found'], 404);
        }

        return response()->json(new UniversityResource($university), 200);
    }

    public function update(UpdateUniversityRequest $request, int $universityId): JsonResponse
    {
        try {
            $university = $this->academicService->updateUniversity($universityId, $request->validated());

            return response()->json(new UniversityResource($university), 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $universityId): JsonResponse
    {
        try {
            $this->academicService->deleteUniversity($universityId);

            return response()->json(['message' => 'University deleted successfully'], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function hierarchy(): JsonResponse
    {
        $hierarchy = $this->academicService->getHierarchy();

        return response()->json(UniversityResource::collection($hierarchy), 200);
    }
}
