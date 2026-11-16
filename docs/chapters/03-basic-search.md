# Chapter 3: Basic Search

## What you will learn

- How `Product::search()` turns text into an Elasticsearch query.
- The query string language: fields, `AND` / `OR` / `NOT`, phrases, wildcards and ranges.
- How invalid syntax is reported instead of crashing the request.

## Where the code lives

| Layer    | File                                                      |
|----------|-----------------------------------------------------------|
| Service  | `app/Services/Search/ProductSearchService.php`            |
| Endpoint | `GET /api/v1/search?q=...` (`SearchController`)           |
| Page     | `resources/js/pages/chapters/Chapter03Search.vue`         |
| Tests    | `tests/Feature/Api/SearchApiTest.php`                     |

## How it works

```php
Product::search('brand:Apex AND category:Audio')->take(20)->get();
```

The Elastic driver wraps the text in a `query_string` query inside a `bool` query. An empty string
becomes a `match_all` query. The result is a collection of Eloquent models loaded from the database
by the ids Elasticsearch returned.

## Query string cheatsheet

| Query                          | Meaning                              |
|--------------------------------|--------------------------------------|
| `wireless`                     | the word in any field               |
| `"wireless headphones"`        | the exact phrase                     |
| `brand:Apex AND category:Audio`| both conditions                      |
| `name:(smartwatch OR tracker)` | either word in `name`                |
| `name:head*`                   | wildcard                             |
| `price:[100 TO 500]`           | numeric range                        |
| `rating:>=4.5`                 | comparison                           |
| `tags:gaming AND NOT brand:Nova` | exclusion                         |

## Try it

Open `/search`, click each example and compare the results. Then type `name:(phone` to trigger the
syntax error state.