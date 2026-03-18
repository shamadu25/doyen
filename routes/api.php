<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:web');

// API routes for external integrations
Route::prefix('v1')->group(function () {
    // DVSA MOT API webhooks
    Route::post('/mot/webhook', function (Request $request) {
        // Handle MOT test results webhook
        return response()->json(['status' => 'success']);
    });
    
    // DVLA API webhooks
    Route::post('/dvla/webhook', function (Request $request) {
        // Handle vehicle data webhook
        return response()->json(['status' => 'success']);
    });
});
