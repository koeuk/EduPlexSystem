<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizOption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'option_order',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'option_order' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'selected_option_id');
    }
}
