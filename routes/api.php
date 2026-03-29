<?php

use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\TogglCompatController;
use App\Http\Controllers\Api\VectorController;
use App\Http\Controllers\DataPortController;
use App\Http\Middleware\TogglBasicAuth;
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

// Toggl API v9 compatible endpoints (for StreamDeck plugin and other Toggl clients)
Route::middleware(TogglBasicAuth::class)->prefix('v9')->group(function () {
    Route::get('/me', [TogglCompatController::class, 'me']);
    Route::get('/me/workspaces', [TogglCompatController::class, 'workspaces']);
    Route::get('/me/time_entries/current', [TogglCompatController::class, 'currentEntry']);
    Route::get('/me/time_entries', [TogglCompatController::class, 'listEntries']);

    Route::post('/workspaces/{wid}/time_entries', [TogglCompatController::class, 'startEntry']);
    Route::patch('/workspaces/{wid}/time_entries/{id}/stop', [TogglCompatController::class, 'stopEntry']);
    Route::put('/workspaces/{wid}/time_entries/{id}', [TogglCompatController::class, 'updateEntry']);
    Route::delete('/workspaces/{wid}/time_entries/{id}', [TogglCompatController::class, 'deleteEntry']);

    Route::get('/workspaces/{wid}/projects', [TogglCompatController::class, 'projects']);
});
