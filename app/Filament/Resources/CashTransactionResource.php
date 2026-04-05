<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashTransactionResource\Pages;
use App\Models\CashTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CashTransactionResource extends Resource
{
    protected static ?string $model = CashTransaction::class;
    use \App\Filament\Traits\IsolatesOutletData;


    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Kas';

    protected static ?string $pluralLabel = 'Transaksi Kas';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kas')
                    ->schema([
                        Forms\Components\Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('direction')
                            ->label('Arah')
                            ->options([
                                'in' => 'Masuk',
                                'out' => 'Keluar',
                            ])
                            ->required(),
                        Forms\Components\Select::make('transaction_type')
                            ->label('Tipe Transaksi')
                            ->options([
                                'penjualan' => 'Penjualan',
                                'transaksi_digital' => 'Transaksi Digital',
                                'pengeluaran' => 'Pengeluaran',
                                'lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\DateTimePicker::make('effective_at')
                            ->label('Waktu Efektif')
                            ->required()
                            ->default(now()),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('effective_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'penjualan' => 'Penjualan',
                        'transaksi_digital' => 'Transaksi Digital',
                        'pengeluaran' => 'Pengeluaran',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('direction')
                    ->label('Arah')
                    ->badge()
                    ->color(fn ($state) => $state === 'in' ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state === 'in' ? 'Masuk' : 'Keluar'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('effective_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->label('Arah')
                    ->options([
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                    ]),
                Tables\Filters\SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name'),
            ])
            ->actions([
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
            'index' => Pages\ListCashTransactions::route('/'),
        ];
    }
}
