<?php

namespace App\Filament\Widgets;

use App\Enums\DigitalTransactionStatus;
use App\Models\DigitalTransaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TransaksiPendingWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Transaksi Digital Menunggu Tindakan';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DigitalTransaction::query()
                    ->whereIn('status', [
                        DigitalTransactionStatus::Draft,
                        DigitalTransactionStatus::InProgress,
                        DigitalTransactionStatus::PendingValidation,
                    ])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->weight('bold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('digitalService.name')
                    ->label('Layanan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('destination_account')
                    ->label('Tujuan')
                    ->copyable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M H:i')
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (DigitalTransaction $record) => route('filament.admin.resources.digital-transactions.edit', $record))
                    ->openUrlInNewTab(),
            ]);
    }
}
