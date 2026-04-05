<?php

namespace App\Filament\Resources\ManualChannelResource\Pages;

use App\Filament\Resources\ManualChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListManualChannels extends ListRecords
{
    protected static string $resource = ManualChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
