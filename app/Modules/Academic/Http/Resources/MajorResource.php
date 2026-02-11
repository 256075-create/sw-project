<?php

namespace App\Modules\Academic\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MajorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'major_id' => $this->major_id,
            'department_id' => $this->department_id,
            'name' => $this->name,
            'code' => $this->code,
            'total_credits' => $this->total_credits,
            'department' => new DepartmentResource($this->whenLoaded('department')),
        ];
    }
}
