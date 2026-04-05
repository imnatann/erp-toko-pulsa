<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::Owner, UserRole::Admin, UserRole::Supervisor, UserRole::Cashier]);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([UserRole::Admin, UserRole::Supervisor, UserRole::Cashier]);
    }

    public function view(User $user, Sale $sale): bool
    {
        if ($user->hasAnyRole([UserRole::Owner, UserRole::Admin, UserRole::Supervisor])) {
            return true;
        }

        return $user->outlet_id === $sale->outlet_id;
    }
}
