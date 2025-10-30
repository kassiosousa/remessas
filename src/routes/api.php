<?php

use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportProjectController;
use App\Http\Controllers\PayoutController;

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('',        [ReportController::class, 'index'])->name('index');
    Route::post('',       [ReportController::class, 'store'])->name('store');
    Route::get('{report}',[ReportController::class, 'show'])->name('show');
    Route::put('{report}',[ReportController::class, 'update'])->name('update');
    Route::delete('{report}',[ReportController::class,'destroy'])->name('destroy');

    Route::post('{report}/allocate', [ReportProjectController::class, 'allocate'])->name('allocate');

    Route::post('{report}/generate-payouts', [PayoutController::class, 'generateFromReport'])
        ->name('generate-payouts');
});

Route::prefix('payouts')->name('payouts.')->group(function () {
    Route::get('',          [PayoutController::class, 'index'])->name('index');
    Route::get('{payout}',  [PayoutController::class, 'show'])->name('show');
    Route::put('{payout}',  [PayoutController::class, 'update'])->name('update');
    Route::post('{payout}/mark-paid', [PayoutController::class, 'markPaid'])->name('mark-paid');
    Route::delete('{payout}',[PayoutController::class,'destroy'])->name('destroy');
});

