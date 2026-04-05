<?php

namespace App\Models;

use App\Enums\DigitalServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'service_type',
    ];

    protected function casts(): array
    {
        return [
            'service_type' => DigitalServiceType::class,
        ];
    }

    public function digitalServices(): HasMany
    {
        return $this->hasMany(DigitalService::class);
    }
}
