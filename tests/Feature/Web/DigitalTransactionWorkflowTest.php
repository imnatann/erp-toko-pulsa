<?php

namespace Tests\Feature\Web;

use App\Enums\DigitalServiceType;
use App\Enums\DigitalTransactionStatus;
use App\Enums\UserRole;
use App\Models\DigitalService;
use App\Models\DigitalTransaction;
use App\Models\ManualChannel;
use App\Models\Outlet;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DigitalTransactionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_digital_transaction_ticket_from_web_form(): void
    {
        $outlet = Outlet::factory()->create();
        $cashier = User::factory()->for($outlet)->role(UserRole::Cashier)->create();
        $this->actingAs($cashier);

        $serviceCategory = ServiceCategory::query()->create([
            'code' => 'EWALLET',
            'name' => 'E-Wallet',
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
        $channel = ManualChannel::query()->create([
            'code' => 'MANUAL',
            'name' => 'Manual App',
            'channel_type' => 'app',
            'is_active' => true,
        ]);

        $response = $this->post(route('digital-transactions.store'), [
            'outlet_id' => $outlet->id,
            'digital_service_id' => $service->id,
            'manual_channel_id' => $channel->id,
            'destination_account' => '08123456789',
            'nominal_amount' => 10000,
            'fee_amount' => 1500,
            'operator_note' => 'Pelanggan menunggu di toko',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('digital_transactions', [
            'created_by' => $cashier->id,
            'status' => DigitalTransactionStatus::Draft->value,
            'destination_account' => '08123456789',
            'total_amount' => 11500,
        ]);
    }

    public function test_operator_can_transition_transaction_from_detail_page(): void
    {
        $outlet = Outlet::factory()->create();
        $cashier = User::factory()->for($outlet)->role(UserRole::Cashier)->create();
        $operator = User::factory()->for($outlet)->role(UserRole::Operator)->create();
        $serviceCategory = ServiceCategory::query()->create([
            'code' => 'PULSA',
            'name' => 'Pulsa',
            'service_type' => DigitalServiceType::Pulsa,
        ]);
        $service = DigitalService::query()->create([
            'service_category_id' => $serviceCategory->id,
            'code' => 'PULSA-5K',
            'name' => 'Pulsa 5K',
            'provider' => 'Manual',
            'default_nominal_amount' => 5000,
            'default_fee_amount' => 1000,
            'is_active' => true,
            'requires_reference' => false,
            'requires_destination_name' => false,
        ]);

        $transaction = DigitalTransaction::query()->create([
            'outlet_id' => $outlet->id,
            'digital_service_id' => $service->id,
            'code' => 'DT-WEB-001',
            'status' => DigitalTransactionStatus::Draft,
            'destination_account' => '08123456789',
            'nominal_amount' => 5000,
            'fee_amount' => 1000,
            'total_amount' => 6000,
            'cash_effect_amount' => 6000,
            'submitted_at' => now(),
            'created_by' => $cashier->id,
        ]);

        $this->actingAs($operator);

        $startResponse = $this->post(route('digital-transactions.transition', $transaction), [
            'target_status' => DigitalTransactionStatus::InProgress->value,
        ]);

        $startResponse->assertRedirect(route('digital-transactions.show', $transaction));
        $this->assertDatabaseHas('digital_transactions', [
            'id' => $transaction->id,
            'status' => DigitalTransactionStatus::InProgress->value,
            'processed_by' => $operator->id,
        ]);

        $pendingResponse = $this->post(route('digital-transactions.transition', $transaction), [
            'target_status' => DigitalTransactionStatus::PendingValidation->value,
        ]);

        $pendingResponse->assertRedirect(route('digital-transactions.show', $transaction));
        $this->assertDatabaseHas('digital_transactions', [
            'id' => $transaction->id,
            'status' => DigitalTransactionStatus::PendingValidation->value,
        ]);
    }

    public function test_queue_page_can_filter_by_sla_and_run_quick_success_action(): void
    {
        $outlet = Outlet::factory()->create(['name' => 'Outlet Queue']);
        $cashier = User::factory()->for($outlet)->role(UserRole::Cashier)->create();
        $operator = User::factory()->for($outlet)->role(UserRole::Operator)->create();
        $supervisor = User::factory()->for($outlet)->role(UserRole::Supervisor)->create();
        $serviceCategory = ServiceCategory::query()->create([
            'code' => 'DATAQ',
            'name' => 'Data Queue',
            'service_type' => DigitalServiceType::Data,
        ]);
        $service = DigitalService::query()->create([
            'service_category_id' => $serviceCategory->id,
            'code' => 'DATA-QUEUE',
            'name' => 'Data Queue 1GB',
            'provider' => 'Manual',
            'default_nominal_amount' => 15000,
            'default_fee_amount' => 2000,
            'is_active' => true,
            'requires_reference' => false,
            'requires_destination_name' => false,
        ]);

        Carbon::setTestNow(now());

        $oldTransaction = DigitalTransaction::query()->create([
            'outlet_id' => $outlet->id,
            'digital_service_id' => $service->id,
            'code' => 'DT-QUEUE-OLD',
            'status' => DigitalTransactionStatus::PendingValidation,
            'destination_account' => '081111111111',
            'nominal_amount' => 15000,
            'fee_amount' => 2000,
            'total_amount' => 17000,
            'cash_effect_amount' => 17000,
            'submitted_at' => now()->subMinutes(45),
            'processed_at' => now()->subMinutes(43),
            'processed_by' => $operator->id,
            'created_by' => $cashier->id,
        ]);

        DigitalTransaction::query()->create([
            'outlet_id' => $outlet->id,
            'digital_service_id' => $service->id,
            'code' => 'DT-QUEUE-NEW',
            'status' => DigitalTransactionStatus::InProgress,
            'destination_account' => '082222222222',
            'nominal_amount' => 15000,
            'fee_amount' => 2000,
            'total_amount' => 17000,
            'cash_effect_amount' => 17000,
            'submitted_at' => now()->subMinutes(5),
            'created_by' => $cashier->id,
        ]);

        $this->actingAs($operator);

        $queueResponse = $this->get(route('digital-transactions.queue', ['sla' => '30']));
        $queueResponse->assertOk();
        $queueResponse->assertSee('DT-QUEUE-OLD');
        $queueResponse->assertDontSee('DT-QUEUE-NEW');
        $queueResponse->assertSee('Escalated');

        $assignResponse = $this->post(route('digital-transactions.assign', $oldTransaction), [
            'assignee_id' => $supervisor->id,
            'sla' => '30',
        ]);

        $assignResponse->assertRedirect(route('digital-transactions.queue', ['sla' => '30']));
        $this->assertDatabaseHas('digital_transactions', [
            'id' => $oldTransaction->id,
            'assigned_to' => $supervisor->id,
        ]);

        $quickActionResponse = $this->post(route('digital-transactions.quick-transition', $oldTransaction), [
            'target_status' => DigitalTransactionStatus::Succeeded->value,
            'note' => 'Provider mengonfirmasi transaksi sukses.',
            'external_reference' => 'QREF-001',
            'sla' => '30',
        ]);

        $quickActionResponse->assertRedirect(route('digital-transactions.queue', ['sla' => '30']));
        $this->assertDatabaseHas('digital_transactions', [
            'id' => $oldTransaction->id,
            'status' => DigitalTransactionStatus::Succeeded->value,
            'validated_by' => $operator->id,
            'external_reference' => 'QREF-001',
        ]);

        Carbon::setTestNow();
    }
}
