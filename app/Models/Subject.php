<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['code', 'name'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
