<x-filament-panels::page>
    <x-filament::section icon="heroicon-o-funnel" heading="Filter Laporan">
        <form wire:submit.prevent="$refresh">
            {{ $this->form }}

            <div class="mt-6 flex items-center gap-4">
                <x-filament::button type="submit" color="primary">
                    Tampilkan Data
                </x-filament::button>

                <x-filament::button wire:click="exportCsv" color="gray" icon="heroicon-o-arrow-down-tray">
                    Unduh CSV
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    @php
        $reports = $this->reportData;
        $grandTotal = 0;
    @endphp

    <x-filament::section>
        @if($reports->isEmpty())
            <div class="flex flex-col items-center justify-center p-8 text-center">
                <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum Ada Data Penjualan</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ubah rentang tanggal atau outlet untuk melihat data.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                            <th class="px-4 py-3 font-semibold text-sm text-gray-900 dark:text-white">Tanggal Transaksi</th>
                            <th class="px-4 py-3 font-semibold text-sm text-gray-900 dark:text-white">Tipe Entri</th>
                            <th class="px-4 py-3 font-semibold text-sm text-right text-gray-900 dark:text-white">Pendapatan Bersih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach($reports as $data)
                            @php $grandTotal += $data->total; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $data->entry_date->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($data->entry_type == 'digital_sale') 
                                        <x-filament::badge color="info">Penjualan Digital</x-filament::badge>
                                    @elseif($data->entry_type == 'digital_fee') 
                                        <x-filament::badge color="success">Fee Admin Digital</x-filament::badge>
                                    @elseif($data->entry_type == 'penjualan_fisik') 
                                        <x-filament::badge color="warning">Penjualan Aksesoris</x-filament::badge>
                                    @else 
                                        <x-filament::badge color="gray">{{ $data->entry_type }}</x-filament::badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-white">
                                    Rp {{ number_format($data->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                            <td colspan="2" class="px-4 py-4 text-right font-medium text-gray-700 dark:text-gray-300">
                                Total Omzet Keseluruhan
                            </td>
                            <td class="px-4 py-4 text-right font-bold text-lg text-primary-600 dark:text-primary-400">
                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
