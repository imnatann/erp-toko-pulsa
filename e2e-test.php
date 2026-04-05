<?php

use App\Enums\CashSessionStatus;
use App\Enums\DigitalTransactionStatus;
use App\Models\CashSession;
use App\Models\Customer;
use App\Models\DigitalService;
use App\Models\DigitalTransaction;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use App\Services\DigitalTransactionService;
use App\Services\SaleService;

echo "Mulai E2E Test...\n";

// Load Users
$kasir = User::where('email', 'kasir@erp.local')->first();
$operator = User::where('email', 'operator@erp.local')->first();
$supervisor = User::where('email', 'supervisor@erp.local')->first();
$owner = User::where('email', 'owner@erp.local')->first();
$outlet = Outlet::where('code', 'OUT-MAIN')->first();
$product = Product::first();
$customer = Customer::firstOrCreate(
    ['phone' => '08123456789'],
    ['outlet_id' => $outlet->id, 'name' => 'Budi Pelanggan']
);
$digitalService = DigitalService::where('code', 'PULSA-10K')->first();

echo "1. Kasir Buka Shift...\n";
$cashSession = CashSession::create([
    'outlet_id' => $outlet->id,
    'opened_by' => $kasir->id,
    'opened_at' => now(),
    'opening_balance_amount' => 500000,
    'status' => CashSessionStatus::Open,
]);
echo '- Shift dibuka. ID: '.$cashSession->id." | Modal Awal: Rp500.000\n\n";

echo "2. Kasir Mencatat Penjualan Fisik Aksesoris...\n";
$saleData = [
    'outlet_id' => $outlet->id,
    'customer_id' => $customer->id,
    'items' => [
        [
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price_amount' => $product->selling_price_amount,
            'discount_amount' => 0,
        ],
    ],
    'discount_amount' => 0,
    'paid_amount' => $product->selling_price_amount, // uang pas
    'payment_method' => 'cash',
];
$sale = app(SaleService::class)->checkout($saleData, $kasir);
echo '- Penjualan Fisik Berhasil. Kode: '.$sale->code.' | Total: '.$sale->total_amount."\n\n";

echo "3. Kasir Membuat Transaksi Digital (Draft)...\n";
$digitalTx = DigitalTransaction::create([
    'outlet_id' => $outlet->id,
    'customer_id' => $customer->id,
    'digital_service_id' => $digitalService->id,
    'code' => 'TRX-E2E-'.rand(1000, 9999),
    'status' => DigitalTransactionStatus::Draft,
    'destination_account' => '08123456789',
    'nominal_amount' => $digitalService->default_nominal_amount,
    'fee_amount' => $digitalService->default_fee_amount,
    'total_amount' => $digitalService->default_nominal_amount + $digitalService->default_fee_amount,
    'cash_effect_amount' => $digitalService->default_nominal_amount + $digitalService->default_fee_amount, // Uang diterima kasir
    'created_by' => $kasir->id,
]);
echo '- Transaksi Draft Dibuat. Kode: '.$digitalTx->code.' | Total: '.$digitalTx->total_amount."\n\n";

echo "4. Operator Mulai Memproses Transaksi Digital...\n";
$txService = app(DigitalTransactionService::class);
$txService->submitTransaction($digitalTx, $operator);
echo '- Status berubah menjadi: '.$digitalTx->status->value."\n";

echo "5. Operator Selesai Memproses...\n";
$txService->markAsPendingValidation($digitalTx, $operator, 'Pulsa berhasil dikirim via aplikasi bank');
echo '- Status berubah menjadi: '.$digitalTx->status->value." (Menunggu Validasi)\n\n";

echo "6. Supervisor Memvalidasi Transaksi Digital...\n";
$txService->validateAsSuccess($digitalTx, $supervisor, 'Sesuai dengan mutasi bank');
echo '- Status berubah menjadi: '.$digitalTx->status->value." (Berhasil)\n\n";

echo "7. Kasir Menutup Shift...\n";
// Saldo akhir harusnya Modal Awal + Penjualan Fisik + Uang Transaksi Digital
$expectedCash = 500000 + $sale->total_amount + $digitalTx->total_amount;
$cashSession->update([
    'status' => CashSessionStatus::Closed,
    'closed_at' => now(),
    'closed_by' => $kasir->id,
    'closing_balance_amount' => $expectedCash,
    'closing_note' => 'Shift ditutup aman, saldo sesuai',
]);
echo '- Shift ditutup. Saldo Fisik: Rp'.number_format($expectedCash)."\n\n";

echo "8. Owner Mengecek Ledger (Buku Besar)...\n";
$ledgerEntries = DB::table('ledger_entries')->whereDate('entry_date', now()->toDateString())->get();
foreach ($ledgerEntries as $entry) {
    echo '- '.$entry->entry_type.' | Rp'.number_format($entry->amount).' | '.$entry->description."\n";
}

echo "\nE2E Test Selesai. Semua role berhasil menjalankan tugasnya!";
