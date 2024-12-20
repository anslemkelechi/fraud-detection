<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//API routes here

// Get Server Health Status
Route::get('/health', function () {
    return response()->json([
        'status' => 'Success',
        'message' => 'Server is up and running 🚀',
    ], 200);
})->name('healthCheck');

Route::prefix('risk-weight')->group(function ($router) {
    Route::post('/', [App\Http\Controllers\RiskWeightController::class, 'createRiskWeight'])->name('createRiskWeight');
    Route::patch('/', [App\Http\Controllers\RiskWeightController::class, 'updateRiskWeight'])->name('updateRiskWeight');
    Route::get('/{identifier}', [App\Http\Controllers\RiskWeightController::class, 'getRiskWeightByIdentifier'])->name('getRiskWeightByIdentifier');
    Route::get('/', [App\Http\Controllers\RiskWeightController::class, 'getAllRiskWeights'])->name('getAllRiskWeights');
});
Route::prefix('users')->group(function ($router) {
    Route::post('/', [App\Http\Controllers\UserController::class, 'createUser'])->name('createUser');
    Route::patch('/', [App\Http\Controllers\UserController::class, 'updateUser'])->name('updateUser');
    Route::get('/', [App\Http\Controllers\UserController::class, 'getAllUsers'])->name('getAllUsers');
});
