<?php

namespace App\Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $table = 'academic_departments';
    protected $primaryKey = 'department_id';

    const UPDATED_AT = null;

    protected $fillable = [
        'college_id',
        'name',
        'code',
    ];

    protected $casts = [
        'college_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function majors(): HasMany
    {
        return $this->hasMany(Major::class, 'department_id', 'department_id');
    }

    protected static function newFactory(): \App\Modules\Academic\Database\Factories\DepartmentFactory
    {
        return \App\Modules\Academic\Database\Factories\DepartmentFactory::new();
    }
}
