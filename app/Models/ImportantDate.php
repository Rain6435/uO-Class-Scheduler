<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportantDate extends Model
{
    protected $fillable = [
        'term_id',
        'category',
        'subcategory',
        'title',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}
