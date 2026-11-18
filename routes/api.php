<?php

use App\Http\Controllers\Api\AutocompleteController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\FilterOptionsController;
use App\Http\Controllers\Api\FuzzyController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MappingController;
use App\Http\Controllers\Api\PaginationController;
use App\Http\Controllers\Api\ProductActivationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductIndexController;
use App\Http\Controllers\Api\RelevanceController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::get('/products/index-summary', [ProductIndexController::class, 'summary']);
Route::get('/products/{product}/document', [ProductIndexController::class, 'document']);
Route::apiResource('products', ProductController::class)
    ->only(['index', 'store', 'update', 'destroy']);

Route::get('/search', SearchController::class);

Route::get('/filters/options', FilterOptionsController::class);
Route::get('/filters', FilterController::class);

Route::get('/pagination', PaginationController::class);

Route::get('/documents/raw', [DocumentController::class, 'raw']);
Route::get('/documents/{product}', [DocumentController::class, 'show']);
Route::patch('/products/{product}/activation', ProductActivationController::class);

Route::get('/mapping', [MappingController::class, 'show']);
Route::get('/mapping/analyze', [MappingController::class, 'analyze']);

Route::get('/relevance', [RelevanceController::class, 'search']);
Route::get('/relevance/{product}/explain', [RelevanceController::class, 'explain']);

Route::get('/fuzzy', FuzzyController::class);

Route::get('/autocomplete', AutocompleteController::class);
