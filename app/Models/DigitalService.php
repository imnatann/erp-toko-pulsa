<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalService extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_category_id',
        'code',
        'name',
        'provider',
        'default_nominal_amount',
        'default_fee_amount',
        'is_active',
        'requires_reference',
        'requires_destination_name',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_reference' => 'boolean',
            'requires_destination_name' => 'boolean',
        ];
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }
}
