<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Term extends Model
{
    protected $fillable = ['year', 'term'];

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }

    public function importantDates(): HasMany
    {
        return $this->hasMany(ImportantDate::class);
    }
}
