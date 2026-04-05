<?php

namespace App\Models;

use App\Enums\DigitalTransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalTransactionStatusLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'digital_transaction_id',
        'from_status',
        'to_status',
        'acted_by',
        'acted_at',
        'note',
        'external_reference',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => DigitalTransactionStatus::class,
            'to_status' => DigitalTransactionStatus::class,
            'acted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(DigitalTransaction::class, 'digital_transaction_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
