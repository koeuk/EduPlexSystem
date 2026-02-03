<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Lesson extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'course_id',
        'module_id',
        'lesson_title',
        'lesson_type',
        'lesson_order',
        'description',
        'content',
        'video_duration',
        'quiz_id',
        'is_mandatory',
        'duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'lesson_order' => 'integer',
            'video_duration' => 'integer',
            'is_mandatory' => 'boolean',
            'duration_minutes' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['lesson_title', 'lesson_type', 'lesson_order', 'is_mandatory'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Lesson has been {$eventName}");
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('video')
            ->singleFile()
            ->acceptsMimeTypes(['video/mp4', 'video/webm', 'video/ogg']);

        $this->addMediaCollection('thumbnail')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300)
            ->performOnCollections('thumbnail');
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('video') ?: null;
    }

    public function getVideoThumbnailUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('thumbnail', 'thumb') ?: null;
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        if ($this->video_duration) {
            $minutes = floor($this->video_duration / 60);
            $seconds = $this->video_duration % 60;
            return sprintf('%d:%02d', $minutes, $seconds);
        }
        return $this->duration_minutes ? $this->duration_minutes . ' min' : 'N/A';
    }
}
