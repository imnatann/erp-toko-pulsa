<?php

namespace App\Filament\Resources\DigitalServiceResource\Pages;

use App\Filament\Resources\DigitalServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDigitalServices extends ListRecords
{
    protected static string $resource = DigitalServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
