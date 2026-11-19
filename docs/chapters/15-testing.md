# Chapter 15: Testing

## What you will learn

- Why a search feature needs tests against a real cluster, not only mocks.
- How a separate index prefix keeps tests away from development data.
- How CI runs Elasticsearch as a service container.

## Layers

| Layer       | Tool                | Command                   | Elasticsearch |
|-------------|---------------------|---------------------------|---------------|
| Unit        | Vitest              | `npm test`                | no            |
| Feature     | PHPUnit, mocks      | `php artisan test`        | no            |
| Integration | PHPUnit, real index | `composer test:integration` | yes         |

## Running the integration tests locally

1. Start Elasticsearch (local install or `docker compose up -d elasticsearch`).
2. Run `composer test:integration`.

`phpunit.integration.xml` forces `SCOUT_PREFIX=test_`, so the tests use `test_products` and `test_stores`.
The base class `Tests\Integration\ElasticsearchTestCase` refuses to run without that prefix, skips locally when
Elasticsearch is down and fails in CI.

## Rules for good search tests

- Refresh before asserting: documents are searchable only after a refresh.
- Start every test from empty indices.
- Assert behaviour (this query finds that product), not scores.
- Keep mocks for HTTP contracts and error handling, use the real cluster for mappings, analyzers and paging.

## CI

The `integration` job starts `elasticsearch:9.0.0` as a service container with security disabled,
waits for the health check and runs `phpunit --configuration phpunit.integration.xml`.