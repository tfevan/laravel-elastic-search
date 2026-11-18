# Chapter 12: Faceted Search

## What you will learn

- Aggregations: `terms` (counts per value), `range` (buckets), `avg` and `stats` (metrics).
- Why a naive filter breaks multi-select facets, and how `post_filter` plus per-facet `filter`
  aggregations fix it.
- Reading the hits straight from `_source` when the database is not needed.

## Where the code lives

| Layer    | File                                                          |
|----------|---------------------------------------------------------------|
| Service  | `app/Services/Search/FacetService.php`                        |
| Endpoint | `GET /api/v1/facets?q=&categories[]=&brands[]=&price_range=&min_rating=` |
| Page     | `resources/js/pages/chapters/Chapter12Facets.vue`             |
| Tests    | `tests/Feature/FacetQueryTest.php`                            |

## The idea

Put selected filters in the query and every aggregation shrinks with them: after ticking `Audio` the
category list would only show `Audio`. Instead:

1. `query`: only the search text.
2. `post_filter`: all selected facets. It filters the **hits** after aggregations are computed.
3. One aggregation per facet, wrapped in a `filter` aggregation holding every selected facet **except its own**.

```json
{
  "query": { "match_all": {} },
  "post_filter": { "bool": { "filter": [
    { "terms": { "category": ["Audio"] } },
    { "terms": { "brand": ["Apex"] } }
  ] } },
  "aggs": {
    "categories": {
      "filter": { "bool": { "filter": [ { "terms": { "brand": ["Apex"] } } ] } },
      "aggs": { "values": { "terms": { "field": "category", "min_doc_count": 0 } } }
    }
  }
}
```

The category counts respect the brand choice but not the category choice, so you can tick a second
category and see what it would add.

## Aggregations used

| Aggregation | Used for                                   |
|-------------|--------------------------------------------|
| `terms`     | category and brand counts                  |
| `range`     | price buckets and "4 stars and up"         |
| `avg`       | average rating of the current results      |
| `stats`     | cheapest and most expensive product        |

`min_doc_count: 0` keeps values with no match in the list, so a selected value never disappears.

## Try it

1. Tick `Audio`: category counts stay, brand counts drop to the brands that sell audio products.
2. Tick a second category: results are the union, brand counts follow.
3. Add a price range and a rating: the summary cards update.
4. Open the request panel and compare it with the example above.