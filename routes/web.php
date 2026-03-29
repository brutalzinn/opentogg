<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DataPortController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect']);
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    Route::get('/vectors', function () {
        return view('pages.vectors');
    })->name('vectors');

    Route::get('/tags', function () {
        return view('pages.tags');
    })->name('tags');

    Route::get('/reports', function () {
        return view('pages.reports');
    })->name('reports');

    Route::get('/reports/export/csv', [ReportExportController::class, 'csv'])->name('reports.export.csv');
    Route::get('/reports/export/pdf', [ReportExportController::class, 'pdf'])->name('reports.export.pdf');

    Route::get('/settings', function () {
        return view('pages.settings');
    })->name('settings');

    Route::get('/data/export', [DataPortController::class, 'export'])->name('data.export');
    Route::post('/data/import', [DataPortController::class, 'import'])->name('data.import');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
