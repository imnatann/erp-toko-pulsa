<?php

namespace App\Filament\Resources\ManualChannelResource\Pages;

use App\Filament\Resources\ManualChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditManualChannel extends EditRecord
{
    protected static string $resource = ManualChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
