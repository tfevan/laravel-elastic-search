# Chapter 1: Connection & Health

## What you will learn

- How Laravel connects to Elasticsearch (`config/elastic.client.php`, `ELASTIC_HOST`).
- How to read cluster status, server version and the index list with the official PHP client.
- Why a single-node cluster reports `yellow`.

## Where the code lives

| Layer    | File                                                     |
|----------|----------------------------------------------------------|
| Service  | `app/Services/Elasticsearch/ClusterHealthService.php`    |
| Endpoint | `GET /api/v1/health` (`HealthController`)                |
| Page     | `resources/js/pages/chapters/Chapter01Health.vue`        |
| Tests    | `tests/Feature/Api/HealthTest.php`                       |

## Try it

```bash
php artisan migrate:fresh --seed
curl http://127.0.0.1:8000/api/v1/health
```

Stop Elasticsearch and reload the page to see the `503` error state.