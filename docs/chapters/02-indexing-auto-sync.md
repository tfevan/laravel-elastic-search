# Chapter 2: Indexing & Auto-sync

## What you will learn

- How the `Searchable` trait syncs a model with Elasticsearch on create, update and delete.
- How to bulk import, flush and delete an index with Scout commands.
- How to skip syncing while seeding large amounts of data.

## Where the code lives

| Layer    | File                                                        |
|----------|-------------------------------------------------------------|
| Model    | `app/Models/Product.php` (`Searchable`, `toSearchableArray`) |
| Endpoint | `/api/v1/products` (`ProductController`)                    |
| Status   | `/api/v1/products/index-summary` and `/{product}/document`  |
| Page     | `resources/js/pages/chapters/Chapter02Indexing.vue`         |

## Scout commands

```bash
php artisan scout:import "App\Models\Product"   # bulk index every row
php artisan scout:flush "App\Models\Product"    # remove all documents, keep the index
php artisan scout:delete-index products         # drop the index itself
```

## Pausing the sync

```php
Product::withoutSyncingToSearch(function (): void {
    Product::factory()->count(1000)->create();
});
```

Seeding 1000 rows one by one would send 1000 requests. The seeder pauses the sync and runs one bulk
`scout:import` instead.

## Queueing

Sync runs inside the request by default. Set `SCOUT_QUEUE=true` to index in a queue worker instead.

## Try it

1. Open `/products`, create a product and click **Indexed doc**.
2. Delete a product and watch both counters drop.
3. Run `php artisan scout:flush "App\Models\Product"` and reload: the page reports *Out of sync*.
4. Run `php artisan scout:import "App\Models\Product"` and reload: back in sync.