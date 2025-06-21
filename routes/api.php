<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your plugin. These
| routes are loaded by the RouteServiceProvider of your plugin within
| a group which contains the "api" middleware group and your plugin name
| as prefix. Now create something great!
|
*/

Route::get('/rate-limiter-test', function (Request $request) {
    return response()->json([
        'message' => 'API limiter test endpoint',
        'client_ip' => $request->ip(),
        'timestamp' => now(),
        'rate_limit_enabled' => \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('enabled', false),
        // REMOVED: whitelist_ips - confidential information leak
    ]);
})->middleware('throttle:10,1'); // Limit: 10 requests per minute

// Test route for IP debugging (only for authorized admins)
Route::get('/debug-ip', function (Request $request) {
    // Check admin permissions
    if (!auth()->check() || !auth()->user()->can('admin.access')) {
        return response()->json(['error' => 'Access denied'], 403);
    }
    
    return response()->json([
        'request_ip' => $request->ip(),
        'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'forwarded_for' => $request->header('X-Forwarded-For'),
        'real_ip' => $request->header('X-Real-IP'),
        // REMOVED: all_headers - confidential information leak
    ]);
})->middleware('throttle:5,1'); // Limit: 5 requests per minute

// Test route with direct middleware application
Route::get('/test-middleware', function (Request $request) {
    return response()->json([
        'message' => 'This route has direct middleware applied',
        'client_ip' => $request->ip(),
        'timestamp' => now(),
    ]);
})->middleware([\Azuriom\Plugin\ApiLimiter\Middleware\ApiLimiter::class, 'throttle:10,1']); 