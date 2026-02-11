<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Search\StudentSearchService;
use App\Services\Search\CourseSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        protected StudentSearchService $studentSearch,
        protected CourseSearchService $courseSearch
    ) {}

    public function searchStudents(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);
        $filters = $request->only(['status', 'major_name']);

        $results = $this->studentSearch->search($query, $page, $perPage, $filters);

        return response()->json($results, 200);
    }

    public function searchCourses(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);
        $filters = $request->only(['is_active', 'credit_hours']);

        $results = $this->courseSearch->search($query, $page, $perPage, $filters);

        return response()->json($results, 200);
    }
}
