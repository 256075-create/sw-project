<?php

namespace App\Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    use HasFactory;

    protected $table = 'academic_colleges';
    protected $primaryKey = 'college_id';

    const UPDATED_AT = null;

    protected $fillable = [
        'university_id',
        'name',
        'code',
    ];

    protected $casts = [
        'university_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class, 'university_id', 'university_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'college_id', 'college_id');
    }

    protected static function newFactory(): \App\Modules\Academic\Database\Factories\CollegeFactory
    {
        return \App\Modules\Academic\Database\Factories\CollegeFactory::new();
    }
}
