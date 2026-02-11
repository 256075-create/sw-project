<?php

namespace App\Modules\Registration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'classroom_id' => $this->classroom_id,
            'room_number' => $this->room_number,
            'building' => $this->building,
            'capacity' => $this->capacity,
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
