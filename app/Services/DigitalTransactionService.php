<?php

namespace App\Services;

use App\Enums\DigitalTransactionStatus;
use App\Models\DigitalTransaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class DigitalTransactionService
{
    /**
     * Submit a new transaction (draft -> diproses)
     */
    public function submitTransaction(DigitalTransaction $transaction, User $operator): void
    {
        if ($transaction->status !== DigitalTransactionStatus::Draft) {
            throw new Exception('Hanya transaksi draft yang bisa disubmit.');
        }

        DB::transaction(function () use ($transaction, $operator) {
            $transaction->update([
                'status' => DigitalTransactionStatus::InProgress,
                'submitted_at' => now(),
                'processed_by' => $operator->id, // Mark who picked it up
            ]);

            $this->logStatus($transaction, $operator, DigitalTransactionStatus::InProgress, 'Transaksi mulai diproses oleh operator.');
        });
    }

    /**
     * Mark as pending validation (diproses -> pending_validasi)
     */
    public function markAsPendingValidation(DigitalTransaction $transaction, User $operator, ?string $note = null): void
    {
        if ($transaction->status !== DigitalTransactionStatus::InProgress) {
            throw new Exception('Transaksi harus dalam status diproses.');
        }

        DB::transaction(function () use ($transaction, $operator, $note) {
            $transaction->update([
                'status' => DigitalTransactionStatus::PendingValidation,
                'processed_at' => now(),
                'operator_note' => $note,
            ]);

            $this->logStatus($transaction, $operator, DigitalTransactionStatus::PendingValidation, 'Menunggu validasi supervisor/admin.');
        });
    }

    /**
     * Validate transaction as success (pending_validasi -> berhasil)
     */
    public function validateAsSuccess(DigitalTransaction $transaction, User $validator, ?string $note = null): void
    {
        if ($transaction->status !== DigitalTransactionStatus::PendingValidation) {
            throw new Exception('Transaksi harus dalam status pending validasi.');
        }

        DB::transaction(function () use ($transaction, $validator, $note) {
            $transaction->update([
                'status' => DigitalTransactionStatus::Succeeded,
                'validated_at' => now(),
                'validated_by' => $validator->id,
                'validation_note' => $note,
            ]);

            $this->logStatus($transaction, $validator, DigitalTransactionStatus::Succeeded, 'Validasi sukses.');

            // Record to CashTransaction/Ledger automatically
            $this->recordToLedger($transaction);
        });
    }

    /**
     * Record successful transaction to Ledger & Cash
     */
    protected function recordToLedger(DigitalTransaction $transaction): void
    {
        // Add to Ledger Entry
        DB::table('ledger_entries')->insert([
            'outlet_id' => $transaction->outlet_id,
            'reference_type' => DigitalTransaction::class,
            'reference_id' => $transaction->id,
            'entry_date' => now()->toDateString(),
            'entry_type' => 'digital_sale',
            'account_code' => '4000', // Example Sales Revenue account
            'amount' => $transaction->total_amount,
            'description' => "Pendapatan Transaksi Digital {$transaction->code}",
            'created_by' => $transaction->validated_by,
        ]);

        if ($transaction->fee_amount > 0) {
            DB::table('ledger_entries')->insert([
                'outlet_id' => $transaction->outlet_id,
                'reference_type' => DigitalTransaction::class,
                'reference_id' => $transaction->id,
                'entry_date' => now()->toDateString(),
                'entry_type' => 'digital_fee',
                'account_code' => '4001', // Example Fee Revenue account
                'amount' => $transaction->fee_amount,
                'description' => "Fee Admin Transaksi {$transaction->code}",
                'created_by' => $transaction->validated_by,
            ]);
        }

        // Affect Physical Cash if there is a cash_effect_amount
        if ($transaction->cash_effect_amount != 0) {
            $direction = $transaction->cash_effect_amount > 0 ? 'in' : 'out';
            DB::table('cash_transactions')->insert([
                'outlet_id' => $transaction->outlet_id,
                'reference_type' => DigitalTransaction::class,
                'reference_id' => $transaction->id,
                'direction' => $direction,
                'transaction_type' => 'digital_cash_effect',
                'amount' => abs($transaction->cash_effect_amount),
                'effective_at' => now(),
                'note' => "Mutasi Kas untuk Transaksi {$transaction->code}",
                'created_by' => $transaction->validated_by,
            ]);
        }
    }

    /**
     * Validate transaction as failed (pending_validasi -> gagal)
     */
    public function validateAsFailed(DigitalTransaction $transaction, User $validator, ?string $note = null): void
    {
        if ($transaction->status !== DigitalTransactionStatus::PendingValidation) {
            throw new Exception('Transaksi harus dalam status pending validasi.');
        }

        DB::transaction(function () use ($transaction, $validator, $note) {
            $transaction->update([
                'status' => DigitalTransactionStatus::Failed,
                'validated_at' => now(),
                'validated_by' => $validator->id,
                'validation_note' => $note,
            ]);

            $this->logStatus($transaction, $validator, DigitalTransactionStatus::Failed, 'Validasi gagal.');
        });
    }

    /**
     * Cancel a draft transaction (draft -> dibatalkan)
     */
    public function cancelTransaction(DigitalTransaction $transaction, User $actor, ?string $note = null): void
    {
        if ($transaction->status !== DigitalTransactionStatus::Draft) {
            throw new Exception('Hanya transaksi draft yang bisa dibatalkan langsung. Transaksi yang sedang diproses harus diselesaikan dulu.');
        }

        DB::transaction(function () use ($transaction, $actor, $note) {
            $transaction->update([
                'status' => DigitalTransactionStatus::Cancelled,
                'validation_note' => $note,
            ]);

            $this->logStatus($transaction, $actor, DigitalTransactionStatus::Cancelled, $note ?? 'Transaksi dibatalkan.');
        });
    }

    /**
     * Internal method to log the status change
     */
    protected function logStatus(DigitalTransaction $transaction, User $user, DigitalTransactionStatus $status, string $note): void
    {
        $transaction->statusLogs()->create([
            'to_status' => $status,
            'acted_by' => $user->id,
            'acted_at' => now(),
            'note' => $note,
        ]);
    }
}
