# Chapter 8: Relevance & Bool Queries

## What you will learn

- How Elasticsearch scores matches (BM25) and what a `_score` means.
- The four roles of a `bool` query: `must`, `should`, `filter` and `must_not`.
- Field boosting with `multi_match` (`name^3`).
- Reading a score explanation to understand why a result ranks where it does.

## Where the code lives

| Layer    | File                                                              |
|----------|-------------------------------------------------------------------|
| Service  | `app/Services/Search/RelevanceService.php`                        |
| Endpoint | `GET /api/v1/relevance`, `GET /api/v1/relevance/{product}/explain` |
| Page     | `resources/js/pages/chapters/Chapter08Relevance.vue`              |
| Tests    | `tests/Feature/RelevanceQueryTest.php`                            |

## The query

```php
use Elastic\ScoutDriverPlus\Support\Query;

$query = Query::bool()
    ->must(Query::multiMatch()->fields(['name^3', 'description^1'])->query('wireless headphones'))
    ->should(Query::range()->field('rating')->gte(4.5)->boost(2))
    ->filter(Query::term()->field('category')->value('Audio'))
    ->mustNot(Query::term()->field('brand')->value('Nova'));

$result = Product::searchQuery($query)->size(10)->execute();

foreach ($result->hits() as $hit) {
    $hit->model();  // the Eloquent model
    $hit->score();  // the relevance score
}
```

| Clause     | Matches required | Affects score |
|------------|------------------|---------------|
| `must`     | yes              | yes           |
| `should`   | no               | yes, when it matches |
| `filter`   | yes              | no            |
| `must_not` | excluded         | no            |

Use `filter` for anything that is a yes/no condition (category, price range, status). Elasticsearch
can cache filters and skips the scoring work.

## Reading an explanation

The score tree shows the BM25 parts: `tf` (how often the term appears in the field), `idf` (how rare the
term is across all documents) and the field length normalization. A `^3` boost multiplies the field's
contribution by 3.

## Try it

1. Search `wireless headphones` with the "Name matters most" preset and open **Why this score?**
2. Switch to "Equal weight" and see the order change.
3. Switch the operator to *all words (and)*: results that only match one word disappear.
4. Tick the `should` options: top-rated and popular products move up but nothing disappears.
5. Add a category filter: the scores stay the same, only the set of results shrinks.