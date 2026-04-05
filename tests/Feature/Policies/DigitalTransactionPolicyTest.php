<?php

namespace Tests\Feature\Policies;

use App\Enums\DigitalServiceType;
use App\Enums\DigitalTransactionStatus;
use App\Enums\UserRole;
use App\Models\DigitalService;
use App\Models\DigitalTransaction;
use App\Models\Outlet;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class DigitalTransactionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_only_mark_own_outlet_transaction_in_progress(): void
    {
        [$operator, $transaction] = $this->makeTransaction(UserRole::Operator, DigitalTransactionStatus::Draft, false, true);

        $this->assertTrue(Gate::forUser($operator)->allows('markInProgress', $transaction));
    }

    public function test_operator_cannot_mark_sensitive_transaction_as_succeeded(): void
    {
        [$operator, $transaction] = $this->makeTransaction(UserRole::Operator, DigitalTransactionStatus::PendingValidation, true, true);

        $this->assertFalse(Gate::forUser($operator)->allows('markSucceeded', $transaction));
    }

    public function test_supervisor_can_mark_sensitive_transaction_as_succeeded(): void
    {
        [$supervisor, $transaction] = $this->makeTransaction(UserRole::Supervisor, DigitalTransactionStatus::PendingValidation, true, true);

        $this->assertTrue(Gate::forUser($supervisor)->allows('markSucceeded', $transaction));
    }

    private function makeTransaction(UserRole $role, DigitalTransactionStatus $status, bool $sensitive, bool $sameOutlet): array
    {
        $outlet = Outlet::factory()->create();
        $actor = User::factory()->for($outlet)->role($role)->create();
        $otherOutlet = Outlet::factory()->create();

        $serviceCategory = ServiceCategory::query()->create([
            'code' => 'PULSA',
            'name' => 'Pulsa',
            'service_type' => DigitalServiceType::Pulsa,
        ]);

        $service = DigitalService::query()->create([
            'service_category_id' => $serviceCategory->id,
            'code' => fake()->unique()->lexify('SERVICE-???'),
            'name' => 'Pulsa 10K',
            'provider' => 'Manual',
            'default_nominal_amount' => 10000,
            'default_fee_amount' => 1500,
            'is_active' => true,
            'requires_reference' => false,
            'requires_destination_name' => false,
        ]);

        $transaction = DigitalTransaction::query()->create([
            'outlet_id' => $sameOutlet ? $outlet->id : $otherOutlet->id,
            'digital_service_id' => $service->id,
            'code' => fake()->unique()->lexify('DT-????'),
            'status' => $status,
            'destination_account' => '08123456789',
            'nominal_amount' => 10000,
            'fee_amount' => 1500,
            'total_amount' => 11500,
            'cash_effect_amount' => 11500,
            'submitted_at' => now(),
            'created_by' => $actor->id,
            'requires_supervisor_approval' => $sensitive,
        ]);

        return [$actor, $transaction];
    }
}
