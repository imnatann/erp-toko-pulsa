<?php

namespace App\Policies;

use App\Enums\CashSessionStatus;
use App\Enums\UserRole;
use App\Models\CashSession;
use App\Models\User;

class CashSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::Owner, UserRole::Admin, UserRole::Supervisor, UserRole::Cashier]);
    }

    public function open(User $user): bool
    {
        return $user->hasAnyRole([UserRole::Admin, UserRole::Supervisor, UserRole::Cashier]);
    }

    public function close(User $user, CashSession $session): bool
    {
        if ($session->status !== CashSessionStatus::Open) {
            return false;
        }

        if ($user->hasAnyRole([UserRole::Admin, UserRole::Supervisor])) {
            return true;
        }

        return $user->hasRole(UserRole::Cashier) && $user->outlet_id === $session->outlet_id;
    }
}
