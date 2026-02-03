<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class QuizAttempt extends Model
{
    use LogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
        'student_id',
        'attempt_number',
        'score_percentage',
        'total_points',
        'max_points',
        'passed',
        'started_at',
        'submitted_at',
        'time_taken_minutes',
    ];

    protected function casts(): array
    {
        return [
            'score_percentage' => 'decimal:2',
            'total_points' => 'integer',
            'max_points' => 'integer',
            'passed' => 'boolean',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'time_taken_minutes' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['score_percentage', 'passed', 'submitted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Quiz attempt has been {$eventName}");
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }
}
