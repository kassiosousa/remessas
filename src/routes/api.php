<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportProjectController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPartnerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', function () {
    return response()->json(['message' => 'api ok']);
});

Route::prefix('partners')->group(function () {
    Route::get('', [PartnerController::class, 'index']);
    Route::post('', [PartnerController::class, 'store']);
    Route::get('{partner}', [PartnerController::class, 'show']);
    Route::put('{partner}', [PartnerController::class, 'update']);
    Route::delete('{partner}', [PartnerController::class, 'destroy']);
});

Route::prefix('projects')->group(function () {
    Route::get('', [ProjectController::class, 'index']);
    Route::post('', [ProjectController::class, 'store']);
    Route::get('{project}', [ProjectController::class, 'show']);
    Route::put('{project}', [ProjectController::class, 'update']);
    Route::delete('{project}', [ProjectController::class, 'destroy']);

    Route::put('{project}/partners', [ProjectPartnerController::class, 'sync']);
    Route::get('{project}/partners', [ProjectPartnerController::class, 'index']);
});

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('', [ReportController::class, 'index'])->name('index');
    Route::post('', [ReportController::class, 'store'])->name('store');
    Route::get('{report}', [ReportController::class, 'show'])->name('show');
    Route::put('{report}', [ReportController::class, 'update'])->name('update');
    Route::delete('{report}', [ReportController::class, 'destroy'])->name('destroy');

    Route::post('{report}/allocate', [ReportProjectController::class, 'allocate'])->name('allocate');
    Route::post('{report}/generate-payouts', [PayoutController::class, 'generateFromReport'])->name('generate-payouts');
});

Route::prefix('payouts')->name('payouts.')->group(function () {
    Route::get('', [PayoutController::class, 'index'])->name('index');
    Route::get('{payout}', [PayoutController::class, 'show'])->name('show');
    Route::put('{payout}', [PayoutController::class, 'update'])->name('update');
    Route::post('{payout}/mark-paid', [PayoutController::class, 'markPaid'])->name('mark-paid');
    Route::delete('{payout}', [PayoutController::class, 'destroy'])->name('destroy');
});