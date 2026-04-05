<?php

namespace Tests\Feature\Web;

use App\Enums\CashSessionStatus;
use App\Enums\UserRole;
use App\Models\CashSession;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_open_and_close_cash_session(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $this->actingAs($cashier);

        $openResponse = $this->post(route('cash-sessions.store'), [
            'opening_balance_amount' => 150000,
        ]);

        $openResponse->assertRedirect(route('cash-sessions.index'));
        $this->assertDatabaseHas('cash_sessions', [
            'outlet_id' => $cashier->outlet_id,
            'opened_by' => $cashier->id,
            'status' => CashSessionStatus::Open->value,
        ]);

        $session = CashSession::query()->firstOrFail();

        $closeResponse = $this->post(route('cash-sessions.close', $session), [
            'closing_balance_amount' => 170000,
            'closing_note' => 'Shift sore selesai.',
        ]);

        $closeResponse->assertRedirect(route('cash-sessions.index'));
        $this->assertDatabaseHas('cash_sessions', [
            'id' => $session->id,
            'closed_by' => $cashier->id,
            'status' => CashSessionStatus::Closed->value,
        ]);
    }

    public function test_cashier_can_checkout_pos_and_reduce_stock(): void
    {
        $cashier = User::factory()->role(UserRole::Cashier)->create();
        $this->actingAs($cashier);

        CashSession::query()->create([
            'outlet_id' => $cashier->outlet_id,
            'opened_by' => $cashier->id,
            'opened_at' => now(),
            'opening_balance_amount' => 100000,
            'status' => CashSessionStatus::Open,
        ]);

        $category = ProductCategory::query()->create([
            'code' => 'CASE',
            'name' => 'Casing',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'sku' => 'CASE-01',
            'name' => 'Casing Armor',
            'purchase_price_amount' => 25000,
            'selling_price_amount' => 40000,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        Stock::query()->create([
            'outlet_id' => $cashier->outlet_id,
            'product_id' => $product->id,
            'on_hand_qty' => 5,
            'reserved_qty' => 0,
            'minimum_stock' => 2,
        ]);

        $response = $this->post(route('pos.store'), [
            'payment_method' => 'cash',
            'paid_amount' => 80000,
            'items' => [
                ['product_id' => $product->id, 'qty' => 2],
            ],
        ]);

        $response->assertRedirect(route('pos.index'));
        $this->assertDatabaseHas('sales', [
            'outlet_id' => $cashier->outlet_id,
            'sold_by' => $cashier->id,
            'total_amount' => 80000,
        ]);
        $this->assertDatabaseHas('stocks', [
            'outlet_id' => $cashier->outlet_id,
            'product_id' => $product->id,
            'on_hand_qty' => 3,
        ]);
        $this->assertDatabaseHas('cash_transactions', [
            'transaction_type' => 'sale_checkout',
            'amount' => 80000,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'qty' => -2,
        ]);
    }
}
