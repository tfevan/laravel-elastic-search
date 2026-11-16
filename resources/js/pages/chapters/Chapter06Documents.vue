<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useDocumentInspector } from "../../composables/useDocumentInspector";
import { useRawSearch } from "../../composables/useRawSearch";

const { inspection, loading, error, inspect, setActive } =
    useDocumentInspector();
const raw = useRawSearch();

const productId = ref<number>(1);
const query = ref<string>("wireless");

const pretty = (value: unknown): string => JSON.stringify(value, null, 2);

onMounted(() => {
    void inspect(productId.value);
});
</script>

<template>
    <section class="max-w-5xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 6 &middot; Beginner
        </p>
        <h1 class="mt-1 text-3xl font-bold">Searchable Data</h1>
        <p class="mt-3 text-gray-600">
            Scout indexes whatever <code>toSearchableArray()</code> returns,
            under the name <code>searchableAs()</code>, with
            <code>getScoutKey()</code> as the document id.
            <code>shouldBeSearchable()</code> decides which records are indexed
            at all.
        </p>

        <h2 class="mt-8 text-xl font-semibold">1. From model to document</h2>
        <form
            class="mt-3 flex items-end gap-3"
            @submit.prevent="inspect(productId)"
        >
            <label class="text-sm">
                <span class="text-gray-600">Product ID</span>
                <input
                    v-model.number="productId"
                    type="number"
                    min="1"
                    class="mt-1 block w-32 rounded border border-gray-300 px-3 py-1.5"
                />
            </label>
            <button
                type="submit"
                class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="loading"
            >
                Inspect
            </button>
        </form>

        <p
            v-if="error"
            class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <template v-if="inspection">
            <dl class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">
                        searchableAs()
                    </dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ inspection.index }}
                    </dd>
                </div>
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">
                        getScoutKey()
                    </dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ inspection.scout_key }}
                    </dd>
                </div>
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">
                        shouldBeSearchable()
                    </dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ inspection.should_be_searchable }}
                    </dd>
                </div>
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <button
                            type="button"
                            class="rounded border px-3 py-1 text-xs font-medium"
                            :class="
                                inspection.is_active
                                    ? 'border-red-300 text-red-700'
                                    : 'border-green-300 text-green-700'
                            "
                            @click="setActive(productId, !inspection.is_active)"
                        >
                            {{
                                inspection.is_active ? "Deactivate" : "Activate"
                            }}
                        </button>
                    </dd>
                </div>
            </dl>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold">toSearchableArray()</h3>
                    <pre
                        class="mt-1 max-h-96 overflow-auto rounded bg-gray-900 p-4 text-xs text-green-300"
                        >{{ pretty(inspection.searchable_array) }}</pre
                    >
                </div>
                <div>
                    <h3 class="text-sm font-semibold">
                        Stored in Elasticsearch
                    </h3>
                    <pre
                        v-if="inspection.indexed_document"
                        class="mt-1 max-h-96 overflow-auto rounded bg-gray-900 p-4 text-xs text-green-300"
                        >{{ pretty(inspection.indexed_document) }}</pre
                    >
                    <p
                        v-else
                        class="mt-1 rounded border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800"
                    >
                        Not indexed. The product is inactive, so Scout removed
                        its document from the index.
                    </p>
                </div>
            </div>
        </template>

        <h2 class="mt-12 text-xl font-semibold">2. The raw response</h2>
        <p class="mt-2 text-sm text-gray-600">
            <code>Product::search($query)->raw()</code> skips the database and
            returns what Elasticsearch found, including each hit's relevance
            <code>score</code>.
        </p>
        <form class="mt-3 flex gap-3" @submit.prevent="raw.search(query)">
            <input
                v-model="query"
                type="text"
                class="w-full rounded border border-gray-300 px-4 py-2"
            />
            <button
                type="submit"
                class="rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="raw.loading.value"
            >
                Search
            </button>
        </form>

        <p
            v-if="raw.error.value"
            class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
            {{ raw.error.value }}
        </p>

        <template v-if="raw.result.value">
            <p class="mt-4 text-sm text-gray-600">
                {{ raw.result.value.total }} total match(es), showing the top
                {{ raw.result.value.hits.length }}.
            </p>
            <ul class="mt-2 space-y-3">
                <li
                    v-for="hit in raw.result.value.hits"
                    :key="hit.id"
                    class="rounded border border-gray-200 bg-white p-4"
                >
                    <p class="text-sm">
                        <span class="font-medium">_id {{ hit.id }}</span>
                        <span
                            class="ml-3 rounded bg-indigo-50 px-2 py-0.5 text-xs text-indigo-700"
                            >_score {{ hit.score?.toFixed(3) ?? "n/a" }}</span
                        >
                    </p>
                    <pre
                        class="mt-2 overflow-x-auto rounded bg-gray-900 p-3 text-xs text-green-300"
                        >{{ pretty(hit.source) }}</pre
                    >
                </li>
            </ul>
        </template>

        <p class="mt-8 text-xs text-gray-500">
            A change can take about a second to appear in search results because
            Elasticsearch refreshes its index periodically. Set
            <code>refresh_documents</code> in
            <code>config/elastic.scout_driver.php</code>
            to refresh immediately (useful in tests, costly in production).
        </p>
    </section>
</template>
