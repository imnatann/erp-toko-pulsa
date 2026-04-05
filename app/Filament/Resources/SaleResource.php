<?php

namespace App\Filament\Resources;

use App\Enums\SaleStatus;
use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Traits\IsolatesOutletData;
use App\Models\Product;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    use IsolatesOutletData;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Penjualan Fisik';

    protected static ?string $pluralLabel = 'Penjualan Fisik';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([

                    Forms\Components\Section::make('Informasi Penjualan')
                        ->schema([
                            Forms\Components\Select::make('outlet_id')
                                ->label('Outlet')
                                ->relationship('outlet', 'name')
                                ->required()
                                ->default(fn () => auth()->user()->outlet_id ?? null)
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('customer_id')
                                ->label('Pelanggan (opsional)')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                    Forms\Components\TextInput::make('phone')->tel(),
                                ]),
                            Forms\Components\Select::make('payment_method')
                                ->label('Metode Bayar')
                                ->options([
                                    'cash' => 'Tunai',
                                    'transfer' => 'Transfer',
                                    'qris' => 'QRIS',
                                ])
                                ->required()
                                ->default('cash'),
                        ])->columns(3),

                    Forms\Components\Section::make('Item Barang')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->label('')
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Produk')
                                        ->options(
                                            fn () => Product::where('is_active', true)
                                                ->pluck('name', 'id')
                                        )
                                        ->required()
                                        ->searchable()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if ($state) {
                                                $product = Product::find($state);
                                                if ($product) {
                                                    $set('unit_price_amount', $product->selling_price_amount);
                                                }
                                            }
                                        })
                                        ->columnSpan(4),
                                    Forms\Components\TextInput::make('qty')
                                        ->label('Qty')
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->minValue(1)
                                        ->reactive()
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('unit_price_amount')
                                        ->label('Harga Satuan')
                                        ->numeric()
                                        ->required()
                                        ->prefix('Rp')
                                        ->reactive()
                                        ->columnSpan(2),
                                    Forms\Components\TextInput::make('discount_amount')
                                        ->label('Diskon')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->columnSpan(2),
                                    Forms\Components\Placeholder::make('line_total')
                                        ->label('Subtotal')
                                        ->content(function (Get $get): string {
                                            $qty = (int) ($get('qty') ?? 1);
                                            $price = (int) ($get('unit_price_amount') ?? 0);
                                            $discount = (int) ($get('discount_amount') ?? 0);
                                            $total = ($price * $qty) - $discount;

                                            return 'Rp '.number_format(max(0, $total), 0, ',', '.');
                                        })
                                        ->columnSpan(3),
                                ])
                                ->columns(12)
                                ->defaultItems(1)
                                ->addActionLabel('+ Tambah Item')
                                ->reorderable()
                                ->cloneable(),
                        ]),

                ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()->schema([

                    Forms\Components\Section::make('Ringkasan Pembayaran')
                        ->schema([
                            Forms\Components\TextInput::make('discount_amount')
                                ->label('Diskon Total')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),
                            Forms\Components\TextInput::make('paid_amount')
                                ->label('Uang Diterima')
                                ->numeric()
                                ->required()
                                ->default(0)
                                ->prefix('Rp'),
                            Forms\Components\Placeholder::make('kembalian_info')
                                ->label('Info')
                                ->content('Kembalian dihitung otomatis saat simpan.'),
                        ]),

                ])->columnSpan(['lg' => 1]),

                Forms\Components\Hidden::make('sold_by')
                    ->default(fn () => auth()->id()),

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sold_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Bayar')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'qris' => 'QRIS',
                        default => ucfirst($state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'cash' => 'success',
                        'transfer' => 'info',
                        'qris' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->defaultSort('sold_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(SaleStatus::class),
                Tables\Filters\SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name'),
                Tables\Filters\Filter::make('sold_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($q, $v) => $q->whereDate('sold_at', '>=', $v))
                        ->when($data['until'], fn ($q, $v) => $q->whereDate('sold_at', '<=', $v))
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('cetak_struk')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Sale $record) => route('print.sale', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
        ];
    }
}
