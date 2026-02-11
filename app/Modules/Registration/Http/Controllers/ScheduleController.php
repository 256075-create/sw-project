<?php

namespace App\Modules\Registration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Registration\Http\Requests\StoreScheduleRequest;
use App\Modules\Registration\Http\Requests\UpdateScheduleRequest;
use App\Modules\Registration\Http\Resources\ScheduleResource;
use App\Modules\Registration\Services\ScheduleService;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService
    ) {}

    public function index(int $sectionId): JsonResponse
    {
        $schedules = $this->scheduleService->getBySection($sectionId);

        return response()->json(ScheduleResource::collection($schedules), 200);
    }

    public function store(StoreScheduleRequest $request): JsonResponse
    {
        try {
            $schedule = $this->scheduleService->create($request->validated());

            return response()->json(new ScheduleResource($schedule), 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(int $scheduleId): JsonResponse
    {
        $schedule = $this->scheduleService->findById($scheduleId);

        if (!$schedule) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        return response()->json(new ScheduleResource($schedule), 200);
    }

    public function update(UpdateScheduleRequest $request, int $scheduleId): JsonResponse
    {
        try {
            $schedule = $this->scheduleService->update($scheduleId, $request->validated());

            return response()->json(new ScheduleResource($schedule), 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $scheduleId): JsonResponse
    {
        $this->scheduleService->delete($scheduleId);

        return response()->json(['message' => 'Schedule deleted successfully'], 200);
    }
}
