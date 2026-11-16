# Chapter 4: Filters & Sorting

## What you will learn

- `where` and `whereIn` add exact-match filters to a search.
- `orderBy` sorts the results instead of ranking them by relevance.
- Why filters and sorts target `.keyword` sub-fields on `text` fields.

## Where the code lives

| Layer    | File                                                       |
|----------|------------------------------------------------------------|
| Filters  | `app/Data/ProductFilters.php`                              |
| Service  | `app/Services/Search/ProductFilterService.php`             |
| Endpoint | `GET /api/v1/filters` and `GET /api/v1/filters/options`    |
| Page     | `resources/js/pages/chapters/Chapter04Filters.vue`         |

## How it works

```php
Product::search('')
    ->where('category.keyword', 'Audio')
    ->whereIn('brand.keyword', ['Apex', 'Nova'])
    ->orderBy('price', 'asc')
    ->take(20)
    ->get();
```

Dynamic mapping stores every string twice: as an analyzed `text` field (`category`, lowercased and
split into words, good for searching) and as an exact `keyword` sub-field (`category.keyword`, good
for filtering, sorting and aggregations). Exact-match filters and sorts need the keyword version.

Numeric fields such as `price` and `rating` need no sub-field.

## Try it

Pick a category, tick two brands, set a price range and sort by price. Compare the Scout snippet on
the page with the results. Chapter 7 shows how to define the mapping yourself.