<?php

namespace App\Models;

use App\Enums\DigitalTransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class DigitalTransaction extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Digital Transaction {$eventName}");
    }

    protected $fillable = [
        'outlet_id',
        'customer_id',
        'digital_service_id',
        'manual_channel_id',
        'code',
        'status',
        'destination_account',
        'destination_name',
        'nominal_amount',
        'fee_amount',
        'total_amount',
        'cash_effect_amount',
        'submitted_at',
        'processed_at',
        'validated_at',
        'created_by',
        'processed_by',
        'assigned_to',
        'validated_by',
        'supervisor_approved_by',
        'operator_note',
        'validation_note',
        'external_reference',
        'requires_supervisor_approval',
    ];

    protected function casts(): array
    {
        return [
            'status' => DigitalTransactionStatus::class,
            'submitted_at' => 'datetime',
            'processed_at' => 'datetime',
            'validated_at' => 'datetime',
            'requires_supervisor_approval' => 'boolean',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function digitalService(): BelongsTo
    {
        return $this->belongsTo(DigitalService::class);
    }

    public function manualChannel(): BelongsTo
    {
        return $this->belongsTo(ManualChannel::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function supervisorApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_approved_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(DigitalTransactionStatusLog::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TransactionAttachment::class);
    }
}
