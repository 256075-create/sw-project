<?php

namespace App\Modules\Registration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'section_id' => $this->section_id,
            'course_id' => $this->course_id,
            'classroom_id' => $this->classroom_id,
            'section_number' => $this->section_number,
            'instructor_name' => $this->instructor_name,
            'max_capacity' => $this->max_capacity,
            'current_enrollment' => $this->current_enrollment,
            'remaining_capacity' => $this->remaining_capacity,
            'semester' => $this->semester,
            'academic_year' => $this->academic_year,
            'course' => new CourseResource($this->whenLoaded('course')),
            'classroom' => new ClassroomResource($this->whenLoaded('classroom')),
            'schedules' => ScheduleResource::collection($this->whenLoaded('schedules')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
