<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Models\Stock;
use App\Services\StockService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;
    use \App\Filament\Traits\IsolatesOutletData;


    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Stok Barang';

    protected static ?string $pluralLabel = 'Stok Barang';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name')
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->required()
                    ->disabledOn('edit')
                    ->searchable(),
                Forms\Components\TextInput::make('on_hand_qty')
                    ->label('Stok Tersedia')
                    ->required()
                    ->numeric()
                    ->disabledOn('edit') // Di-disable karena update harus via Action mutasi
                    ->default(0),
                Forms\Components\TextInput::make('minimum_stock')
                    ->label('Batas Minimum Stok')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('on_hand_qty')
                    ->label('Tersedia')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->weight('bold')
                    ->color(fn (Stock $record) => $record->on_hand_qty <= $record->minimum_stock ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Min. Stok')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name'),
                Tables\Filters\TernaryFilter::make('low_stock')
                    ->label('Status Stok')
                    ->placeholder('Semua')
                    ->trueLabel('Stok Menipis / Habis')
                    ->falseLabel('Stok Aman')
                    ->queries(
                        true: fn (Builder $query) => $query->whereColumn('on_hand_qty', '<=', 'minimum_stock'),
                        false: fn (Builder $query) => $query->whereColumn('on_hand_qty', '>', 'minimum_stock'),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('sesuaikan_stok')
                    ->label('Sesuaikan Stok')
                    ->icon('heroicon-o-arrows-up-down')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('movement_type')
                            ->label('Tipe Penyesuaian')
                            ->options([
                                'restock' => 'Barang Masuk (Restock)',
                                'correction_in' => 'Koreksi Plus (+)',
                                'correction_out' => 'Koreksi Minus (-)',
                                'damage' => 'Barang Rusak/Hilang (-)',
                            ])
                            ->required()
                            ->reactive(),
                        Forms\Components\TextInput::make('qty')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->hint('Masukkan angka positif. Minus/Plus ditentukan oleh Tipe.'),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan (Opsional)')
                            ->rows(2),
                    ])
                    ->action(function (Stock $record, array $data) {
                        $qty = (int) $data['qty'];
                        $isOutgoing = in_array($data['movement_type'], ['correction_out', 'damage']);

                        if ($isOutgoing) {
                            $qty = -$qty;
                        }

                        app(StockService::class)->adjustStock(
                            $record,
                            $qty,
                            $data['movement_type'],
                            auth()->user(),
                            $data['note'] ?? null
                        );
                    }),
                Tables\Actions\EditAction::make()
                    ->label('Edit Min Stok'),
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
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
