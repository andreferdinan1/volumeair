<?php

use App\Http\Controllers\VolumeAirController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [VolumeAirController::class, 'health']);

Route::prefix('volumeair')->group(function () {
    Route::get('/current', [VolumeAirController::class, 'getCurrent']);
    Route::get('/stats',   [VolumeAirController::class, 'getStats']);
    Route::get('/latest',  [VolumeAirController::class, 'getLatest']);

    Route::post('/add-data',          [VolumeAirController::class, 'addSensorData']);
    Route::post('/add-prayer-volume', [VolumeAirController::class, 'addPrayerVolume']);
    Route::get('/daily-summary',      [VolumeAirController::class, 'getDailySummary']);
    Route::get('/prayer-reports',     [VolumeAirController::class, 'getPrayerReports']);
    Route::get('/weekly-view',        [VolumeAirController::class, 'getWeeklyView']);
});
