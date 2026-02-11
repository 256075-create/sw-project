<?php

namespace App\Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Registration\Models\Section;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'student_enrollments';
    protected $primaryKey = 'enrollment_id';

    protected $fillable = [
        'student_id',
        'section_id',
        'enrollment_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'student_id' => 'integer',
            'section_id' => 'integer',
            'enrollment_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    protected static function newFactory(): \App\Modules\Student\Database\Factories\EnrollmentFactory
    {
        return \App\Modules\Student\Database\Factories\EnrollmentFactory::new();
    }
}
