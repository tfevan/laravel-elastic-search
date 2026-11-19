<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TouchProductsRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class IndexingController extends Controller
{
    /**
     * Report how Scout currently indexes changes.
     */
    public function status(): JsonResponse
    {
        return response()->json(['data' => $this->snapshot()]);
    }

    /**
     * Save products again so Scout re-indexes them, and measure how long the request takes.
     */
    public function touch(TouchProductsRequest $request): JsonResponse
    {
        $started = hrtime(true);

        $products = Product::query()->limit($request->integer('count'))->get();

        foreach ($products as $product) {
            $product->touch();
        }

        $elapsed = round((hrtime(true) - $started) / 1_000_000, 1);

        return response()->json(['data' => $this->snapshot() + [
            'touched' => $products->count(),
            'elapsed_ms' => $elapsed,
        ]]);
    }

    /**
     * Describe the indexing mode and the number of waiting jobs.
     */
    private function snapshot(): array
    {
        $connection = (string) config('queue.default');

        return [
            'queued' => (bool) config('scout.queue'),
            'queue_connection' => $connection,
            'pending_jobs' => $connection === 'database' ? DB::table('jobs')->count() : null,
        ];
    }
}
