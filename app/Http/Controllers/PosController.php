<?php

namespace App\Http\Controllers;

use App\Actions\Sales\CreateSaleAction;
use App\Enums\CashSessionStatus;
use App\Models\CashSession;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('create', Sale::class);

        $user = $request->user();
        $activeSession = CashSession::query()
            ->where('outlet_id', $user->outlet_id)
            ->where('status', CashSessionStatus::Open)
            ->latest('opened_at')
            ->first();

        $products = Product::query()
            ->select('products.*', 'stocks.on_hand_qty')
            ->join('stocks', function ($join) use ($user) {
                $join->on('stocks.product_id', '=', 'products.id')
                    ->where('stocks.outlet_id', '=', $user->outlet_id);
            })
            ->where('products.is_active', true)
            ->orderBy('products.name')
            ->get();

        $recentSales = Sale::query()
            ->with('items.product')
            ->where('outlet_id', $user->outlet_id)
            ->latest('sold_at')
            ->limit(10)
            ->get();

        return view('pos.index', [
            'activeSession' => $activeSession,
            'products' => $products,
            'recentSales' => $recentSales,
        ]);
    }

    public function store(Request $request, CreateSaleAction $action): RedirectResponse
    {
        Gate::authorize('create', Sale::class);

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:50'],
            'paid_amount' => ['required', 'integer', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:0'],
        ]);

        $items = array_values(array_filter(
            $validated['items'],
            fn (array $item) => (int) $item['qty'] > 0,
        ));

        $sale = $action->execute(
            $request->user(),
            $items,
            $validated['payment_method'],
            (int) $validated['paid_amount'],
        );

        return redirect()->route('pos.index')->with('status', 'Checkout berhasil untuk '.$sale->code.'.');
    }
}
