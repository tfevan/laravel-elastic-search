# Chapter 9: Fuzzy Search

## What you will learn

- What edit distance (Levenshtein distance) is and how `fuzziness` uses it.
- Why `AUTO` is the recommended default and what `prefix_length` does for performance.
- How to build a "Did you mean?" suggestion with the phrase suggester.

## Where the code lives

| Layer    | File                                                  |
|----------|-------------------------------------------------------|
| Service  | `app/Services/Search/FuzzyService.php`                |
| Endpoint | `GET /api/v1/fuzzy?q=&fuzziness=&prefix_length=`      |
| Page     | `resources/js/pages/chapters/Chapter09Fuzzy.vue`      |
| Tests    | `tests/Feature/FuzzyQueryTest.php`                    |

## The query

```php
use Elastic\ScoutDriverPlus\Support\Query;

$query = Query::multiMatch()
    ->fields(['name^3', 'description'])
    ->query('headphnes')
    ->fuzziness('AUTO')
    ->prefixLength(1);

$result = Product::searchQuery($query)->execute();
```

| Fuzziness | Meaning                                   |
|-----------|-------------------------------------------|
| `0`       | exact match only                          |
| `1`       | up to 1 edit (`headphnes` to `headphones`) |
| `2`       | up to 2 edits                             |
| `AUTO`    | 0 edits for 1-2 letters, 1 for 3-5, 2 for 6+ |

An edit is inserting, deleting, replacing or swapping neighbouring letters.

## Did you mean?

```json
{
  "suggest": {
    "did_you_mean": {
      "text": "wirless chargr",
      "phrase": { "field": "name", "analyzer": "product_index", "direct_generator": [{ "field": "name", "suggest_mode": "missing" }] }
    }
  }
}
```

The suggester looks at the terms that exist in the index and proposes the most likely correction of the
whole phrase. `suggest_mode: missing` only corrects words that are not in the index.

## Costs

Fuzzy queries expand every term into many candidates. Keep `prefix_length` at 1 or 2 and prefer `AUTO`
over a fixed `2` on short words.

## Try it

1. Search `headphnes` with fuzziness `0`: nothing. Switch to `AUTO`: results appear.
2. Compare the four counters for `smatwatch`.
3. Set the exact prefix to 3 for `lapto`: results narrow down or disappear.
4. Click the "Did you mean" link.