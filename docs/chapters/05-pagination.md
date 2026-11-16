# Chapter 5: Pagination

## What you will learn

- How `paginate()` maps pages to Elasticsearch `from` and `size`.
- How the total hit count drives a page navigation component.
- Why Elasticsearch limits `from + size` to 10,000 and what to do about it.

## Where the code lives

| Layer    | File                                                         |
|----------|--------------------------------------------------------------|
| Service  | `app/Services/Search/ProductPaginationService.php`           |
| Request  | `app/Http/Requests/PaginationRequest.php`                    |
| Endpoint | `GET /api/v1/pagination?q=&page=&per_page=`                  |
| Helper   | `resources/js/utils/pagination.ts`                           |
| Page     | `resources/js/pages/chapters/Chapter05Pagination.vue`        |

## How it works

```php
$paginator = Product::search('wireless')->paginate(10);

$paginator->items();       // the models of the current page
$paginator->total();       // total number of matches
$paginator->currentPage();
```

Page 3 with 10 results per page is requested as `from = 20`, `size = 10`.

## The 10,000 result window

`index.max_result_window` defaults to 10,000. A request with `from + size` above that fails, because
Elasticsearch must sort `from + size` hits on every shard. The API validates this upfront and returns
a `422` with an explanation. Deep pagination with `search_after` is covered in chapter 14.

## Compare with chapter 2

Chapter 2 paginates the database table. Here the pages come from Elasticsearch, so they are
relevance-ordered and respect the search text.

## Try it

Search for a word, move between pages and change the page size. Then open page 1001 at 10 per page
to see the result window message.