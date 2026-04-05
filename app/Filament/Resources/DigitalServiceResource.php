<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DigitalServiceResource\Pages;
use App\Models\DigitalService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DigitalServiceResource extends Resource
{
    protected static ?string $model = DigitalService::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationLabel = 'Layanan Digital';

    protected static ?string $pluralLabel = 'Layanan Digital';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Layanan')
                    ->schema([
                        Forms\Components\Select::make('service_category_id')
                            ->label('Kategori')
                            ->relationship('serviceCategory', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Layanan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('provider')
                            ->label('Provider')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Nominal & Konfigurasi')
                    ->schema([
                        Forms\Components\TextInput::make('default_nominal_amount')
                            ->label('Nominal Default')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('default_fee_amount')
                            ->label('Fee Default')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->required(),
                        Forms\Components\Toggle::make('requires_reference')
                            ->label('Butuh Referensi')
                            ->required(),
                        Forms\Components\Toggle::make('requires_destination_name')
                            ->label('Butuh Nama Tujuan')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serviceCategory.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Layanan')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('provider')
                    ->label('Provider')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('default_nominal_amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_fee_amount')
                    ->label('Fee')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_category_id')
                    ->label('Kategori')
                    ->relationship('serviceCategory', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDigitalServices::route('/'),
            'create' => Pages\CreateDigitalService::route('/create'),
            'edit' => Pages\EditDigitalService::route('/{record}/edit'),
        ];
    }
}
