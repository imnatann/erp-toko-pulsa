<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Sesuaikan stok (tambah/kurang) dengan mencatat mutasi.
     *
     * @param  int  $qty  Perubahan stok (positif untuk masuk, negatif untuk keluar)
     * @param  string  $movementType  Tipe mutasi (cth: restock, correction_in, correction_out, return)
     * @param  User  $actor  User yang melakukan aksi
     * @param  string|null  $note  Catatan opsional
     */
    public function adjustStock(Stock $stock, int $qty, string $movementType, User $actor, ?string $note = null): Stock
    {
        if ($qty === 0) {
            throw new Exception('Kuantitas penyesuaian tidak boleh 0.');
        }

        return DB::transaction(function () use ($stock, $qty, $movementType, $actor, $note) {
            // Lock row
            $stock = Stock::where('id', $stock->id)->lockForUpdate()->first();

            $beforeQty = $stock->on_hand_qty;
            $afterQty = $beforeQty + $qty;

            if ($afterQty < 0) {
                throw new Exception("Stok tidak mencukupi. Stok saat ini: {$beforeQty}, penyesuaian: {$qty}.");
            }

            // Update stok
            $stock->update([
                'on_hand_qty' => $afterQty,
            ]);

            // Catat mutasi
            StockMovement::create([
                'outlet_id' => $stock->outlet_id,
                'product_id' => $stock->product_id,
                'reference_type' => Stock::class,
                'reference_id' => $stock->id,
                'movement_type' => $movementType,
                'qty' => $qty,
                'before_qty' => $beforeQty,
                'after_qty' => $afterQty,
                'note' => $note,
                'acted_by' => $actor->id,
                'acted_at' => now(),
            ]);

            return $stock;
        });
    }

    /**
     * Inisialisasi stok baru untuk produk di outlet tertentu.
     */
    public function initializeStock(int $outletId, int $productId, int $initialQty, User $actor): Stock
    {
        return DB::transaction(function () use ($outletId, $productId, $initialQty, $actor) {
            $stock = Stock::firstOrCreate(
                ['outlet_id' => $outletId, 'product_id' => $productId],
                ['on_hand_qty' => 0, 'reserved_qty' => 0, 'minimum_stock' => 0]
            );

            if ($initialQty > 0) {
                $this->adjustStock($stock, $initialQty, 'initial_stock', $actor, 'Inisialisasi stok awal');
            }

            return $stock;
        });
    }
}
