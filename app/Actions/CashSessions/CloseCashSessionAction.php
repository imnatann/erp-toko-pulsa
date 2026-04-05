<?php

namespace App\Actions\CashSessions;

use App\Enums\CashSessionStatus;
use App\Models\AuditLog;
use App\Models\CashSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CloseCashSessionAction
{
    public function execute(CashSession $session, User $actor, int $closingBalanceAmount, ?string $note = null): CashSession
    {
        if ($session->status !== CashSessionStatus::Open) {
            throw ValidationException::withMessages([
                'closing_balance_amount' => ['Sesi kas ini sudah ditutup.'],
            ]);
        }

        return DB::transaction(function () use ($session, $actor, $closingBalanceAmount, $note) {
            $oldValues = $session->toArray();

            $session->fill([
                'closed_by' => $actor->id,
                'closed_at' => now(),
                'closing_balance_amount' => $closingBalanceAmount,
                'closing_note' => $note,
                'status' => CashSessionStatus::Closed,
            ])->save();

            AuditLog::query()->create([
                'outlet_id' => $session->outlet_id,
                'user_id' => $actor->id,
                'auditable_type' => CashSession::class,
                'auditable_id' => $session->id,
                'event' => 'cash_session.closed',
                'old_values' => $oldValues,
                'new_values' => $session->fresh()->toArray(),
                'created_at' => now(),
            ]);

            return $session->fresh();
        });
    }
}
