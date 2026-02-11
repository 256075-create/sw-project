<?php

namespace App\Modules\Student\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_id' => $this->student_id,
            'student_number' => $this->student_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'major_id' => $this->major_id,
            'user_id' => $this->user_id,
            'enrollment_date' => $this->enrollment_date,
            'status' => $this->status,
            'major' => $this->whenLoaded('major', function () {
                return [
                    'major_id' => $this->major->major_id,
                    'name' => $this->major->name,
                    'code' => $this->major->code,
                    'department' => $this->major->relationLoaded('department') ? [
                        'department_id' => $this->major->department->department_id,
                        'name' => $this->major->department->name,
                        'college' => $this->major->department->relationLoaded('college') ? [
                            'college_id' => $this->major->department->college->college_id,
                            'name' => $this->major->department->college->name,
                        ] : null,
                    ] : null,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
