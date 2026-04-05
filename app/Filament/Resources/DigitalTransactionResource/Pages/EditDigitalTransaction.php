<?php

namespace App\Filament\Resources\DigitalTransactionResource\Pages;

use App\Filament\Resources\DigitalTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDigitalTransaction extends EditRecord
{
    protected static string $resource = DigitalTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
