<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CourseReview extends Model
{
    use LogsActivity;

    protected $fillable = [
        'course_id',
        'student_id',
        'rating',
        'review_text',
        'would_recommend',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'would_recommend' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['rating', 'review_text', 'would_recommend'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Course review has been {$eventName}");
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
