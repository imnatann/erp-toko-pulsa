<?php

namespace App\Http\Controllers;

use App\Enums\DigitalTransactionStatus;
use App\Models\DigitalTransaction;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\CashSession;
use Illuminate\Contracts\View\View;

class OperationalDashboardController extends Controller
{
    public function __invoke(): View
    {
        $statusCounts = collect(DigitalTransactionStatus::cases())
            ->mapWithKeys(fn (DigitalTransactionStatus $status) => [
                $status->value => DigitalTransaction::query()->where('status', $status)->count(),
            ]);

        $pendingTransactions = DigitalTransaction::query()
            ->with(['digitalService', 'outlet', 'assignee'])
            ->whereIn('status', [DigitalTransactionStatus::InProgress, DigitalTransactionStatus::PendingValidation])
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        $escalatedPendingCount = DigitalTransaction::query()
            ->whereIn('status', [DigitalTransactionStatus::InProgress, DigitalTransactionStatus::PendingValidation])
            ->where('submitted_at', '<=', now()->subMinutes(30))
            ->count();

        $lowStockProducts = Product::query()
            ->select('products.*')
            ->join('stocks', 'stocks.product_id', '=', 'products.id')
            ->whereColumn('stocks.on_hand_qty', '<=', 'stocks.minimum_stock')
            ->orderBy('stocks.on_hand_qty')
            ->limit(10)
            ->get();

        return view('dashboard.operational', [
            'outletCount' => Outlet::query()->count(),
            'statusCounts' => $statusCounts,
            'pendingTransactions' => $pendingTransactions,
            'lowStockProducts' => $lowStockProducts,
            'openCashSessionsCount' => CashSession::query()->where('status', 'open')->count(),
            'escalatedPendingCount' => $escalatedPendingCount,
        ]);
    }
}
