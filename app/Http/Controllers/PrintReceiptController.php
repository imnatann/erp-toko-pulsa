<?php

namespace App\Http\Controllers;

use App\Models\DigitalTransaction;
use App\Models\Sale;

class PrintReceiptController extends Controller
{
    public function sale(Sale $sale)
    {
        $sale->load(['items.product', 'outlet', 'cashier', 'customer']);

        return view('receipts.sale', compact('sale'));
    }

    public function digital(DigitalTransaction $transaction)
    {
        $transaction->load(['outlet', 'digitalService', 'creator', 'processor', 'customer']);

        return view('receipts.digital', compact('transaction'));
    }
}
