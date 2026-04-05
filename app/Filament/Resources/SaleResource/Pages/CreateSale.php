<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Services\SaleService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Override handleRecordCreation agar semua logika bisnis
     * (stok, kas, ledger) berjalan via SaleService, bukan Eloquent langsung.
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            $sale = app(SaleService::class)->checkout($data, auth()->user());

            Notification::make()
                ->title('Penjualan berhasil dicatat')
                ->body("Kode: {$sale->code} | Total: Rp ".number_format($sale->total_amount, 0, ',', '.'))
                ->success()
                ->send();

            return $sale;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal mencatat penjualan')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
