<?php

use App\Http\Controllers\Report;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('reports')->group(function () {
        Route::prefix('orders')->group(function () {
            Route::get('monthly-trend', Report\OrderMonthlyTrendReportController::class);
        });

        Route::prefix('sales')->group(function () {
            Route::get('target-vs-actual-revenue', Report\SalesTargetVsRevenueReportController::class);
            Route::get('performance-statistic', Report\SalesPerformanceStatisticReportController::class);
        });
    });
});
