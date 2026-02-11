<?php

namespace App\Modules\Registration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory;

    protected $table = 'registration_classrooms';
    protected $primaryKey = 'classroom_id';

    protected $fillable = [
        'room_number',
        'building',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'classroom_id', 'classroom_id');
    }

    protected static function newFactory()
    {
        return \App\Modules\Registration\Database\Factories\ClassroomFactory::new();
    }
}
