<?php

namespace App\Actions\DigitalTransactions;

use App\Enums\DigitalServiceType;
use App\Enums\DigitalTransactionStatus;
use App\Models\DigitalService;
use App\Models\DigitalTransaction;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateDigitalTransactionAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes, User $actor): DigitalTransaction
    {
        /** @var DigitalService $service */
        $service = Arr::get($attributes, 'digital_service')
            ?? DigitalService::query()->findOrFail($attributes['digital_service_id']);

        $nominalAmount = (int) $attributes['nominal_amount'];
        $feeAmount = (int) ($attributes['fee_amount'] ?? $service->default_fee_amount ?? 0);
        $serviceType = $service->serviceCategory->service_type ?? DigitalServiceType::Pulsa;
        $cashEffectAmount = ($nominalAmount + $feeAmount) * $serviceType->defaultCashEffectSign();

        return DigitalTransaction::query()->create([
            'outlet_id' => $attributes['outlet_id'] ?? $actor->outlet_id,
            'customer_id' => $attributes['customer_id'] ?? null,
            'digital_service_id' => $service->getKey(),
            'manual_channel_id' => $attributes['manual_channel_id'] ?? null,
            'code' => $attributes['code'] ?? $this->generateCode(),
            'status' => DigitalTransactionStatus::Draft,
            'destination_account' => $attributes['destination_account'],
            'destination_name' => $attributes['destination_name'] ?? null,
            'nominal_amount' => $nominalAmount,
            'fee_amount' => $feeAmount,
            'total_amount' => $nominalAmount + $feeAmount,
            'cash_effect_amount' => $attributes['cash_effect_amount'] ?? $cashEffectAmount,
            'submitted_at' => now(),
            'created_by' => $actor->getKey(),
            'operator_note' => $attributes['operator_note'] ?? null,
            'requires_supervisor_approval' => $attributes['requires_supervisor_approval']
                ?? $nominalAmount >= config('erp.digital_transactions.supervisor_approval_threshold_amount'),
        ]);
    }

    private function generateCode(): string
    {
        return 'DT-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
