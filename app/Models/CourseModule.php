<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CourseModule extends Model
{
    use LogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'module_title',
        'module_order',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'module_order' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['module_title', 'module_order', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Course module has been {$eventName}");
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'module_id')->orderBy('lesson_order');
    }
}
