<?php

namespace App\Modules\Student\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'enrollment_id' => $this->enrollment_id,
            'student_id' => $this->student_id,
            'section_id' => $this->section_id,
            'enrollment_date' => $this->enrollment_date,
            'status' => $this->status,
            'section' => $this->whenLoaded('section', function () {
                return [
                    'section_id' => $this->section->section_id,
                    'section_number' => $this->section->section_number,
                    'instructor_name' => $this->section->instructor_name,
                    'max_capacity' => $this->section->max_capacity,
                    'current_enrollment' => $this->section->current_enrollment,
                    'semester' => $this->section->semester,
                    'academic_year' => $this->section->academic_year,
                    'course' => $this->section->relationLoaded('course') ? [
                        'course_id' => $this->section->course->course_id,
                        'course_code' => $this->section->course->course_code,
                        'name' => $this->section->course->name,
                        'credit_hours' => $this->section->course->credit_hours,
                    ] : null,
                    'classroom' => $this->section->relationLoaded('classroom') ? [
                        'classroom_id' => $this->section->classroom->classroom_id,
                        'room_number' => $this->section->classroom->room_number,
                        'building' => $this->section->classroom->building,
                    ] : null,
                    'schedules' => $this->section->relationLoaded('schedules')
                        ? $this->section->schedules->map(fn($s) => [
                            'day_of_week' => $s->day_of_week,
                            'start_time' => $s->start_time,
                            'end_time' => $s->end_time,
                        ])
                        : null,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
