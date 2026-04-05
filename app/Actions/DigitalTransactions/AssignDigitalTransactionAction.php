<?php

namespace App\Actions\DigitalTransactions;

use App\Models\AuditLog;
use App\Models\DigitalTransaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AssignDigitalTransactionAction
{
    public function execute(DigitalTransaction $transaction, User $actor, User $assignee): DigitalTransaction
    {
        Gate::forUser($actor)->authorize('assign', $transaction);

        if (! $assignee->hasAnyRole(['operator', 'supervisor'])) {
            throw new AuthorizationException('Assignee harus operator atau supervisor.');
        }

        if ($assignee->outlet_id !== $transaction->outlet_id) {
            throw new AuthorizationException('Assignee harus dari outlet yang sama.');
        }

        return DB::transaction(function () use ($transaction, $actor, $assignee) {
            $oldValues = [
                'assigned_to' => $transaction->assigned_to,
            ];

            $transaction->forceFill([
                'assigned_to' => $assignee->id,
            ])->save();

            AuditLog::query()->create([
                'outlet_id' => $transaction->outlet_id,
                'user_id' => $actor->id,
                'auditable_type' => DigitalTransaction::class,
                'auditable_id' => $transaction->id,
                'event' => 'digital_transaction.assigned',
                'old_values' => $oldValues,
                'new_values' => ['assigned_to' => $assignee->id],
                'created_at' => now(),
            ]);

            return $transaction->fresh(['assignee']);
        });
    }
}
