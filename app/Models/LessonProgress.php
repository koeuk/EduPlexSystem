<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    public $timestamps = false;

    protected $table = 'lesson_progress';

    protected $fillable = [
        'student_id',
        'lesson_id',
        'course_id',
        'status',
        'progress_percentage',
        'time_spent_minutes',
        'video_last_position',
        'scroll_position',
        'first_accessed',
        'last_accessed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'progress_percentage' => 'decimal:2',
            'time_spent_minutes' => 'integer',
            'video_last_position' => 'integer',
            'scroll_position' => 'integer',
            'first_accessed' => 'datetime',
            'last_accessed' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
