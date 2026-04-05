<?php

namespace App\Services;

use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Checkout: buat Sale + SaleItem + kurangi stok + catat kas masuk.
     *
     * @param  array{
     *   outlet_id: int,
     *   customer_id: int|null,
     *   items: array<array{product_id: int, qty: int, unit_price_amount: int, discount_amount: int}>,
     *   discount_amount: int,
     *   paid_amount: int,
     *   payment_method: string,
     * } $data
     */
    public function checkout(array $data, User $cashier): Sale
    {
        return DB::transaction(function () use ($data, $cashier) {
            // Hitung subtotal dari items
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $lineTotal = ($item['unit_price_amount'] * $item['qty']) - ($item['discount_amount'] ?? 0);
                $subtotal += $lineTotal;
            }

            $discountAmount = $data['discount_amount'] ?? 0;
            $totalAmount = $subtotal - $discountAmount;
            $paidAmount = $data['paid_amount'];
            $changeAmount = max(0, $paidAmount - $totalAmount);

            // Buat Sale
            $sale = Sale::create([
                'outlet_id' => $data['outlet_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'code' => 'SALE-'.strtoupper(uniqid()),
                'status' => SaleStatus::Completed,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $data['payment_method'],
                'sold_by' => $cashier->id,
                'sold_at' => now(),
            ]);

            // Buat SaleItem + kurangi stok
            foreach ($data['items'] as $item) {
                $lineTotal = ($item['unit_price_amount'] * $item['qty']) - ($item['discount_amount'] ?? 0);

                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price_amount' => $item['unit_price_amount'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total_amount' => $lineTotal,
                ]);

                $this->deductStock($sale, $item, $cashier);
            }

            // Catat kas masuk
            DB::table('cash_transactions')->insert([
                'outlet_id' => $sale->outlet_id,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'direction' => 'in',
                'transaction_type' => 'penjualan',
                'amount' => $totalAmount,
                'effective_at' => now(),
                'note' => "Penjualan {$sale->code}",
                'created_by' => $cashier->id,
            ]);

            // Catat ledger
            DB::table('ledger_entries')->insert([
                'outlet_id' => $sale->outlet_id,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'entry_date' => now()->toDateString(),
                'entry_type' => 'penjualan_fisik',
                'account_code' => '4100',
                'amount' => $totalAmount,
                'description' => "Penjualan Fisik {$sale->code}",
                'created_by' => $cashier->id,
            ]);

            return $sale;
        });
    }

    /**
     * Kurangi stok dan catat stock movement.
     */
    protected function deductStock(Sale $sale, array $item, User $cashier): void
    {
        $stock = Stock::where('outlet_id', $sale->outlet_id)
            ->where('product_id', $item['product_id'])
            ->lockForUpdate()
            ->first();

        if (! $stock) {
            throw new Exception("Stok produk ID {$item['product_id']} tidak ditemukan di outlet ini.");
        }

        if ($stock->on_hand_qty < $item['qty']) {
            throw new Exception("Stok produk tidak mencukupi. Tersedia: {$stock->on_hand_qty}, diminta: {$item['qty']}.");
        }

        $beforeQty = $stock->on_hand_qty;
        $afterQty = $beforeQty - $item['qty'];

        $stock->update(['on_hand_qty' => $afterQty]);

        StockMovement::create([
            'outlet_id' => $sale->outlet_id,
            'product_id' => $item['product_id'],
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'movement_type' => 'sale_out',
            'qty' => -$item['qty'],
            'before_qty' => $beforeQty,
            'after_qty' => $afterQty,
            'note' => "Penjualan {$sale->code}",
            'acted_by' => $cashier->id,
            'acted_at' => now(),
        ]);
    }
}
