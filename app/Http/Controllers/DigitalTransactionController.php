<?php

namespace App\Http\Controllers;

use App\Actions\DigitalTransactions\CreateDigitalTransactionAction;
use App\Actions\DigitalTransactions\AssignDigitalTransactionAction;
use App\Actions\DigitalTransactions\TransitionDigitalTransactionStatusAction;
use App\Enums\DigitalTransactionStatus;
use App\Models\DigitalService;
use App\Models\DigitalTransaction;
use App\Models\ManualChannel;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class DigitalTransactionController extends Controller
{
    public function queue(Request $request): View
    {
        Gate::authorize('viewAny', DigitalTransaction::class);

        $user = $request->user();
        $sla = $request->string('sla')->toString();
        $status = $request->string('status')->toString();
        $outletId = $request->integer('outlet_id');
        $baseQuery = DigitalTransaction::query()
            ->with(['digitalService.serviceCategory', 'outlet', 'creator', 'assignee'])
            ->whereIn('status', [DigitalTransactionStatus::InProgress, DigitalTransactionStatus::PendingValidation])
            ->when(
                ! $user->hasAnyRole(['owner', 'admin', 'supervisor']),
                fn ($query) => $query->where('outlet_id', $user->outlet_id),
            )
            ->when(
                $user->hasAnyRole(['owner', 'admin', 'supervisor']) && $outletId > 0,
                fn ($query) => $query->where('outlet_id', $outletId),
            )
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($sla !== '', fn ($query) => $this->applySlaFilter($query, $sla))
            ->orderByDesc('submitted_at');

        $transactions = (clone $baseQuery)
            ->paginate(20)
            ->withQueryString();

        $quickTransitions = [];

        foreach ($transactions as $transaction) {
            $quickTransitions[$transaction->id] = $this->availableTransitions($transaction, $request);
        }

        return view('digital-transactions.queue', [
            'transactions' => $transactions,
            'sla' => $sla,
            'status' => $status,
            'outletId' => $outletId,
            'outlets' => $user->hasAnyRole(['owner', 'admin', 'supervisor'])
                ? Outlet::query()->orderBy('name')->get()
                : collect(),
            'queueCounts' => [
                'all' => $this->queueCountFor($user, null, $outletId),
                '10' => $this->queueCountFor($user, '10', $outletId),
                '30' => $this->queueCountFor($user, '30', $outletId),
                '60' => $this->queueCountFor($user, '60', $outletId),
            ],
            'quickTransitions' => $quickTransitions,
            'assignees' => User::query()
                ->whereIn('role', ['operator', 'supervisor'])
                ->where('is_active', true)
                ->when(
                    ! $user->hasAnyRole(['owner', 'admin', 'supervisor']),
                    fn ($query) => $query->where('outlet_id', $user->outlet_id),
                )
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', DigitalTransaction::class);

        $status = $request->string('status')->toString();
        $user = $request->user();

        $transactions = DigitalTransaction::query()
            ->with(['digitalService.serviceCategory', 'outlet', 'creator', 'validator'])
            ->when(
                ! $user->hasAnyRole(['owner', 'admin', 'supervisor']),
                fn ($query) => $query->where('outlet_id', $user->outlet_id),
            )
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('digital-transactions.index', [
            'transactions' => $transactions,
            'statuses' => DigitalTransactionStatus::cases(),
            'currentStatus' => $status,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', DigitalTransaction::class);

        $user = request()->user();

        return view('digital-transactions.create', [
            'outlets' => $user->hasAnyRole(['owner', 'admin', 'supervisor'])
                ? Outlet::query()->orderBy('name')->get()
                : Outlet::query()->whereKey($user->outlet_id)->get(),
            'services' => DigitalService::query()->with('serviceCategory')->where('is_active', true)->orderBy('name')->get(),
            'manualChannels' => ManualChannel::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, CreateDigitalTransactionAction $action): RedirectResponse
    {
        Gate::authorize('create', DigitalTransaction::class);

        $validated = $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'digital_service_id' => ['required', 'exists:digital_services,id'],
            'manual_channel_id' => ['nullable', 'exists:manual_channels,id'],
            'destination_account' => ['required', 'string', 'max:255'],
            'destination_name' => ['nullable', 'string', 'max:255'],
            'nominal_amount' => ['required', 'integer', 'min:1'],
            'fee_amount' => ['nullable', 'integer', 'min:0'],
            'operator_note' => ['nullable', 'string'],
        ]);

        $actor = $request->user();

        if (! $actor->hasAnyRole(['owner', 'admin', 'supervisor'])) {
            $validated['outlet_id'] = $actor->outlet_id;
        }

        $transaction = $action->execute($validated, $actor);

        return redirect()
            ->route('digital-transactions.show', $transaction)
            ->with('status', 'Tiket transaksi digital berhasil dibuat.');
    }

    public function show(DigitalTransaction $digitalTransaction): View
    {
        Gate::authorize('view', $digitalTransaction);

        $digitalTransaction->load([
            'outlet',
            'digitalService.serviceCategory',
            'manualChannel',
            'creator',
            'processor',
            'validator',
            'supervisorApprover',
            'statusLogs.actor',
        ]);

        return view('digital-transactions.show', [
            'transaction' => $digitalTransaction,
            'availableTransitions' => $this->availableTransitions($digitalTransaction, request()),
        ]);
    }

    public function transition(
        Request $request,
        DigitalTransaction $digitalTransaction,
        TransitionDigitalTransactionStatusAction $action,
    ): RedirectResponse {
        Gate::authorize('view', $digitalTransaction);

        $validated = $request->validate([
            'target_status' => ['required', Rule::in(array_map(fn (DigitalTransactionStatus $status) => $status->value, DigitalTransactionStatus::cases()))],
            'note' => ['nullable', 'string'],
            'external_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $actor = $request->user();

        $action->execute(
            $digitalTransaction,
            DigitalTransactionStatus::from($validated['target_status']),
            $actor,
            [
                'note' => $validated['note'] ?? null,
                'external_reference' => $validated['external_reference'] ?? null,
            ],
        );

        return redirect()
            ->route('digital-transactions.show', $digitalTransaction)
            ->with('status', 'Status transaksi berhasil diperbarui.');
    }

    public function quickTransition(
        Request $request,
        DigitalTransaction $digitalTransaction,
        TransitionDigitalTransactionStatusAction $action,
    ): RedirectResponse {
        Gate::authorize('view', $digitalTransaction);

        $validated = $request->validate([
            'target_status' => ['required', Rule::in(array_map(fn (DigitalTransactionStatus $status) => $status->value, DigitalTransactionStatus::cases()))],
            'note' => ['nullable', 'string'],
            'external_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $action->execute(
            $digitalTransaction,
            DigitalTransactionStatus::from($validated['target_status']),
            $request->user(),
            [
                'note' => $validated['note'] ?? null,
                'external_reference' => $validated['external_reference'] ?? null,
            ],
        );

        return redirect()
            ->route('digital-transactions.queue', $request->only(['sla', 'status', 'outlet_id']))
            ->with('status', 'Aksi cepat queue berhasil dijalankan.');
    }

    public function assign(
        Request $request,
        DigitalTransaction $digitalTransaction,
        AssignDigitalTransactionAction $action,
    ): RedirectResponse {
        Gate::authorize('view', $digitalTransaction);

        $validated = $request->validate([
            'assignee_id' => ['required', 'exists:users,id'],
        ]);

        $assignee = User::query()->findOrFail($validated['assignee_id']);
        $action->execute($digitalTransaction, $request->user(), $assignee);

        return redirect()
            ->route('digital-transactions.queue', $request->only(['sla', 'status', 'outlet_id']))
            ->with('status', 'Assignee queue berhasil diperbarui.');
    }

    /**
     * @return array<int, DigitalTransactionStatus>
     */
    private function availableTransitions(DigitalTransaction $transaction, Request $request): array
    {
        $candidates = match ($transaction->status) {
            DigitalTransactionStatus::Draft => [DigitalTransactionStatus::InProgress, DigitalTransactionStatus::Cancelled],
            DigitalTransactionStatus::InProgress => [DigitalTransactionStatus::PendingValidation],
            DigitalTransactionStatus::PendingValidation => [DigitalTransactionStatus::Succeeded, DigitalTransactionStatus::Failed],
            DigitalTransactionStatus::Failed => [DigitalTransactionStatus::ManualRefund],
            default => [],
        };

        return array_values(array_filter(
            $candidates,
            fn (DigitalTransactionStatus $status) => Gate::forUser($request->user())->allows($this->abilityForStatus($status), $transaction),
        ));
    }

    private function abilityForStatus(DigitalTransactionStatus $status): string
    {
        return match ($status) {
            DigitalTransactionStatus::InProgress => 'markInProgress',
            DigitalTransactionStatus::PendingValidation => 'markPendingValidation',
            DigitalTransactionStatus::Succeeded => 'markSucceeded',
            DigitalTransactionStatus::Failed => 'markFailed',
            DigitalTransactionStatus::Cancelled => 'cancel',
            DigitalTransactionStatus::ManualRefund => 'refundManual',
            default => 'view',
        };
    }

    private function applySlaFilter($query, string $sla)
    {
        $minutes = (int) $sla;

        if ($minutes <= 0) {
            return $query;
        }

        return $query->where('submitted_at', '<=', now()->subMinutes($minutes));
    }

    private function queueCountFor($user, ?string $sla = null, int $outletId = 0): int
    {
        $query = DigitalTransaction::query()
            ->whereIn('status', [DigitalTransactionStatus::InProgress, DigitalTransactionStatus::PendingValidation])
            ->when(
                ! $user->hasAnyRole(['owner', 'admin', 'supervisor']),
                fn ($builder) => $builder->where('outlet_id', $user->outlet_id),
            )
            ->when(
                $user->hasAnyRole(['owner', 'admin', 'supervisor']) && $outletId > 0,
                fn ($builder) => $builder->where('outlet_id', $outletId),
            );

        if ($sla !== null) {
            $this->applySlaFilter($query, $sla);
        }

        return $query->count();
    }
}
