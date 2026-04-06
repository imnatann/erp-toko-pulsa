<?php

use App\Http\Controllers\PrintReceiptController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');
Route::redirect('/login', '/admin/login')->name('login');

Route::middleware('auth')->group(function (): void {
    Route::get('/print/sale/{sale}', [PrintReceiptController::class, 'sale'])->name('print.sale');
    Route::get('/print/digital/{transaction}', [PrintReceiptController::class, 'digital'])->name('print.digital');
});
