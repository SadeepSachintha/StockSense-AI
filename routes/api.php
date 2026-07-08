<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductForecastController;
use App\Http\Controllers\POSIngestController;

Route::get('/products', [ProductForecastController::class, 'index']);
Route::get('/products/{id}/forecast', [ProductForecastController::class, 'showForecast']);
Route::post('/products/ingest', [POSIngestController::class, 'ingest']);
