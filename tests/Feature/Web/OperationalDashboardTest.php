<?php

namespace Tests\Feature\Web;

use App\Enums\DigitalServiceType;
use App\Enums\DigitalTransactionStatus;
use App\Models\DigitalService;
use App\Models\DigitalTransaction;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_pending_transaction_and_low_stock_sections(): void
    {
        $outlet = Outlet::factory()->create(['name' => 'Outlet A']);
        $user = User::factory()->for($outlet)->create();
        $this->actingAs($user);

        $serviceCategory = ServiceCategory::query()->create([
            'code' => 'DATA',
            'name' => 'Data',
            'service_type' => DigitalServiceType::Data,
        ]);

        $service = DigitalService::query()->create([
            'service_category_id' => $serviceCategory->id,
            'code' => 'DATA-TEST',
            'name' => 'Data Test',
            'provider' => 'Manual',
            'default_nominal_amount' => 15000,
            'default_fee_amount' => 2000,
            'is_active' => true,
            'requires_reference' => false,
            'requires_destination_name' => false,
        ]);

        DigitalTransaction::query()->create([
            'outlet_id' => $outlet->id,
            'digital_service_id' => $service->id,
            'code' => 'DT-DASH-001',
            'status' => DigitalTransactionStatus::PendingValidation,
            'destination_account' => '08123456789',
            'nominal_amount' => 15000,
            'fee_amount' => 2000,
            'total_amount' => 17000,
            'cash_effect_amount' => 17000,
            'submitted_at' => now()->subMinutes(35),
            'created_by' => $user->id,
        ]);

        $category = ProductCategory::query()->create(['code' => 'AKS', 'name' => 'Aksesoris', 'is_active' => true]);
        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'sku' => 'SKU-1',
            'name' => 'Kabel Data',
            'purchase_price_amount' => 10000,
            'selling_price_amount' => 15000,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        \DB::table('stocks')->insert([
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'on_hand_qty' => 1,
            'reserved_qty' => 0,
            'minimum_stock' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Antrian Validasi');
        $response->assertSee('DT-DASH-001');
        $response->assertSee('Escalated');
        $response->assertSee('Stok Kritis');
        $response->assertSee('Kabel Data');
    }
}
