<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use LogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'course_id',
        'amount',
        'payment_method',
        'transaction_id',
        'bakong_transaction_id',
        'qr_string',
        'md5_hash',
        'qr_expires_at',
        'bakong_status',
        'bakong_response',
        'payment_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'datetime',
            'qr_expires_at' => 'datetime',
            'bakong_response' => 'array',
        ];
    }

    public function isQRExpired(): bool
    {
        return $this->qr_expires_at && $this->qr_expires_at->isPast();
    }

    public function isPendingBakong(): bool
    {
        return $this->payment_method === 'bakong' && $this->bakong_status === 'PENDING';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'payment_method', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Payment has been {$eventName}");
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

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}
