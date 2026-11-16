# Chapter 6: Searchable Data

## What you will learn

- What `toSearchableArray()` sends to Elasticsearch and how to shape it.
- How the index name (`searchableAs()`) and document id (`getScoutKey()`) are chosen.
- How `shouldBeSearchable()` keeps records out of the index and removes them when they change.
- How `->raw()` exposes the Elasticsearch response, including relevance scores.

## Where the code lives

| Layer    | File                                                        |
|----------|-------------------------------------------------------------|
| Model    | `app/Models/Product.php`                                    |
| Endpoint | `GET /api/v1/documents/{product}`, `GET /api/v1/documents/raw` |
| Toggle   | `PATCH /api/v1/products/{product}/activation`               |
| Page     | `resources/js/pages/chapters/Chapter06Documents.vue`        |

## The four hooks

```php
public function toSearchableArray(): array { /* fields stored in the index */ }
public function searchableAs(): string      { /* index name, defaults to the table name */ }
public function getScoutKey(): mixed        { /* document id, defaults to the primary key */ }
public function shouldBeSearchable(): bool  { return $this->is_active; }
```

When a product is saved with `shouldBeSearchable()` returning `false`, Scout deletes its document.
`scout:import` also skips such records. To namespace indexes per environment use `SCOUT_PREFIX`
instead of overriding `searchableAs()`.

## Raw results

```php
$result = Product::search('wireless')->raw();

$result->total();
$result->hits(); // each hit has score() and document()
```

## Try it

1. Inspect product 1 and compare the two JSON panels.
2. Click **Deactivate**: the stored document disappears. Search for its name in the raw search.
3. Click **Activate**: the document is back.
4. Run `php artisan scout:flush "App\Models\Product"` then `scout:import` and notice that inactive
   products are not imported.