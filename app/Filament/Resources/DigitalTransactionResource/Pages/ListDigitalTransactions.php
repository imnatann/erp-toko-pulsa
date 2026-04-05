<?php

namespace App\Filament\Resources\DigitalTransactionResource\Pages;

use App\Filament\Resources\DigitalTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDigitalTransactions extends ListRecords
{
    protected static string $resource = DigitalTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
