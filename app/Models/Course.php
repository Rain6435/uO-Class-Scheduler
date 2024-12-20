<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'subject_id',
        'code',
        'title',
        'description',
        'credits',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            Course::class,
            'course_prerequisites',
            'course_id',
            'prerequisite_course_id'
        )->withPivot('prerequisite_group');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }

    // Helper method to get full course code
    public function getFullCodeAttribute(): string
    {
        return $this->subject->code.' '.$this->code;
    }
}
