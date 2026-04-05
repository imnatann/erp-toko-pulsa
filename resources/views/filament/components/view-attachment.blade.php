@if($attachment)
    <div class="flex justify-center items-center">
        <img src="{{ Storage::disk($attachment->disk)->url($attachment->path) }}" alt="{{ $attachment->original_name }}" class="max-w-full rounded-lg shadow-sm border border-gray-300 dark:border-gray-700">
    </div>
@else
    <p class="text-center text-gray-500">Tidak ada bukti transaksi</p>
@endif
