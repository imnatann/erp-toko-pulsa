<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    use \App\Filament\Traits\IsolatesOutletData;


    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Mutasi Stok';

    protected static ?string $pluralLabel = 'Riwayat Mutasi Stok';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([]); // Read-only
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('acted_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('movement_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'sale_out' => 'Terjual',
                        'restock' => 'Restock',
                        'correction_in' => 'Koreksi (+)',
                        'correction_out' => 'Koreksi (-)',
                        'damage' => 'Rusak/Hilang',
                        'initial_stock' => 'Stok Awal',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'sale_out', 'correction_out', 'damage' => 'danger',
                        'restock', 'correction_in', 'initial_stock' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.'))
                    ->weight('bold')
                    ->color(fn (StockMovement $record) => $record->qty > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('after_qty')
                    ->label('Sisa')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.')),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Oleh')
                    ->searchable(),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
            ])
            ->defaultSort('acted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('movement_type')
                    ->label('Tipe Mutasi')
                    ->options([
                        'sale_out' => 'Terjual',
                        'restock' => 'Restock',
                        'correction_in' => 'Koreksi (+)',
                        'correction_out' => 'Koreksi (-)',
                        'damage' => 'Rusak/Hilang',
                    ]),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
        ];
    }
}
