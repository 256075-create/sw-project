<?php

namespace App\Modules\Registration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $table = 'registration_courses';
    protected $primaryKey = 'course_id';

    protected $fillable = [
        'course_code',
        'name',
        'description',
        'credit_hours',
        'department_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_hours' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Academic\Models\Department::class, 'department_id', 'department_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'course_id', 'course_id');
    }

    protected static function newFactory()
    {
        return \App\Modules\Registration\Database\Factories\CourseFactory::new();
    }
}
