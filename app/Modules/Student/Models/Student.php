<?php

namespace App\Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Academic\Models\Major;
use App\Modules\Identity\Models\User;

class Student extends Model
{
    use HasFactory;

    protected $table = 'student_students';
    protected $primaryKey = 'student_id';

    protected $fillable = [
        'major_id',
        'user_id',
        'student_number',
        'first_name',
        'last_name',
        'email',
        'enrollment_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'major_id' => 'integer',
            'enrollment_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'major_id', 'major_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id', 'student_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    protected static function newFactory(): \App\Modules\Student\Database\Factories\StudentFactory
    {
        return \App\Modules\Student\Database\Factories\StudentFactory::new();
    }
}
