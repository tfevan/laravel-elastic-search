<?php

namespace App\Services\Elasticsearch;

use App\Exceptions\AliasOperationException;
use App\Models\Product;
use App\Services\Elasticsearch\Concerns\ConvertsResponses;
use Elastic\Client\ClientBuilderInterface;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Symfony\Component\HttpFoundation\Response;

class AliasReindexService
{
    use ConvertsResponses;

    public const ALIAS = 'catalog';

    /**
     * Create a new service instance.
     */
    public function __construct(private readonly ClientBuilderInterface $clientBuilder) {}

    /**
     * Name the index that follows the given one.
     */
    public function nextIndexName(?string $current): string
    {
        return self::ALIAS.'_v'.($current === null ? 1 : $this->versionOf($current) + 1);
    }

    /**
     * Describe the alias, the index behind it and every version that exists.
     */
    public function status(): array
    {
        $current = $this->currentIndex();

        return [
            'alias' => self::ALIAS,
            'index' => $current,
            'documents' => $current === null ? 0 : (int) $this->toArray($this->clientBuilder->default()->count(['index' => self::ALIAS]))['count'],
            'indices' => $this->indices($current),
        ];
    }

    /**
     * Build the next version, copy the documents and switch the alias in one atomic step.
     */
    public function reindex(): array
    {
        $client = $this->clientBuilder->default();
        $current = $this->currentIndex();
        $source = $current ?? (new Product)->searchableAs();
        $target = $this->nextIndexName($current);
        $steps = [];
        $copied = 0;

        $this->timed($steps, "Create {$target}", function () use ($client, $source, $target): void {
            $client->indices()->create(['index' => $target, 'body' => $this->template($source)]);
        });

        $this->timed($steps, "Copy documents from {$source}", function () use ($client, $source, $target, &$copied): void {
            $result = $this->toArray($client->reindex([
                'body' => ['source' => ['index' => $source], 'dest' => ['index' => $target]],
                'wait_for_completion' => true,
                'refresh' => true,
            ]));

            $copied = (int) $result['created'];
        });

        $this->timed($steps, 'Switch the alias', function () use ($current, $target): void {
            $this->switchAlias($current, $target);
        });

        $this->timed($steps, 'Delete old versions (the previous one is kept)', function () use ($current, $target): void {
            foreach ($this->indexNames() as $name) {
                if ($name !== $current && $name !== $target) {
                    $this->clientBuilder->default()->indices()->delete(['index' => $name]);
                }
            }
        });

        return [
            'from' => $source,
            'to' => $target,
            'copied' => $copied,
            'steps' => $steps,
            'status' => $this->status(),
        ];
    }

    /**
     * Point the alias back at the previous version.
     */
    public function rollback(): array
    {
        $current = $this->currentIndex();

        if ($current === null) {
            throw new AliasOperationException('The alias does not exist yet. Run a reindex first.');
        }

        $older = array_filter($this->indexNames(), fn (string $name): bool => $this->versionOf($name) < $this->versionOf($current));

        if ($older === []) {
            throw new AliasOperationException('There is no previous version to roll back to.');
        }

        $this->switchAlias($current, end($older));

        return $this->status();
    }

    /**
     * Delete every demo index so the demo can start over.
     */
    public function reset(): array
    {
        foreach ($this->indexNames() as $name) {
            $this->clientBuilder->default()->indices()->delete(['index' => $name]);
        }

        return $this->status();
    }

    /**
     * Find the index the alias points to, or null when the alias does not exist.
     */
    private function currentIndex(): ?string
    {
        try {
            $aliases = $this->toArray($this->clientBuilder->default()->indices()->getAlias(['name' => self::ALIAS]));
        } catch (ClientResponseException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw $exception;
        }

        $name = array_key_first($aliases);

        return $name === null ? null : (string) $name;
    }

    /**
     * Move the alias from one index to another in a single atomic request.
     */
    private function switchAlias(?string $from, string $to): void
    {
        $actions = [['add' => ['index' => $to, 'alias' => self::ALIAS]]];

        if ($from !== null) {
            array_unshift($actions, ['remove' => ['index' => $from, 'alias' => self::ALIAS]]);
        }

        $this->clientBuilder->default()->indices()->updateAliases(['body' => ['actions' => $actions]]);
    }

    /**
     * Copy the shards, replicas, analysis settings and mapping of an index.
     */
    private function template(string $index): array
    {
        $indices = $this->clientBuilder->default()->indices();

        $settings = array_values($this->toArray($indices->getSettings(['index' => $index])))[0]['settings']['index'] ?? [];
        $mappings = array_values($this->toArray($indices->getMapping(['index' => $index])))[0]['mappings'] ?? [];

        $copy = [
            'number_of_shards' => $settings['number_of_shards'] ?? '1',
            'number_of_replicas' => $settings['number_of_replicas'] ?? '0',
        ];

        if (isset($settings['analysis'])) {
            $copy['analysis'] = $settings['analysis'];
        }

        return ['settings' => $copy, 'mappings' => $mappings];
    }

    /**
     * List the names of all versions, oldest first.
     */
    private function indexNames(): array
    {
        $rows = $this->toArray($this->clientBuilder->default()->cat()->indices([
            'index' => self::ALIAS.'_v*',
            'format' => 'json',
            'h' => 'index',
        ]));

        $names = array_column($rows, 'index');

        usort($names, fn (string $a, string $b): int => $this->versionOf($a) <=> $this->versionOf($b));

        return $names;
    }

    /**
     * List the versions with their size and whether the alias points to them.
     */
    private function indices(?string $current): array
    {
        $rows = $this->toArray($this->clientBuilder->default()->cat()->indices([
            'index' => self::ALIAS.'_v*',
            'format' => 'json',
            'h' => 'index,docs.count,store.size',
        ]));

        usort($rows, fn (array $a, array $b): int => $this->versionOf($a['index']) <=> $this->versionOf($b['index']));

        return array_map(fn (array $row): array => [
            'name' => $row['index'],
            'documents' => (int) $row['docs.count'],
            'size' => $row['store.size'],
            'current' => $row['index'] === $current,
        ], $rows);
    }

    /**
     * Read the version number from an index name.
     */
    private function versionOf(string $index): int
    {
        return (int) substr($index, strlen(self::ALIAS) + 2);
    }

    /**
     * Run a step and record how long it took.
     */
    private function timed(array &$steps, string $name, callable $callback): void
    {
        $started = hrtime(true);

        $callback();

        $steps[] = ['step' => $name, 'ms' => round((hrtime(true) - $started) / 1_000_000, 1)];
    }
}
