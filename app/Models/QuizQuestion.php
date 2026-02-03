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

class QuizQuestion extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'question_type',
        'points',
        'question_order',
        'explanation',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'question_order' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['question_text', 'question_type', 'points', 'question_order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Quiz question has been {$eventName}");
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('question_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('question_image') ?: null;
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('option_order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'question_id');
    }

    public function correctOption()
    {
        return $this->options()->where('is_correct', true)->first();
    }
}
