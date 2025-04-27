<?php

use App\Http\Controllers\Report;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('reports')->group(function () {
        Route::prefix('orders')->group(function () {
            Route::get('monthly-trend', Report\OrderMonthlyTrendReportController::class);
        });
    });
});
