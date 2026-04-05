<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DigitalTransactionStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case InProgress = 'diproses';
    case PendingValidation = 'pending_validasi';
    case Succeeded = 'berhasil';
    case Failed = 'gagal';
    case Cancelled = 'dibatalkan';
    case ManualRefund = 'refund_manual';

    public function isFinal(): bool
    {
        return in_array($this, [self::Succeeded, self::Failed, self::Cancelled, self::ManualRefund], true);
    }

    public function requiresNote(): bool
    {
        return in_array($this, [self::Succeeded, self::Failed, self::Cancelled, self::ManualRefund], true);
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InProgress => 'Diproses',
            self::PendingValidation => 'Menunggu Validasi',
            self::Succeeded => 'Berhasil',
            self::Failed => 'Gagal',
            self::Cancelled => 'Dibatalkan',
            self::ManualRefund => 'Refund Manual',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::InProgress => 'info',
            self::PendingValidation => 'warning',
            self::Succeeded => 'success',
            self::Failed => 'danger',
            self::Cancelled => 'danger',
            self::ManualRefund => 'warning',
        };
    }
}
