<?php

namespace App\Filament\Resources\DigitalServiceResource\Pages;

use App\Filament\Resources\DigitalServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDigitalService extends EditRecord
{
    protected static string $resource = DigitalServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
