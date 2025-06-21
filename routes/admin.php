<?php

use Azuriom\Plugin\ApiLimiter\Controllers\Admin\LimiterController;
use Azuriom\Plugin\ApiLimiter\Controllers\Admin\LogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your plugin. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group.
|
*/

Route::get('/settings', [LimiterController::class, 'settings'])->name('settings');
Route::post('/settings', [LimiterController::class, 'update'])->name('update');
Route::get('/api-routes', [LimiterController::class, 'apiRoutes'])->name('api-routes');
Route::post('/clear', [LimiterController::class, 'clear'])->name('clear');

// Logs routes
Route::get('/logs', [LogController::class, 'index'])->name('logs');
Route::post('/logs/clear', [LogController::class, 'clear'])->name('logs.clear');
Route::get('/logs/download', [LogController::class, 'download'])->name('logs.download'); 