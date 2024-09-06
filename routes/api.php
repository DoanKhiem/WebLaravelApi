<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\LoanControler;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentPeriodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::middleware('auth:sanctum')->post('check-token', [AuthController::class, 'checkToken']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'dashboard']);
    Route::resource('agent', AgentController::class);
    Route::resource('customer', CustomerController::class);
    Route::resource('package', PackageController::class);
    Route::resource('payment-period', PaymentPeriodController::class);
    Route::resource('loan', LoanControler::class);

    Route::get('export-pdf-loan', [LoanControler::class, 'exportPdf']);
});
