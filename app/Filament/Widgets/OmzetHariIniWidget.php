<?php

namespace App\Filament\Widgets;

use App\Enums\DigitalTransactionStatus;
use App\Models\CashTransaction;
use App\Models\DigitalTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OmzetHariIniWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->toDateString();

        // Omzet transaksi digital berhasil hari ini
        $omzetDigital = DigitalTransaction::query()
            ->where('status', DigitalTransactionStatus::Succeeded)
            ->whereDate('validated_at', $today)
            ->sum('total_amount');

        // Kas masuk hari ini
        $kasmasuk = CashTransaction::query()
            ->where('direction', 'in')
            ->whereDate('effective_at', $today)
            ->sum('amount');

        // Kas keluar hari ini
        $kasKeluar = CashTransaction::query()
            ->where('direction', 'out')
            ->whereDate('effective_at', $today)
            ->sum('amount');

        // Jumlah transaksi digital hari ini
        $jumlahTrx = DigitalTransaction::query()
            ->whereDate('created_at', $today)
            ->count();

        return [
            Stat::make('Omzet Digital Hari Ini', 'Rp '.number_format($omzetDigital, 0, ',', '.'))
                ->description($jumlahTrx.' transaksi digital dibuat')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Kas Masuk Hari Ini', 'Rp '.number_format($kasmasuk, 0, ',', '.'))
                ->description('Total penerimaan kas')
                ->descriptionIcon('heroicon-m-arrow-down-circle')
                ->color('info'),

            Stat::make('Kas Keluar Hari Ini', 'Rp '.number_format($kasKeluar, 0, ',', '.'))
                ->description('Total pengeluaran kas')
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color('warning'),
        ];
    }
}
