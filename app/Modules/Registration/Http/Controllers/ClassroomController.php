<?php

namespace App\Modules\Registration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Registration\Http\Requests\StoreClassroomRequest;
use App\Modules\Registration\Http\Requests\UpdateClassroomRequest;
use App\Modules\Registration\Http\Resources\ClassroomResource;
use App\Modules\Registration\Models\Classroom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClassroomController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Classroom::query();

        if ($request->has('building')) {
            $query->where('building', $request->input('building'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('room_number', 'like', "%{$search}%")
                  ->orWhere('building', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);

        return ClassroomResource::collection($query->paginate($perPage));
    }

    public function store(StoreClassroomRequest $request): JsonResponse
    {
        $classroom = Classroom::create($request->validated());

        return response()->json(new ClassroomResource($classroom), 201);
    }

    public function show(int $classroomId): JsonResponse
    {
        $classroom = Classroom::find($classroomId);

        if (!$classroom) {
            return response()->json(['error' => 'Classroom not found'], 404);
        }

        return response()->json(new ClassroomResource($classroom), 200);
    }

    public function update(UpdateClassroomRequest $request, int $classroomId): JsonResponse
    {
        $classroom = Classroom::findOrFail($classroomId);
        $classroom->update($request->validated());

        return response()->json(new ClassroomResource($classroom->fresh()), 200);
    }

    public function destroy(int $classroomId): JsonResponse
    {
        $classroom = Classroom::findOrFail($classroomId);

        if ($classroom->sections()->count() > 0) {
            return response()->json(['error' => 'Cannot delete classroom with active sections'], 422);
        }

        $classroom->delete();

        return response()->json(['message' => 'Classroom deleted successfully'], 200);
    }
}
