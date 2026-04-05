<?php

namespace App\Filament\Resources;

use App\Enums\CashSessionStatus;
use App\Filament\Resources\CashSessionResource\Pages;
use App\Models\CashSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CashSessionResource extends Resource
{
    protected static ?string $model = CashSession::class;
    use \App\Filament\Traits\IsolatesOutletData;


    protected static ?string $navigationIcon = 'heroicon-o-currency-bangladeshi';

    protected static ?string $navigationLabel = 'Shift Kasir';

    protected static ?string $pluralLabel = 'Sesi Kas / Shift Kasir';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name')
                    ->required()
                    ->disabledOn('edit')
                    ->default(fn () => auth()->user()->outlet_id ?? null),
                Forms\Components\TextInput::make('opening_balance_amount')
                    ->label('Saldo Awal (Modal Kasir)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->disabledOn('edit')
                    ->default(0),
                Forms\Components\Hidden::make('opened_by')
                    ->default(fn () => auth()->id()),
                Forms\Components\Hidden::make('status')
                    ->default(CashSessionStatus::Open->value),
                Forms\Components\Hidden::make('opened_at')
                    ->default(fn () => now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('opened_at')
                    ->label('Waktu Buka')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('opener.name')
                    ->label('Dibuka Oleh')
                    ->searchable(),
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('opening_balance_amount')
                    ->label('Saldo Awal')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('closing_balance_amount')
                    ->label('Saldo Akhir')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Rp '.number_format($state, 0, ',', '.') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('closed_at')
                    ->label('Waktu Tutup')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('opened_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(CashSessionStatus::class),
                Tables\Filters\SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('tutup_shift')
                    ->label('Tutup Shift')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (CashSession $record) => $record->status === CashSessionStatus::Open)
                    ->form([
                        Forms\Components\Placeholder::make('info')
                            ->content('Tutup shift akan mengakhiri pencatatan transaksi untuk sesi ini.'),
                        Forms\Components\TextInput::make('closing_balance_amount')
                            ->label('Uang Fisik Dihitung')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->hint('Total uang yang ada di laci kas saat ini.'),
                        Forms\Components\Textarea::make('closing_note')
                            ->label('Catatan (bila ada selisih)'),
                    ])
                    ->action(function (CashSession $record, array $data) {
                        $record->update([
                            'status' => CashSessionStatus::Closed,
                            'closed_at' => now(),
                            'closed_by' => auth()->id(),
                            'closing_balance_amount' => $data['closing_balance_amount'],
                            'closing_note' => $data['closing_note'] ?? null,
                        ]);
                    }),
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
            'index' => Pages\ListCashSessions::route('/'),
            'create' => Pages\CreateCashSession::route('/create'),
        ];
    }
}
