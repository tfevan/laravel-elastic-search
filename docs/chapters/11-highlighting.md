# Chapter 11: Highlighting

## What you will learn

- How the highlighter reports which terms matched, per field.
- `number_of_fragments` and `fragment_size` for short and long fields.
- Why highlights also work with synonyms and fuzzy matches.
- How to render highlighted text without `v-html` (and without an XSS hole).

## Where the code lives

| Layer    | File                                                       |
|----------|------------------------------------------------------------|
| Service  | `app/Services/Search/HighlightService.php`                 |
| Endpoint | `GET /api/v1/highlight?q=&fuzzy=`                          |
| Parser   | `resources/js/utils/highlight.ts` (+ unit tests)           |
| Page     | `resources/js/pages/chapters/Chapter11Highlight.vue`       |

## The query

```php
$result = Product::searchQuery($query)
    ->highlight('name', ['number_of_fragments' => 0, 'pre_tags' => ['[[hl]]'], 'post_tags' => ['[[/hl]]']])
    ->highlight('description', ['number_of_fragments' => 1, 'fragment_size' => 120, 'pre_tags' => ['[[hl]]'], 'post_tags' => ['[[/hl]]']])
    ->execute();

$hit->highlight()?->snippets('name')?->first(); // "Apex [[hl]]Smartphone[[/hl]] AB-123"
```

| Parameter                   | Effect                                                      |
|-----------------------------|-------------------------------------------------------------|
| `number_of_fragments: 0`    | return the whole field, highlighted (good for short fields) |
| `number_of_fragments: 1`    | return one snippet around the best match                    |
| `fragment_size`             | approximate snippet length in characters                    |
| `pre_tags` / `post_tags`    | the markers placed around each match                        |

## Security

Highlight snippets contain the original, unescaped field text. Never pass them to `v-html`. This chapter
uses text markers instead and splits the snippet into `{ text, highlighted }` segments, which Vue prints with
`{{ }}` and `<mark>`. The parser has unit tests, including one with an `<img onerror>` payload.

## Try it

1. Search `mobile`: the word is not in the name, yet `Smartphone` is highlighted (search-time synonyms).
2. Search `headphnes` with *Typo tolerant* on.
3. Create a product named `<b>Test</b> phone` in chapter 2, then search `phone`: the tags show as text.