<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\LoanControler;
use App\Http\Controllers\DashboardController;
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
    Route::resource('member', MemberController::class);
    Route::resource('customer', CustomerController::class);
    Route::resource('package', PackageController::class);
    Route::resource('loan', LoanControler::class);

    Route::get('export-pdf-loan', [LoanControler::class, 'exportPdf']);
});
