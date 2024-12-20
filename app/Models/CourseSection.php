<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSection extends Model
{
    protected $fillable = [
        'course_id',
        'term_id',
        'section_code',
        'status',
        'type',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SectionSchedule::class, 'section_id');
    }

    public function professors(): BelongsToMany
    {
        return $this->belongsToMany(Professor::class);
    }
}
