<?php

namespace Tests\Feature\DigitalTransactions;

use App\Actions\DigitalTransactions\TransitionDigitalTransactionStatusAction;
use App\Enums\DigitalServiceType;
use App\Enums\DigitalTransactionStatus;
use App\Enums\UserRole;
use App\Models\DigitalService;
use App\Models\DigitalTransaction;
use App\Models\Outlet;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransitionDigitalTransactionStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_complete_happy_path_until_success(): void
    {
        [$operator, $transaction] = $this->makeTransaction(UserRole::Operator);
        $action = app(TransitionDigitalTransactionStatusAction::class);

        $action->execute($transaction, DigitalTransactionStatus::InProgress, $operator);
        $transaction = $action->execute($transaction->fresh(), DigitalTransactionStatus::PendingValidation, $operator);
        $transaction = $action->execute($transaction->fresh(), DigitalTransactionStatus::Succeeded, $operator, [
            'note' => 'Provider confirmed successful top up.',
            'external_reference' => 'REF-001',
        ]);

        $this->assertSame(DigitalTransactionStatus::Succeeded, $transaction->status);
        $this->assertNotNull($transaction->validated_at);
        $this->assertDatabaseCount('digital_transaction_status_logs', 3);
        $this->assertDatabaseHas('cash_transactions', [
            'reference_id' => $transaction->id,
            'transaction_type' => 'digital_transaction_success',
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'reference_id' => $transaction->id,
            'account_code' => 'DIGITAL_FEE_REVENUE',
            'amount' => $transaction->fee_amount,
        ]);
    }

    public function test_final_status_requires_note(): void
    {
        [$operator, $transaction] = $this->makeTransaction(UserRole::Operator, DigitalTransactionStatus::PendingValidation);

        $this->expectException(ValidationException::class);

        app(TransitionDigitalTransactionStatusAction::class)->execute(
            $transaction,
            DigitalTransactionStatus::Succeeded,
            $operator,
        );
    }

    public function test_cashier_cannot_mark_transaction_as_success(): void
    {
        [$cashier, $transaction] = $this->makeTransaction(UserRole::Cashier, DigitalTransactionStatus::PendingValidation);

        $this->expectException(AuthorizationException::class);

        app(TransitionDigitalTransactionStatusAction::class)->execute(
            $transaction,
            DigitalTransactionStatus::Succeeded,
            $cashier,
            ['note' => 'Tidak berhak finalisasi.'],
        );
    }

    public function test_supervisor_approval_is_required_for_sensitive_success_transition(): void
    {
        [$operator, $transaction] = $this->makeTransaction(UserRole::Operator, DigitalTransactionStatus::PendingValidation, true);

        $this->expectException(AuthorizationException::class);

        app(TransitionDigitalTransactionStatusAction::class)->execute(
            $transaction,
            DigitalTransactionStatus::Succeeded,
            $operator,
            ['note' => 'Operator mencoba menyelesaikan transaksi besar.'],
        );
    }

    private function makeTransaction(
        UserRole $actorRole,
        DigitalTransactionStatus $status = DigitalTransactionStatus::Draft,
        bool $requiresSupervisorApproval = false,
    ): array {
        $outlet = Outlet::factory()->create();
        $actor = User::factory()->for($outlet)->role($actorRole)->create();

        $serviceCategory = ServiceCategory::query()->create([
            'code' => 'EWALLET',
            'name' => 'Top Up E-Wallet',
            'service_type' => DigitalServiceType::EWallet,
        ]);

        $service = DigitalService::query()->create([
            'service_category_id' => $serviceCategory->id,
            'code' => 'DANA-10K',
            'name' => 'DANA 10K',
            'provider' => 'Manual',
            'default_nominal_amount' => 10000,
            'default_fee_amount' => 1500,
            'is_active' => true,
            'requires_reference' => false,
            'requires_destination_name' => false,
        ]);

        $transaction = DigitalTransaction::query()->create([
            'outlet_id' => $outlet->id,
            'digital_service_id' => $service->id,
            'code' => 'DT-TEST-001',
            'status' => $status,
            'destination_account' => '08123456789',
            'nominal_amount' => 10000,
            'fee_amount' => 1500,
            'total_amount' => 11500,
            'cash_effect_amount' => 11500,
            'submitted_at' => now(),
            'created_by' => $actor->id,
            'requires_supervisor_approval' => $requiresSupervisorApproval,
        ]);

        if ($status !== DigitalTransactionStatus::Draft) {
            $transaction->processed_at = now();
            $transaction->processed_by = $actor->id;
            $transaction->save();
        }

        return [$actor, $transaction->fresh()];
    }
}
