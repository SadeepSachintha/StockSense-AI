<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductForecastController;
use App\Http\Controllers\POSIngestController;
use App\Http\Controllers\TenantController;

Route::get('/products', [ProductForecastController::class, 'index']);
Route::post('/products', [ProductForecastController::class, 'store']);
Route::post('/products/ingest', [POSIngestController::class, 'ingest']);
Route::get('/transactions', [POSIngestController::class, 'index']);
Route::delete('/transactions/{receipt_id}', [POSIngestController::class, 'destroy']);

// Basic & Premium Tier Routes
Route::middleware(['tier:basic'])->group(function () {
    Route::put('/products/{id}/replenish', [ProductForecastController::class, 'replenish']);
    Route::put('/products/{id}/price', [ProductForecastController::class, 'updatePrice']);

    // Supplier Management Routes
    Route::get('/suppliers', [\App\Http\Controllers\SupplierController::class, 'index']);
    Route::post('/suppliers', [\App\Http\Controllers\SupplierController::class, 'store']);

    // Supplier Purchase Orders Routes
    Route::get('/purchase-orders', [\App\Http\Controllers\SupplierPOController::class, 'index']);
    Route::post('/purchase-orders', [\App\Http\Controllers\SupplierPOController::class, 'dispatch']);
    Route::post('/purchase-orders/{id}/receive', [\App\Http\Controllers\SupplierPOController::class, 'receive']);
});

// Premium Tier ONLY Routes
Route::middleware(['tier:premium'])->group(function () {
    Route::get('/products/{id}/forecast', [ProductForecastController::class, 'showForecast']);
    Route::put('/products/{id}/safety-stock', [ProductForecastController::class, 'updateSafetyStock']);
});

// Register Shift Routes
Route::get('/shifts/active', [\App\Http\Controllers\POSShiftController::class, 'active']);
Route::post('/shifts/open', [\App\Http\Controllers\POSShiftController::class, 'open']);
Route::post('/shifts/close', [\App\Http\Controllers\POSShiftController::class, 'close']);

// Tenant Administration CRUD Routes
Route::get('/tenants', [TenantController::class, 'index']);
Route::post('/tenants', [TenantController::class, 'store']);
Route::get('/tenants/{id}', [TenantController::class, 'show']);
Route::put('/tenants/{id}', [TenantController::class, 'update']);
Route::delete('/tenants/{id}', [TenantController::class, 'destroy']);

// Tenant User Provisioning Routes
Route::get('/tenants/{id}/users', [TenantController::class, 'getUsers']);
Route::post('/tenants/{id}/users', [TenantController::class, 'addUser']);
Route::delete('/tenant-users/{id}', [TenantController::class, 'deleteUser']);
