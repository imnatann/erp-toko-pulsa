<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Services\StockService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $stock = app(StockService::class)->initializeStock(
                $data['outlet_id'],
                $data['product_id'],
                $data['on_hand_qty'] ?? 0,
                auth()->user()
            );

            // Update min stock if provided
            if (isset($data['minimum_stock'])) {
                $stock->update(['minimum_stock' => $data['minimum_stock']]);
            }

            return $stock;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal inisialisasi stok')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
