<?php

namespace App\Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    use HasFactory;

    protected $table = 'academic_universities';
    protected $primaryKey = 'university_id';

    protected $fillable = [
        'name',
        'code',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function colleges(): HasMany
    {
        return $this->hasMany(College::class, 'university_id', 'university_id');
    }

    protected static function newFactory(): \App\Modules\Academic\Database\Factories\UniversityFactory
    {
        return \App\Modules\Academic\Database\Factories\UniversityFactory::new();
    }
}
