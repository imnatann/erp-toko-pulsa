<?php

namespace App\Actions\Sales;

use App\Enums\CashSessionStatus;
use App\Enums\SaleStatus;
use App\Models\AuditLog;
use App\Models\CashSession;
use App\Models\CashTransaction;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateSaleAction
{
    /**
     * @param  array<int, array{product_id:int, qty:int}>  $items
     */
    public function execute(User $actor, array $items, string $paymentMethod, int $paidAmount): Sale
    {
        $cashSession = CashSession::query()
            ->where('outlet_id', $actor->outlet_id)
            ->where('status', CashSessionStatus::Open)
            ->latest('opened_at')
            ->first();

        if (! $cashSession) {
            throw ValidationException::withMessages([
                'cash_session' => ['Buka sesi kas terlebih dahulu sebelum checkout barang.'],
            ]);
        }

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => ['Minimal satu produk harus dipilih.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $items, $paymentMethod, $paidAmount, $cashSession) {
            $resolvedItems = [];
            $subtotal = 0;

            foreach ($items as $item) {
                $product = Product::query()->findOrFail($item['product_id']);
                $qty = (int) $item['qty'];
                $stock = Stock::query()
                    ->where('outlet_id', $actor->outlet_id)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock || $stock->on_hand_qty < $qty) {
                    throw ValidationException::withMessages([
                        'items' => [sprintf('Stok %s tidak mencukupi.', $product->name)],
                    ]);
                }

                $lineTotal = $product->selling_price_amount * $qty;
                $subtotal += $lineTotal;
                $resolvedItems[] = compact('product', 'qty', 'stock', 'lineTotal');
            }

            if ($paidAmount < $subtotal) {
                throw ValidationException::withMessages([
                    'paid_amount' => ['Pembayaran kurang dari total penjualan.'],
                ]);
            }

            $sale = Sale::query()->create([
                'outlet_id' => $actor->outlet_id,
                'cash_session_id' => $cashSession->id,
                'code' => $this->generateCode(),
                'status' => SaleStatus::Completed,
                'subtotal_amount' => $subtotal,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $paidAmount - $subtotal,
                'payment_method' => $paymentMethod,
                'sold_by' => $actor->id,
                'sold_at' => now(),
            ]);

            foreach ($resolvedItems as $resolvedItem) {
                /** @var Product $product */
                $product = $resolvedItem['product'];
                /** @var Stock $stock */
                $stock = $resolvedItem['stock'];
                $qty = $resolvedItem['qty'];
                $lineTotal = $resolvedItem['lineTotal'];
                $beforeQty = $stock->on_hand_qty;
                $afterQty = $beforeQty - $qty;

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_price_amount' => $product->selling_price_amount,
                    'discount_amount' => 0,
                    'line_total_amount' => $lineTotal,
                ]);

                $stock->update(['on_hand_qty' => $afterQty]);

                StockMovement::query()->create([
                    'outlet_id' => $actor->outlet_id,
                    'product_id' => $product->id,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'movement_type' => 'sale',
                    'qty' => -$qty,
                    'before_qty' => $beforeQty,
                    'after_qty' => $afterQty,
                    'note' => 'Penjualan barang fisik '.$sale->code,
                    'acted_by' => $actor->id,
                    'acted_at' => now(),
                ]);
            }

            CashTransaction::query()->create([
                'outlet_id' => $actor->outlet_id,
                'cash_session_id' => $cashSession->id,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'direction' => 'in',
                'transaction_type' => 'sale_checkout',
                'amount' => $sale->total_amount,
                'effective_at' => now(),
                'note' => 'Penjualan barang fisik '.$sale->code,
                'created_by' => $actor->id,
            ]);

            LedgerEntry::query()->create([
                'outlet_id' => $actor->outlet_id,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'entry_date' => now()->toDateString(),
                'entry_type' => 'credit',
                'account_code' => 'POS_SALES_REVENUE',
                'amount' => $sale->total_amount,
                'description' => 'Penjualan barang fisik '.$sale->code,
                'created_by' => $actor->id,
            ]);

            AuditLog::query()->create([
                'outlet_id' => $actor->outlet_id,
                'user_id' => $actor->id,
                'auditable_type' => Sale::class,
                'auditable_id' => $sale->id,
                'event' => 'sale.completed',
                'new_values' => $sale->fresh('items')->toArray(),
                'created_at' => now(),
            ]);

            return $sale->fresh('items.product', 'cashSession');
        });
    }

    private function generateCode(): string
    {
        return 'SALE-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
