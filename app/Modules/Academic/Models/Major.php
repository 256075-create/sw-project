<?php

namespace App\Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Major extends Model
{
    use HasFactory;

    protected $table = 'academic_majors';
    protected $primaryKey = 'major_id';
    public $timestamps = false;

    protected $fillable = [
        'department_id',
        'name',
        'code',
        'total_credits',
    ];

    protected $casts = [
        'department_id' => 'integer',
        'total_credits' => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    protected static function newFactory(): \App\Modules\Academic\Database\Factories\MajorFactory
    {
        return \App\Modules\Academic\Database\Factories\MajorFactory::new();
    }
}
