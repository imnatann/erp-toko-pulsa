<?php

namespace App\Filament\Traits;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;

trait IsolatesOutletData
{
    /**
     * Override query dasar agar User selain Owner & Admin hanya melihat data outlet mereka sendiri.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        // Bypass untuk Owner dan Admin
        if (in_array($user->role, [UserRole::Owner, UserRole::Admin])) {
            return $query;
        }

        // Pastikan model punya relasi ke outlet atau punya kolom outlet_id
        // Filter berdasarkan outlet_id user login
        return $query->where('outlet_id', $user->outlet_id);
    }
}
