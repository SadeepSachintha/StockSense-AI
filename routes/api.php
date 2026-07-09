<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductForecastController;
use App\Http\Controllers\POSIngestController;
use App\Http\Controllers\TenantController;

Route::get('/products', [ProductForecastController::class, 'index']);
Route::post('/products', [ProductForecastController::class, 'store']);
Route::get('/products/{id}/forecast', [ProductForecastController::class, 'showForecast']);
Route::post('/products/ingest', [POSIngestController::class, 'ingest']);

// Tenant Administration CRUD Routes
Route::get('/tenants', [TenantController::class, 'index']);
Route::post('/tenants', [TenantController::class, 'store']);
Route::get('/tenants/{id}', [TenantController::class, 'show']);
Route::put('/tenants/{id}', [TenantController::class, 'update']);
Route::delete('/tenants/{id}', [TenantController::class, 'destroy']);
