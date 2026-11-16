# Chapter 7: Mapping & Analyzers

## What you will learn

- Why an explicit mapping beats dynamic mapping, and what `dynamic: strict` protects you from.
- `text` vs `keyword`: analyzed full-text search vs exact matching, sorting and aggregations.
- Custom analyzers, token filters and synonyms, and why synonyms belong to the search analyzer.
- Debugging with the `_analyze` API.
- Managing index schemas as versioned migrations with Elastic Migrations.

## Where the code lives

| Layer     | File                                                        |
|-----------|-------------------------------------------------------------|
| Migration | `elastic/migrations/*_create_products_index.php`            |
| Service   | `app/Services/Elasticsearch/MappingService.php`             |
| Endpoint  | `GET /api/v1/mapping`, `GET /api/v1/mapping/analyze`        |
| Page      | `resources/js/pages/chapters/Chapter07Mapping.vue`          |

## Commands

```bash
php artisan elastic:migrate            # run pending index migrations
php artisan elastic:migrate:rollback   # undo the last batch
php artisan elastic:status             # show which migrations ran
```

## Field types used

| Field                           | Type            | Why                                               |
|---------------------------------|-----------------|---------------------------------------------------|
| `name`, `description`           | `text`          | full-text search with the custom analyzers        |
| `name.keyword`                  | `keyword`       | exact value for sorting                           |
| `category`, `brand`, `tags`     | `keyword`       | exact match filters (case-sensitive)              |
| `price`                         | `scaled_float`  | compact storage for money, factor 100             |
| `rating`                        | `float`         |                                                   |
| `created_at`                    | `date`          |                                                   |

## Analyzers

| Analyzer         | Pipeline                                          | Used for                |
|------------------|---------------------------------------------------|-------------------------|
| `product_index`  | standard tokenizer, lowercase, asciifolding       | indexing text fields    |
| `product_search` | the above plus the synonym filter                 | searching text fields   |

Synonyms are applied at search time only. Changing the synonym list then needs no re-indexing.

## Try it

1. Open `/mapping` and read the table.
2. Analyze `mobile` and compare the four analyzers.
3. Open `/search` and search for `mobile`: smartphones appear although the word is not in their name.
4. Open `/search` with `brand:apex` (lowercase) and then `brand:Apex`: keyword fields are exact.