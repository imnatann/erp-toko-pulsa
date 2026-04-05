<?php

namespace App\Filament\Resources\DigitalTransactionResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StatusLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'statusLogs';

    protected static ?string $title = 'Riwayat Status';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('acted_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('to_status')
                    ->label('Status Baru')
                    ->badge(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Oleh'),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(80)
                    ->wrap(),
            ])
            ->defaultSort('acted_at', 'asc')
            ->paginated(false);
    }
}
