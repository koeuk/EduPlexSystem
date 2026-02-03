<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Enrollment extends Model
{
    use LogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'course_id',
        'enrollment_date',
        'completion_date',
        'progress_percentage',
        'status',
        'last_accessed',
        'payment_status',
        'certificate_issued',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_date' => 'datetime',
            'completion_date' => 'datetime',
            'last_accessed' => 'datetime',
            'progress_percentage' => 'decimal:2',
            'certificate_issued' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'progress_percentage', 'payment_status', 'certificate_issued'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Enrollment has been {$eventName}");
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
