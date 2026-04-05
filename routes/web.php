<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CashSessionController;
use App\Http\Controllers\DigitalTransactionController;
use App\Http\Controllers\OperationalDashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PrintReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', OperationalDashboardController::class)->name('dashboard.operational');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('digital-transactions')->name('digital-transactions.')->group(function (): void {
        Route::get('/queue', [DigitalTransactionController::class, 'queue'])->name('queue');
        Route::get('/', [DigitalTransactionController::class, 'index'])->name('index');
        Route::get('/create', [DigitalTransactionController::class, 'create'])->name('create');
        Route::post('/', [DigitalTransactionController::class, 'store'])->name('store');
        Route::get('/{digitalTransaction}', [DigitalTransactionController::class, 'show'])->name('show');
        Route::post('/{digitalTransaction}/transition', [DigitalTransactionController::class, 'transition'])->name('transition');
        Route::post('/{digitalTransaction}/quick-transition', [DigitalTransactionController::class, 'quickTransition'])->name('quick-transition');
        Route::post('/{digitalTransaction}/assign', [DigitalTransactionController::class, 'assign'])->name('assign');
    });

    Route::prefix('cash-sessions')->name('cash-sessions.')->group(function (): void {
        Route::get('/', [CashSessionController::class, 'index'])->name('index');
        Route::post('/', [CashSessionController::class, 'store'])->name('store');
        Route::post('/{cashSession}/close', [CashSessionController::class, 'close'])->name('close');
    });

    Route::prefix('pos')->name('pos.')->group(function (): void {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/', [PosController::class, 'store'])->name('store');
    });

    Route::get('/print/sale/{sale}', [PrintReceiptController::class, 'sale'])->name('print.sale');
    Route::get('/print/digital/{transaction}', [PrintReceiptController::class, 'digital'])->name('print.digital');
});
