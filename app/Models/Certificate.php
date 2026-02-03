<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Certificate extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'course_id',
        'issue_date',
        'certificate_code',
        'verification_url',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['certificate_code', 'issue_date', 'verification_url'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Certificate has been {$eventName}");
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('certificate')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf']);
    }

    public function getCertificateUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('certificate') ?: null;
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public static function generateCode(): string
    {
        return strtoupper('CERT-' . date('Y') . '-' . bin2hex(random_bytes(6)));
    }
}
