<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Quiz extends Model
{
    use LogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'quiz_title',
        'instructions',
        'time_limit_minutes',
        'passing_score',
        'max_attempts',
        'show_correct_answers',
        'randomize_questions',
    ];

    protected function casts(): array
    {
        return [
            'passing_score' => 'decimal:2',
            'time_limit_minutes' => 'integer',
            'max_attempts' => 'integer',
            'show_correct_answers' => 'boolean',
            'randomize_questions' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['quiz_title', 'time_limit_minutes', 'passing_score', 'max_attempts'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Quiz has been {$eventName}");
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('question_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->questions()->sum('points');
    }

    public function getTotalQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }
}
