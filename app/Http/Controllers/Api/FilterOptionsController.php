<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class FilterOptionsController extends Controller
{
    /**
     * List the categories and brands available as filters.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'categories' => Product::query()->distinct()->orderBy('category')->pluck('category'),
                'brands' => Product::query()->distinct()->orderBy('brand')->pluck('brand'),
            ],
        ]);
    }
}
