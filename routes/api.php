<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductForecastController;
use App\Http\Controllers\POSIngestController;
use App\Http\Controllers\TenantController;

Route::get('/products', [ProductForecastController::class, 'index']);
Route::post('/products', [ProductForecastController::class, 'store']);
Route::get('/products/{id}/forecast', [ProductForecastController::class, 'showForecast']);
Route::put('/products/{id}/replenish', [ProductForecastController::class, 'replenish']);
Route::put('/products/{id}/price', [ProductForecastController::class, 'updatePrice']);
Route::post('/products/ingest', [POSIngestController::class, 'ingest']);
Route::get('/transactions', [POSIngestController::class, 'index']);
Route::delete('/transactions/{receipt_id}', [POSIngestController::class, 'destroy']);

// Register Shift Routes
Route::get('/shifts/active', [\App\Http\Controllers\POSShiftController::class, 'active']);
Route::post('/shifts/open', [\App\Http\Controllers\POSShiftController::class, 'open']);
Route::post('/shifts/close', [\App\Http\Controllers\POSShiftController::class, 'close']);

// Supplier Management Routes
Route::get('/suppliers', [\App\Http\Controllers\SupplierController::class, 'index']);
Route::post('/suppliers', [\App\Http\Controllers\SupplierController::class, 'store']);

// Tenant Administration CRUD Routes
Route::get('/tenants', [TenantController::class, 'index']);
Route::post('/tenants', [TenantController::class, 'store']);
Route::get('/tenants/{id}', [TenantController::class, 'show']);
Route::put('/tenants/{id}', [TenantController::class, 'update']);
Route::delete('/tenants/{id}', [TenantController::class, 'destroy']);
