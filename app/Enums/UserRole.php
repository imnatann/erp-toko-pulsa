<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Cashier = 'cashier';
    case Operator = 'operator';
    case Supervisor = 'supervisor';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Cashier => 'Kasir',
            self::Operator => 'Operator',
            self::Supervisor => 'Supervisor',
        };
    }
}
