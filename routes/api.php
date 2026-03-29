<?php

use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\VectorController;
use App\Http\Controllers\DataPortController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Vectors
    Route::apiResource('vectors', VectorController::class)->parameters(['vectors' => 'id']);

    // Tags
    Route::apiResource('tags', TagController::class)->parameters(['tags' => 'id']);

    // Time entries
    Route::get('time-entries/current', [TimeEntryController::class, 'current']);
    Route::patch('time-entries/current/stop', [TimeEntryController::class, 'stop']);
    Route::apiResource('time-entries', TimeEntryController::class)->parameters(['time-entries' => 'id']);

    // Data portability
    Route::get('/export', [DataPortController::class, 'apiExport']);
    Route::post('/import', [DataPortController::class, 'apiImport']);
});
