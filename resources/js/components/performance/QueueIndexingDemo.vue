<script setup lang="ts">
import { useQueueIndexing } from "../../composables/useQueueIndexing";

const { status, runs, loading, error, refresh, touch } = useQueueIndexing();
</script>

<template>
    <div>
        <p class="text-sm text-gray-600">
            By default Scout talks to Elasticsearch inside your request: saving
            a model waits for the index to be updated. With
            <code>SCOUT_QUEUE=true</code> the model is saved and a job indexes
            it later. The request gets faster, at the price of a short delay
            before search reflects the change (eventual consistency).
        </p>

        <dl v-if="status" class="mt-4 grid grid-cols-3 gap-3">
            <div
                class="rounded border p-3"
                :class="
                    status.queued
                        ? 'border-indigo-300 bg-indigo-50'
                        : 'border-gray-200 bg-white'
                "
            >
                <dt class="text-xs uppercase text-gray-400">Indexing mode</dt>
                <dd class="mt-1 text-lg font-semibold">
                    {{ status.queued ? "Queued" : "Synchronous" }}
                </dd>
            </div>
            <div class="rounded border border-gray-200 bg-white p-3">
                <dt class="text-xs uppercase text-gray-400">
                    Queue connection
                </dt>
                <dd class="mt-1 text-lg font-semibold">
                    {{ status.queue_connection }}
                </dd>
            </div>
            <div class="rounded border border-gray-200 bg-white p-3">
                <dt class="text-xs uppercase text-gray-400">Jobs waiting</dt>
                <dd class="mt-1 text-lg font-semibold">
                    {{ status.pending_jobs ?? "n/a" }}
                </dd>
            </div>
        </dl>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button
                v-for="count in [10, 50, 200]"
                :key="count"
                type="button"
                class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="loading"
                @click="touch(count)"
            >
                Re-index {{ count }} products
            </button>
            <button
                type="button"
                class="rounded border border-gray-300 px-4 py-2 text-sm"
                @click="refresh"
            >
                Refresh status
            </button>
        </div>

        <p
            v-if="error"
            class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <table
            v-if="runs.length > 0"
            class="mt-4 w-full overflow-hidden rounded border border-gray-200 bg-white text-left text-sm"
        >
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-2">Mode</th>
                    <th class="px-4 py-2">Products</th>
                    <th class="px-4 py-2">Request time</th>
                    <th class="px-4 py-2">Jobs waiting</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(run, index) in runs"
                    :key="index"
                    class="border-t border-gray-100"
                >
                    <td class="px-4 py-2">
                        {{ run.queued ? "Queued" : "Synchronous" }}
                    </td>
                    <td class="px-4 py-2">{{ run.touched }}</td>
                    <td class="px-4 py-2 font-medium">
                        {{ run.elapsed_ms }} ms
                    </td>
                    <td class="px-4 py-2">{{ run.pending_jobs ?? "n/a" }}</td>
                </tr>
            </tbody>
        </table>

        <div
            class="mt-4 rounded border border-gray-200 bg-white p-4 text-sm text-gray-600"
        >
            <p class="font-semibold text-gray-900">Try the comparison</p>
            <ol class="mt-2 list-inside list-decimal space-y-1">
                <li>
                    Run <em>Re-index 50 products</em> in synchronous mode and
                    note the request time.
                </li>
                <li>
                    In <code>.env</code> set <code>SCOUT_QUEUE=true</code>, then
                    restart <code>composer run dev</code> (it already runs a
                    queue worker).
                </li>
                <li>
                    Run it again: the request is much faster, and the waiting
                    jobs count drops to 0 as the worker catches up.
                </li>
                <li>
                    Set <code>SCOUT_QUEUE=false</code> again when you are done.
                </li>
            </ol>
        </div>
    </div>
</template>
