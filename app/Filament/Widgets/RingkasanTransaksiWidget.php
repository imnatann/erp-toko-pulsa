<?php

namespace App\Filament\Widgets;

use App\Enums\DigitalTransactionStatus;
use App\Models\DigitalTransaction;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RingkasanTransaksiWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $today = now()->toDateString();

        // Transaksi digital per status hari ini
        $pending = DigitalTransaction::query()
            ->whereIn('status', [
                DigitalTransactionStatus::Draft,
                DigitalTransactionStatus::InProgress,
                DigitalTransactionStatus::PendingValidation,
            ])
            ->count();

        $pendingValidasi = DigitalTransaction::query()
            ->where('status', DigitalTransactionStatus::PendingValidation)
            ->count();

        // Fee admin terkumpul hari ini (dari transaksi berhasil)
        $feeHariIni = DigitalTransaction::query()
            ->where('status', DigitalTransactionStatus::Succeeded)
            ->whereDate('validated_at', $today)
            ->sum('fee_amount');

        // Penjualan fisik hari ini
        $penjualanHariIni = Sale::query()
            ->whereDate('sold_at', $today)
            ->count();

        $omzetFisikHariIni = Sale::query()
            ->whereDate('sold_at', $today)
            ->sum('total_amount');

        return [
            Stat::make('Transaksi Perlu Tindakan', $pending)
                ->description($pendingValidasi.' menunggu validasi sekarang')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingValidasi > 0 ? 'warning' : 'gray'),

            Stat::make('Fee Admin Hari Ini', 'Rp '.number_format($feeHariIni, 0, ',', '.'))
                ->description('Dari transaksi digital berhasil')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Penjualan Fisik Hari Ini', 'Rp '.number_format($omzetFisikHariIni, 0, ',', '.'))
                ->description($penjualanHariIni.' transaksi POS')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
        ];
    }
}
