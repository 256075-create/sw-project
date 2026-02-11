<?php

namespace App\Modules\Academic\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'department_id' => $this->department_id,
            'college_id' => $this->college_id,
            'name' => $this->name,
            'code' => $this->code,
            'created_at' => $this->created_at?->toISOString(),
            'college' => new CollegeResource($this->whenLoaded('college')),
            'majors' => MajorResource::collection($this->whenLoaded('majors')),
        ];
    }
}
