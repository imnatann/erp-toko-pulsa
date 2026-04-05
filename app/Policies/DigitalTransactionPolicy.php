<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DigitalTransaction;
use App\Models\User;

class DigitalTransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All roles can view the list
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DigitalTransaction $digitalTransaction): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([UserRole::Owner, UserRole::Admin, UserRole::Operator, UserRole::Cashier]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DigitalTransaction $digitalTransaction): bool
    {
        return ! $digitalTransaction->status->isFinal();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DigitalTransaction $digitalTransaction): bool
    {
        return $user->hasAnyRole([UserRole::Owner, UserRole::Admin]) && ! $digitalTransaction->status->isFinal();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DigitalTransaction $digitalTransaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DigitalTransaction $digitalTransaction): bool
    {
        return false;
    }
}
