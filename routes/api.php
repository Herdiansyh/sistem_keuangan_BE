<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AccountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
    });

    // Account routes - manual definition to avoid conflicts
    Route::get('accounts/tree', [AccountController::class, 'tree']);
    Route::get('accounts', [AccountController::class, 'index']);
    Route::post('accounts', [AccountController::class, 'store']);
    Route::get('accounts/{account}', [AccountController::class, 'show']);
    Route::put('accounts/{account}', [AccountController::class, 'update']);
    Route::delete('accounts/{account}', [AccountController::class, 'destroy']);

    // Transaction routes
    Route::get('transactions/statistics', [\App\Http\Controllers\TransactionController::class, 'statistics']);
    Route::get('transactions/report', [\App\Http\Controllers\TransactionController::class, 'report']);
    Route::get('transactions', [\App\Http\Controllers\TransactionController::class, 'index']);
    Route::post('transactions', [\App\Http\Controllers\TransactionController::class, 'store']);
    Route::get('transactions/{transaction}', [\App\Http\Controllers\TransactionController::class, 'show']);
    Route::put('transactions/{transaction}', [\App\Http\Controllers\TransactionController::class, 'update']);
    Route::delete('transactions/{transaction}', [\App\Http\Controllers\TransactionController::class, 'destroy']);
});
