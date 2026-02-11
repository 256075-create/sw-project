<?php

namespace App\Modules\Registration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'registration_schedules';
    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'section_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'section_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    protected static function newFactory()
    {
        return \App\Modules\Registration\Database\Factories\ScheduleFactory::new();
    }
}
