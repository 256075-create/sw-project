<?php

namespace App\Modules\Registration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $table = 'registration_sections';
    protected $primaryKey = 'section_id';

    protected $fillable = [
        'course_id',
        'classroom_id',
        'section_number',
        'instructor_name',
        'max_capacity',
        'current_enrollment',
        'semester',
        'academic_year',
    ];

    protected function casts(): array
    {
        return [
            'course_id' => 'integer',
            'classroom_id' => 'integer',
            'max_capacity' => 'integer',
            'current_enrollment' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id', 'classroom_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'section_id', 'section_id');
    }

    public function hasAvailableCapacity(): bool
    {
        return $this->current_enrollment < $this->max_capacity;
    }

    public function getRemainingCapacityAttribute(): int
    {
        return $this->max_capacity - $this->current_enrollment;
    }

    protected static function newFactory()
    {
        return \App\Modules\Registration\Database\Factories\SectionFactory::new();
    }
}
