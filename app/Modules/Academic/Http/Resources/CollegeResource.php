<?php

namespace App\Modules\Academic\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollegeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'college_id' => $this->college_id,
            'university_id' => $this->university_id,
            'name' => $this->name,
            'code' => $this->code,
            'created_at' => $this->created_at?->toISOString(),
            'university' => new UniversityResource($this->whenLoaded('university')),
            'departments' => DepartmentResource::collection($this->whenLoaded('departments')),
        ];
    }
}
