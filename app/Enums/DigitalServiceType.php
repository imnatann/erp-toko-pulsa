<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DigitalServiceType: string implements HasLabel
{
    case Pulsa = 'pulsa';
    case Data = 'data';
    case Voucher = 'voucher';
    case EWallet = 'e_wallet';
    case Transfer = 'transfer';
    case CashOut = 'cash_out';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pulsa => 'Pulsa',
            self::Data => 'Paket Data',
            self::Voucher => 'Voucher',
            self::EWallet => 'Top Up E-Wallet',
            self::Transfer => 'Transfer',
            self::CashOut => 'Tarik Tunai',
        };
    }

    public function defaultCashEffectSign(): int
    {
        return match ($this) {
            self::CashOut => -1,
            default => 1,
        };
    }
}
