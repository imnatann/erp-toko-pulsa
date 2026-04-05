<?php

namespace App\Filament\Pages;

use App\Models\LedgerEntry;
use App\Models\Outlet;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanPenjualan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.laporan-penjualan';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $title = 'Laporan Penjualan';

    public ?string $outlet_id = null;

    public ?string $date_from = null;

    public ?string $date_until = null;

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_until' => now()->endOfMonth()->toDateString(),
            'outlet_id' => auth()->user()->role->value === 'owner' || auth()->user()->role->value === 'admin' ? null : auth()->user()->outlet_id,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->options(Outlet::pluck('name', 'id'))
                    ->disabled(fn () => ! in_array(auth()->user()->role->value, ['owner', 'admin'])),
                DatePicker::make('date_from')->label('Dari Tanggal'),
                DatePicker::make('date_until')->label('Sampai Tanggal'),
            ])
            ->columns(3);
    }

    public function getReportDataProperty()
    {
        return LedgerEntry::query()
            ->when($this->outlet_id, fn ($q) => $q->where('outlet_id', $this->outlet_id))
            ->when($this->date_from, fn ($q) => $q->whereDate('entry_date', '>=', $this->date_from))
            ->when($this->date_until, fn ($q) => $q->whereDate('entry_date', '<=', $this->date_until))
            ->selectRaw('entry_date, entry_type, SUM(amount) as total')
            ->groupBy('entry_date', 'entry_type')
            ->orderBy('entry_date', 'desc')
            ->get();
    }

    public function exportCsv(): StreamedResponse
    {
        $data = $this->reportData;

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=Laporan_Penjualan_'.now()->format('Ymd_His').'.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['Tanggal', 'Tipe Entri', 'Total (Rp)'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->entry_date->format('Y-m-d'),
                    $row->entry_type,
                    $row->total,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
