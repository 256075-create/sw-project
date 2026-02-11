<?php

namespace App\Modules\Registration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->course_id,
            'course_code' => $this->course_code,
            'name' => $this->name,
            'description' => $this->description,
            'credit_hours' => $this->credit_hours,
            'is_active' => $this->is_active,
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
