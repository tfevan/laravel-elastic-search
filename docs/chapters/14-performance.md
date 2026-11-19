# Chapter 14: Performance

## What you will learn

- Why `from` + `size` gets slower with depth and stops at 10,000, and how `search_after` with a point in time fixes it.
- How queued indexing moves Elasticsearch out of the request, and what eventual consistency means.
- How an alias lets you rebuild an index with zero downtime, and how to roll back.

## Where the code lives

| Topic      | Files                                                                                           |
|------------|-------------------------------------------------------------------------------------------------|
| Deep paging| `app/Services/Search/DeepPaginationService.php`, `GET /api/v1/performance/deep-pagination`      |
| Queue      | `app/Http/Controllers/Api/IndexingController.php`, `config/scout.php` (`queue`)                 |
| Alias      | `app/Services/Elasticsearch/AliasReindexService.php`, `/api/v1/performance/catalog`             |
| Page       | `resources/js/pages/chapters/Chapter14Performance.vue`                                          |

## 1. search_after and point in time

```json
{
  "size": 20,
  "sort": [ { "price": "asc" } ],
  "pit": { "id": "<pit id>", "keep_alive": "2m" },
  "search_after": [ 49.99, 4294967299 ]
}
```

- `search_after` takes the `sort` values of the last hit of the previous page.
- The point in time freezes a view of the index, so documents added or deleted while paging do not shift pages.
- With a PIT, Elasticsearch adds an implicit `_shard_doc` tiebreaker, so products with the same price keep a stable order.
- You cannot jump to page N. It suits "next page", infinite scroll and exports, not numbered pagers.
- The cursor is encrypted, so the client cannot read or change the point in time id.

| Technique            | Jump to page N | Depth limit | Cost per page         |
|----------------------|----------------|-------------|-----------------------|
| `from` + `size`      | yes            | 10,000      | grows with the depth  |
| `search_after` + PIT | no             | none        | constant              |

## 2. Queued indexing

```env
SCOUT_QUEUE=true
```

Scout then dispatches a job per change instead of calling Elasticsearch in the request. Run a worker
(`php artisan queue:work`, already part of `composer run dev`). Related Scout options:

- `SCOUT_AFTER_COMMIT=true` queues indexing only after the database transaction commits.
- `config/scout.php` `queue` can name a connection and queue for the index jobs.
- Failed jobs land in `failed_jobs`: watch them, or the index silently drifts from the database.

Trade-off: the HTTP response is faster, but a search right after a change may not show it yet.

## 3. Alias based reindexing

```text
catalog (alias) ──> catalog_v1   (live)
                    catalog_v2   (being built: new mapping, documents copied with _reindex)
```

1. Create `catalog_v2` with the new settings and mapping.
2. `POST _reindex` from `catalog_v1` to `catalog_v2`.
3. One atomic `POST _aliases` request: `remove` the alias from v1 and `add` it to v2.
4. Keep v1 for a quick rollback, delete older versions.

### Moving the real `products` index to an alias

Elasticsearch cannot have an index and an alias with the same name, but one `_aliases` request can
replace the index with an alias atomically using the `remove_index` action:

```json
{ "actions": [
  { "remove_index": { "index": "products" } },
  { "add": { "index": "products_v1", "alias": "products" } }
] }
```

Do this once (after copying `products` into `products_v1`); later rebuilds are zero-downtime. Remember that the
`create_products_index` Elastic migration drops and recreates a concrete `products` index, so it would have to
be adapted for the alias setup.

## Try it

1. Press *Walk all pages* and compare the two "sorted per shard" columns.
2. Run *Re-index 200 products* in both queue modes and compare the request times.
3. Press *Reindex* twice, then *Roll back*, and watch which version is `current`.