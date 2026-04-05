<?php

namespace App\Actions\DigitalTransactions;

use App\Enums\DigitalTransactionStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\CashTransaction;
use App\Models\DigitalTransaction;
use App\Models\DigitalTransactionStatusLog;
use App\Models\LedgerEntry;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TransitionDigitalTransactionStatusAction
{
    /**
     * @param  array{note?: string|null, external_reference?: string|null, metadata?: array<string, mixed>|null}  $context
     */
    public function execute(
        DigitalTransaction $transaction,
        DigitalTransactionStatus $targetStatus,
        User $actor,
        array $context = [],
    ): DigitalTransaction {
        $currentStatus = $transaction->status;

        if (! $this->canTransition($currentStatus, $targetStatus)) {
            throw ValidationException::withMessages([
                'status' => [sprintf('Transisi status %s -> %s tidak diizinkan.', $currentStatus->value, $targetStatus->value)],
            ]);
        }

        if ($targetStatus->requiresNote() && blank($context['note'] ?? null)) {
            throw ValidationException::withMessages([
                'note' => ['Catatan wajib diisi untuk perubahan status ini.'],
            ]);
        }

        $this->authorize($transaction, $targetStatus, $actor);

        return DB::transaction(function () use ($transaction, $targetStatus, $actor, $context, $currentStatus) {
            $oldValues = $transaction->only([
                'status',
                'processed_at',
                'processed_by',
                'validated_at',
                'validated_by',
                'supervisor_approved_by',
                'validation_note',
                'external_reference',
            ]);

            $updates = [
                'status' => $targetStatus,
            ];

            if ($targetStatus === DigitalTransactionStatus::InProgress) {
                $updates['processed_at'] = now();
                $updates['processed_by'] = $actor->getKey();
            }

            if (in_array($targetStatus, [DigitalTransactionStatus::Succeeded, DigitalTransactionStatus::Failed], true)) {
                $updates['validated_at'] = now();
                $updates['validated_by'] = $actor->getKey();
                $updates['validation_note'] = $context['note'] ?? null;
                $updates['external_reference'] = $context['external_reference'] ?? $transaction->external_reference;
            }

            if ($targetStatus === DigitalTransactionStatus::ManualRefund) {
                $updates['validation_note'] = $context['note'] ?? null;
            }

            if ($transaction->requires_supervisor_approval && $targetStatus->isFinal() && $actor->hasRole(UserRole::Supervisor)) {
                $updates['supervisor_approved_by'] = $actor->getKey();
            }

            $transaction->fill($updates);
            $transaction->save();

            DigitalTransactionStatusLog::query()->create([
                'digital_transaction_id' => $transaction->getKey(),
                'from_status' => $currentStatus,
                'to_status' => $targetStatus,
                'acted_by' => $actor->getKey(),
                'acted_at' => now(),
                'note' => $context['note'] ?? null,
                'external_reference' => $context['external_reference'] ?? null,
                'metadata' => $context['metadata'] ?? null,
            ]);

            $this->syncFinancialEffects($transaction, $targetStatus, $actor, $context['note'] ?? null);

            AuditLog::query()->create([
                'outlet_id' => $transaction->outlet_id,
                'user_id' => $actor->getKey(),
                'auditable_type' => DigitalTransaction::class,
                'auditable_id' => $transaction->getKey(),
                'event' => 'digital_transaction.status_changed',
                'old_values' => $oldValues,
                'new_values' => $transaction->only(array_keys($oldValues)),
                'created_at' => now(),
            ]);

            return $transaction->fresh(['statusLogs']);
        });
    }

    private function canTransition(DigitalTransactionStatus $from, DigitalTransactionStatus $to): bool
    {
        return match ($from) {
            DigitalTransactionStatus::Draft => in_array($to, [DigitalTransactionStatus::InProgress, DigitalTransactionStatus::Cancelled], true),
            DigitalTransactionStatus::InProgress => $to === DigitalTransactionStatus::PendingValidation,
            DigitalTransactionStatus::PendingValidation => in_array($to, [DigitalTransactionStatus::Succeeded, DigitalTransactionStatus::Failed], true),
            DigitalTransactionStatus::Failed => $to === DigitalTransactionStatus::ManualRefund,
            default => false,
        };
    }

    private function authorize(DigitalTransaction $transaction, DigitalTransactionStatus $targetStatus, User $actor): void
    {
        $ability = match ($targetStatus) {
            DigitalTransactionStatus::InProgress => 'markInProgress',
            DigitalTransactionStatus::PendingValidation => 'markPendingValidation',
            DigitalTransactionStatus::Succeeded => 'markSucceeded',
            DigitalTransactionStatus::Failed => 'markFailed',
            DigitalTransactionStatus::Cancelled => 'cancel',
            DigitalTransactionStatus::ManualRefund => 'refundManual',
            default => null,
        };

        if ($ability === null) {
            throw new AuthorizationException('Status target tidak memiliki aturan otorisasi.');
        }

        Gate::forUser($actor)->authorize($ability, $transaction);

        if (
            $transaction->requires_supervisor_approval
            && in_array($targetStatus, [DigitalTransactionStatus::Succeeded, DigitalTransactionStatus::ManualRefund], true)
            && ! $actor->hasRole(UserRole::Supervisor)
        ) {
            throw new AuthorizationException('Transaksi ini membutuhkan approval supervisor untuk status final.');
        }
    }

    private function syncFinancialEffects(DigitalTransaction $transaction, DigitalTransactionStatus $targetStatus, User $actor, ?string $note): void
    {
        if ($targetStatus === DigitalTransactionStatus::Succeeded) {
            CashTransaction::query()->create([
                'outlet_id' => $transaction->outlet_id,
                'reference_type' => DigitalTransaction::class,
                'reference_id' => $transaction->getKey(),
                'direction' => $transaction->cash_effect_amount >= 0 ? 'in' : 'out',
                'transaction_type' => 'digital_transaction_success',
                'amount' => abs($transaction->cash_effect_amount),
                'effective_at' => now(),
                'note' => $note,
                'created_by' => $actor->getKey(),
            ]);

            LedgerEntry::query()->create([
                'outlet_id' => $transaction->outlet_id,
                'reference_type' => DigitalTransaction::class,
                'reference_id' => $transaction->getKey(),
                'entry_date' => now()->toDateString(),
                'entry_type' => 'credit',
                'account_code' => 'DIGITAL_FEE_REVENUE',
                'amount' => $transaction->fee_amount,
                'description' => 'Fee admin transaksi digital '.$transaction->code,
                'created_by' => $actor->getKey(),
            ]);

            return;
        }

        if ($targetStatus === DigitalTransactionStatus::ManualRefund) {
            CashTransaction::query()->create([
                'outlet_id' => $transaction->outlet_id,
                'reference_type' => DigitalTransaction::class,
                'reference_id' => $transaction->getKey(),
                'direction' => 'out',
                'transaction_type' => 'digital_transaction_manual_refund',
                'amount' => $transaction->total_amount,
                'effective_at' => now(),
                'note' => $note,
                'created_by' => $actor->getKey(),
            ]);

            LedgerEntry::query()->create([
                'outlet_id' => $transaction->outlet_id,
                'reference_type' => DigitalTransaction::class,
                'reference_id' => $transaction->getKey(),
                'entry_date' => now()->toDateString(),
                'entry_type' => 'debit',
                'account_code' => 'DIGITAL_MANUAL_REFUND',
                'amount' => $transaction->total_amount,
                'description' => 'Refund manual transaksi digital '.$transaction->code,
                'created_by' => $actor->getKey(),
            ]);
        }
    }
}
