<?php

namespace App\Http\Controllers;

use App\Actions\CashSessions\CloseCashSessionAction;
use App\Actions\CashSessions\OpenCashSessionAction;
use App\Models\CashSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CashSessionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', CashSession::class);

        $user = $request->user();

        $sessions = CashSession::query()
            ->with(['outlet', 'opener', 'closer'])
            ->when(
                ! $user->hasAnyRole(['owner', 'admin', 'supervisor']),
                fn ($query) => $query->where('outlet_id', $user->outlet_id),
            )
            ->latest('opened_at')
            ->paginate(15);

        $activeSession = CashSession::query()
            ->where('outlet_id', $user->outlet_id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        return view('cash-sessions.index', [
            'sessions' => $sessions,
            'activeSession' => $activeSession,
        ]);
    }

    public function store(Request $request, OpenCashSessionAction $action): RedirectResponse
    {
        Gate::authorize('open', CashSession::class);

        $validated = $request->validate([
            'opening_balance_amount' => ['required', 'integer', 'min:0'],
        ]);

        $action->execute($request->user(), (int) $validated['opening_balance_amount']);

        return redirect()->route('cash-sessions.index')->with('status', 'Sesi kas berhasil dibuka.');
    }

    public function close(Request $request, CashSession $cashSession, CloseCashSessionAction $action): RedirectResponse
    {
        Gate::authorize('close', $cashSession);

        $validated = $request->validate([
            'closing_balance_amount' => ['required', 'integer', 'min:0'],
            'closing_note' => ['nullable', 'string'],
        ]);

        $action->execute(
            $cashSession,
            $request->user(),
            (int) $validated['closing_balance_amount'],
            $validated['closing_note'] ?? null,
        );

        return redirect()->route('cash-sessions.index')->with('status', 'Sesi kas berhasil ditutup.');
    }
}
