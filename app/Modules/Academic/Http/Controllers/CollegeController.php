<?php

namespace App\Modules\Academic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Contracts\IAcademicStructureService;
use App\Modules\Academic\Http\Requests\StoreCollegeRequest;
use App\Modules\Academic\Http\Requests\UpdateCollegeRequest;
use App\Modules\Academic\Http\Resources\CollegeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollegeController extends Controller
{
    public function __construct(
        protected IAcademicStructureService $academicService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['university_id', 'search']);
        $perPage = $request->input('per_page', 15);

        $colleges = $this->academicService->listColleges($filters, $perPage);

        return CollegeResource::collection($colleges);
    }

    public function store(StoreCollegeRequest $request): JsonResponse
    {
        $college = $this->academicService->createCollege($request->validated());

        return response()->json(new CollegeResource($college), 201);
    }

    public function show(int $collegeId): JsonResponse
    {
        $college = $this->academicService->findCollegeById($collegeId);

        if (!$college) {
            return response()->json(['error' => 'College not found'], 404);
        }

        return response()->json(new CollegeResource($college), 200);
    }

    public function update(UpdateCollegeRequest $request, int $collegeId): JsonResponse
    {
        try {
            $college = $this->academicService->updateCollege($collegeId, $request->validated());

            return response()->json(new CollegeResource($college), 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $collegeId): JsonResponse
    {
        try {
            $this->academicService->deleteCollege($collegeId);

            return response()->json(['message' => 'College deleted successfully'], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
