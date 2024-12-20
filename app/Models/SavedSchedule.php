<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SavedSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'term_id',
        'name',
        'description',
        'is_public',
        'share_token'
    ];

    protected $casts = [
        'is_public' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(CourseSection::class, 'saved_schedule_sections')
            ->withPivot('color', 'notes')
            ->withTimestamps();
    }

    // Scope for fetching public or user-owned schedules
    public function scopeAccessibleBy($query, ?User $user)
    {
        return $query->where(function ($query) use ($user) {
            $query->where('is_public', true);

            if ($user) {
                $query->orWhere('user_id', $user->id);
            }
        });
    }
}
