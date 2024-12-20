<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionSchedule extends Model
{
    protected $fillable = [
        'section_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }
}
