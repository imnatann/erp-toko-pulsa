<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Detail Penjualan')
                ->schema([
                    TextEntry::make('code')->label('Kode')->copyable()->weight('bold'),
                    TextEntry::make('status')->label('Status')->badge(),
                    TextEntry::make('sold_at')->label('Waktu')->dateTime('d M Y H:i'),
                    TextEntry::make('outlet.name')->label('Outlet'),
                    TextEntry::make('cashier.name')->label('Kasir'),
                    TextEntry::make('customer.name')->label('Pelanggan')->default('-'),
                ])->columns(3),

            Section::make('Item Barang')
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            TextEntry::make('product.name')->label('Produk'),
                            TextEntry::make('qty')->label('Qty'),
                            TextEntry::make('unit_price_amount')
                                ->label('Harga Satuan')
                                ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.')),
                            TextEntry::make('discount_amount')
                                ->label('Diskon')
                                ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.')),
                            TextEntry::make('line_total_amount')
                                ->label('Subtotal')
                                ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                                ->weight('bold'),
                        ])->columns(5),
                ]),

            Section::make('Pembayaran')
                ->schema([
                    TextEntry::make('subtotal_amount')
                        ->label('Subtotal')
                        ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.')),
                    TextEntry::make('discount_amount')
                        ->label('Diskon')
                        ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.')),
                    TextEntry::make('total_amount')
                        ->label('Total')
                        ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                        ->weight('bold')
                        ->size('lg'),
                    TextEntry::make('paid_amount')
                        ->label('Dibayar')
                        ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.')),
                    TextEntry::make('change_amount')
                        ->label('Kembalian')
                        ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.')),
                    TextEntry::make('payment_method')
                        ->label('Metode')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'cash' => 'Tunai',
                            'transfer' => 'Transfer',
                            'qris' => 'QRIS',
                            default => ucfirst($state),
                        }),
                ])->columns(3),
        ]);
    }
}
