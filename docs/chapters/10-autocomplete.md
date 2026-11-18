# Chapter 10: Autocomplete

## What you will learn

- Edge n-grams: moving the cost of prefix matching from query time to index time.
- Using different analyzers for indexing and searching.
- Changing a live index with an incremental Elastic migration, and why existing documents need a re-index.
- Debouncing keystrokes and aborting stale requests in Vue.

## Where the code lives

| Layer     | File                                                              |
|-----------|-------------------------------------------------------------------|
| Migration | `elastic/migrations/*_add_autocomplete_to_products_index.php`     |
| Service   | `app/Services/Search/AutocompleteService.php`                     |
| Endpoint  | `GET /api/v1/autocomplete?q=`                                     |
| Composable| `resources/js/composables/useAutocomplete.ts`                     |
| Page      | `resources/js/pages/chapters/Chapter10Autocomplete.vue`           |

## How it works

`smartphone` is indexed by the `autocomplete_index` analyzer as `sm`, `sma`, `smar`, ... up to 15 letters.
The query text is analyzed by `autocomplete_search`, which only lowercases, so `smart` is a single token that
already exists in the index.

```php
Query::match()->field('name.autocomplete')->query('wire hea')->operator('and');
```

## Re-indexing after a mapping change

A new sub-field is only filled for documents indexed after it exists. After running the migration:

```bash
php artisan elastic:migrate
php artisan scout:import "App\Models\Product"
```

## Alternatives

- `search_as_you_type` field type: Elasticsearch builds the n-gram sub-fields for you.
- Completion suggester: very fast prefix lookups from an in-memory structure, but less flexible matching.

## Try it

1. Type `smart`, `wire hea` and `nova`, and use the arrow keys and Enter.
2. Type quickly and compare *Requests sent* with *Cancelled*.
3. Open `/mapping` and find the new `autocomplete` sub-field of `name`.