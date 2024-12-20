<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Professor extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'rmp_id',
        'average_rating',
        'total_ratings',
    ];

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(CourseSection::class);
    }

    // Helper method to get full name
    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}
