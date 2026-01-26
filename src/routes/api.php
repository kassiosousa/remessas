<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportProjectController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPartnerController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AuthUserController;

Route::get('/', function () {
    return response()->json(['message' => 'api ok']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', LoginController::class);
    Route::post('/register', RegisterController::class);
    Route::post('/logout', LogoutController::class)->middleware('jwt');
    Route::get('/user', AuthUserController::class)->middleware('jwt');
});

Route::prefix('partners')->group(function () {
    Route::get('', [PartnerController::class, 'index'])->middleware('jwt');
    Route::post('', [PartnerController::class, 'store'])->middleware('jwt');
    Route::get('{partner}', [PartnerController::class, 'show'])->middleware('jwt');
    Route::put('{partner}', [PartnerController::class, 'update'])->middleware('jwt');
    Route::delete('{partner}', [PartnerController::class, 'destroy'])->middleware('jwt');
});

Route::prefix('projects')->group(function () {
    Route::get('', [ProjectController::class, 'index'])->middleware('jwt');
    Route::post('', [ProjectController::class, 'store'])->middleware('jwt');
    Route::get('{project}', [ProjectController::class, 'show'])->middleware('jwt');
    Route::put('{project}', [ProjectController::class, 'update'])->middleware('jwt');
    Route::delete('{project}', [ProjectController::class, 'destroy'])->middleware('jwt');

    Route::put('{project}/partners', [ProjectPartnerController::class, 'sync'])->middleware('jwt');
    Route::get('{project}/partners', [ProjectPartnerController::class, 'index'])->middleware('jwt');
});

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('', [ReportController::class, 'index'])->name('index')->middleware('jwt');
    Route::post('', [ReportController::class, 'store'])->name('store')->middleware('jwt');
    Route::get('{report}', [ReportController::class, 'show'])->name('show')->middleware('jwt');
    Route::put('{report}', [ReportController::class, 'update'])->name('update')->middleware('jwt');
    Route::delete('{report}', [ReportController::class, 'destroy'])->name('destroy')->middleware('jwt');
    Route::post('{report}/allocate', [ReportProjectController::class, 'allocate'])->name('allocate')->middleware('jwt');
    Route::post('{report}/generate-payouts', [PayoutController::class, 'generateFromReport'])->name('generate-payouts')->middleware('jwt');
});

Route::prefix('payouts')->name('payouts.')->group(function () {
    Route::get('', [PayoutController::class, 'index'])->name('index')->middleware('jwt');
    Route::get('{payout}', [PayoutController::class, 'show'])->name('show')->middleware('jwt');
    Route::put('{payout}', [PayoutController::class, 'update'])->name('update')->middleware('jwt');
    Route::post('{payout}/mark-paid', [PayoutController::class, 'markPaid'])->name('mark-paid')->middleware('jwt');
    Route::delete('{payout}', [PayoutController::class, 'destroy'])->name('destroy')->middleware('jwt');
});