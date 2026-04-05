<?php

namespace App\Actions\CashSessions;

use App\Enums\CashSessionStatus;
use App\Models\AuditLog;
use App\Models\CashSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OpenCashSessionAction
{
    public function execute(User $actor, int $openingBalanceAmount): CashSession
    {
        $existing = CashSession::query()
            ->where('outlet_id', $actor->outlet_id)
            ->where('status', CashSessionStatus::Open)
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'opening_balance_amount' => ['Masih ada sesi kas terbuka di outlet ini.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $openingBalanceAmount) {
            $session = CashSession::query()->create([
                'outlet_id' => $actor->outlet_id,
                'opened_by' => $actor->id,
                'opened_at' => now(),
                'opening_balance_amount' => $openingBalanceAmount,
                'status' => CashSessionStatus::Open,
            ]);

            AuditLog::query()->create([
                'outlet_id' => $actor->outlet_id,
                'user_id' => $actor->id,
                'auditable_type' => CashSession::class,
                'auditable_id' => $session->id,
                'event' => 'cash_session.opened',
                'new_values' => $session->toArray(),
                'created_at' => now(),
            ]);

            return $session;
        });
    }
}
