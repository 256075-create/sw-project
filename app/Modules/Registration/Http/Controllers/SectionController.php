<?php

namespace App\Modules\Registration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Registration\Contracts\ISectionService;
use App\Modules\Registration\Http\Requests\StoreSectionRequest;
use App\Modules\Registration\Http\Requests\UpdateSectionRequest;
use App\Modules\Registration\Http\Resources\SectionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SectionController extends Controller
{
    public function __construct(
        protected ISectionService $sectionService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['course_id', 'semester', 'academic_year', 'instructor_name', 'classroom_id', 'department_id', 'has_availability', 'sort_by', 'sort_dir']);
        $perPage = $request->input('per_page', 15);

        return SectionResource::collection($this->sectionService->list($filters, $perPage));
    }

    public function store(StoreSectionRequest $request): JsonResponse
    {
        $section = $this->sectionService->create($request->validated());

        return response()->json(new SectionResource($section), 201);
    }

    public function show(int $sectionId): JsonResponse
    {
        $section = $this->sectionService->getSectionWithSchedules($sectionId);

        if (!$section) {
            return response()->json(['error' => 'Section not found'], 404);
        }

        return response()->json(new SectionResource($section), 200);
    }

    public function update(UpdateSectionRequest $request, int $sectionId): JsonResponse
    {
        try {
            $section = $this->sectionService->update($sectionId, $request->validated());

            return response()->json(new SectionResource($section), 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $sectionId): JsonResponse
    {
        try {
            $this->sectionService->delete($sectionId);

            return response()->json(['message' => 'Section deleted successfully'], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
