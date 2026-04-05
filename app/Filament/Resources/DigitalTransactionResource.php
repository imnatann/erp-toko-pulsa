<?php

namespace App\Filament\Resources;

use App\Enums\DigitalTransactionStatus;
use App\Filament\Resources\DigitalTransactionResource\Pages;
use App\Filament\Resources\DigitalTransactionResource\RelationManagers\StatusLogsRelationManager;
use App\Filament\Traits\IsolatesOutletData;
use App\Models\DigitalTransaction;
use App\Models\TransactionAttachment;
use App\Services\DigitalTransactionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DigitalTransactionResource extends Resource
{
    protected static ?string $model = DigitalTransaction::class;

    use IsolatesOutletData;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Transaksi Digital';

    protected static ?string $pluralLabel = 'Transaksi Digital';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Informasi Pelanggan')
                        ->schema([
                            Forms\Components\Select::make('outlet_id')
                                ->relationship('outlet', 'name')
                                ->required()
                                ->default(fn () => auth()->user()->outlet_id ?? null)
                                ->searchable(),
                            Forms\Components\Select::make('customer_id')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                    Forms\Components\TextInput::make('phone')->tel(),
                                ]),
                            Forms\Components\TextInput::make('destination_account')
                                ->label('Nomor Tujuan')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('destination_name')
                                ->label('Nama Tujuan (opsional)')
                                ->maxLength(255),
                        ])->columns(2),

                    Forms\Components\Section::make('Layanan & Nominal')
                        ->schema([
                            Forms\Components\Select::make('digital_service_id')
                                ->relationship('digitalService', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('nominal_amount')
                                ->label('Nominal Pokok')
                                ->required()
                                ->numeric()
                                ->prefix('Rp'),
                            Forms\Components\TextInput::make('fee_amount')
                                ->label('Fee Admin')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),
                        ])->columns(2),
                ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status & Log')
                        ->schema([
                            Forms\Components\TextInput::make('status')
                                ->disabled()
                                ->formatStateUsing(fn ($state) => DigitalTransactionStatus::tryFrom($state)?->getLabel() ?? 'Draft')
                                ->visibleOn('edit'),
                            Forms\Components\TextInput::make('code')
                                ->disabled()
                                ->visibleOn('edit'),
                            Forms\Components\Textarea::make('operator_note')
                                ->disabled()
                                ->visibleOn('edit'),
                            Forms\Components\Textarea::make('validation_note')
                                ->disabled()
                                ->visibleOn('edit'),
                        ]),
                ])->columnSpan(['lg' => 1]),

                Forms\Components\Hidden::make('status')
                    ->default(DigitalTransactionStatus::Draft->value),
                Forms\Components\Hidden::make('total_amount')
                    ->default(0),
                Forms\Components\Hidden::make('cash_effect_amount')
                    ->default(0),
                Forms\Components\Hidden::make('created_by')
                    ->default(fn () => auth()->id()),
                Forms\Components\Hidden::make('code')
                    ->default(fn () => 'TRX-'.strtoupper(uniqid())),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('code')
                            ->weight('bold')
                            ->searchable()
                            ->copyable(),
                        Tables\Columns\TextColumn::make('digitalService.name')
                            ->color('gray'),
                    ]),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('destination_account')
                            ->icon('heroicon-m-phone')
                            ->searchable()
                            ->copyable(),
                        Tables\Columns\TextColumn::make('customer.name')
                            ->color('gray'),
                    ]),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('total_amount')
                            ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                            ->weight('bold'),
                        Tables\Columns\TextColumn::make('status')
                            ->badge(),
                    ]),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('created_at')
                            ->dateTime()
                            ->color('gray'),
                        Tables\Columns\TextColumn::make('creator.name')
                            ->color('gray'),
                    ]),
                ])->from('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(DigitalTransactionStatus::class),
            ])
            ->actions([
                Tables\Actions\Action::make('proses')
                    ->label('Proses')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (DigitalTransaction $record) => $record->status === DigitalTransactionStatus::Draft)
                    ->action(function (DigitalTransaction $record) {
                        app(DigitalTransactionService::class)->submitTransaction($record, auth()->user());
                    }),

                Tables\Actions\Action::make('selesai_proses')
                    ->label('Selesai Diproses')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('note')->label('Catatan Operator'),
                        Forms\Components\FileUpload::make('attachment')
                            ->label('Upload Bukti (Struk/Transfer)')
                            ->directory('transaction_attachments')
                            ->image()
                            ->maxSize(2048),
                    ])
                    ->visible(fn (DigitalTransaction $record) => $record->status === DigitalTransactionStatus::InProgress)
                    ->action(function (DigitalTransaction $record, array $data) {
                        app(DigitalTransactionService::class)->markAsPendingValidation($record, auth()->user(), $data['note'] ?? null);

                        if (! empty($data['attachment'])) {
                            TransactionAttachment::create([
                                'digital_transaction_id' => $record->id,
                                'uploaded_by' => auth()->id(),
                                'disk' => config('filament.default_filesystem_disk', 'public'),
                                'path' => $data['attachment'],
                                'original_name' => 'Bukti_'.$record->code,
                                'attachment_type' => 'proof_of_payment',
                            ]);
                        }
                    }),

                Tables\Actions\Action::make('validasi_sukses')
                    ->label('Validasi Sukses')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('note')->label('Catatan Validasi'),
                    ])
                    ->visible(fn (DigitalTransaction $record) => $record->status === DigitalTransactionStatus::PendingValidation)
                    ->action(function (DigitalTransaction $record, array $data) {
                        app(DigitalTransactionService::class)->validateAsSuccess($record, auth()->user(), $data['note'] ?? null);
                    }),

                Tables\Actions\Action::make('validasi_gagal')
                    ->label('Validasi Gagal')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('note')->label('Alasan Gagal')->required(),
                    ])
                    ->visible(fn (DigitalTransaction $record) => $record->status === DigitalTransactionStatus::PendingValidation)
                    ->action(function (DigitalTransaction $record, array $data) {
                        app(DigitalTransactionService::class)->validateAsFailed($record, auth()->user(), $data['note'] ?? null);
                    }),

                Tables\Actions\Action::make('batalkan')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('note')->label('Alasan Pembatalan'),
                    ])
                    ->visible(fn (DigitalTransaction $record) => $record->status === DigitalTransactionStatus::Draft)
                    ->action(function (DigitalTransaction $record, array $data) {
                        app(DigitalTransactionService::class)->cancelTransaction($record, auth()->user(), $data['note'] ?? null);
                    }),

                Tables\Actions\Action::make('lihat_bukti')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->visible(fn (DigitalTransaction $record) => $record->attachments()->exists())
                    ->modalContent(fn (DigitalTransaction $record) => view('filament.components.view-attachment', ['attachment' => $record->attachments()->latest()->first()]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                Tables\Actions\Action::make('cetak_struk')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (DigitalTransaction $record) => route('print.digital', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->visible(fn (DigitalTransaction $record) => ! $record->status->isFinal()),
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            StatusLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDigitalTransactions::route('/'),
            'create' => Pages\CreateDigitalTransaction::route('/create'),
            'edit' => Pages\EditDigitalTransaction::route('/{record}/edit'),
        ];
    }
}
