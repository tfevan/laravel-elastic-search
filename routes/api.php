<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductIndexController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::get('/products/index-summary', [ProductIndexController::class, 'summary']);
Route::get('/products/{product}/document', [ProductIndexController::class, 'document']);
Route::apiResource('products', ProductController::class)
    ->only(['index', 'store', 'update', 'destroy']);
Route::get('/search', SearchController::class);
