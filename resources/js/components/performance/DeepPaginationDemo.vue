<script setup lang="ts">
import { computed } from "vue";
import { useDeepPagination } from "../../composables/useDeepPagination";

const {
    sort,
    size,
    items,
    cursor,
    pages,
    tookMs,
    loading,
    error,
    start,
    next,
    walkAll,
} = useDeepPagination();

const lastItems = computed(() => items.value.slice(-8));
const fromSizeWindow = computed(() => pages.value * size.value);
const averageMs = computed(() =>
    tookMs.value.length === 0
        ? 0
        : tookMs.value.reduce((sum, value) => sum + value, 0) /
          tookMs.value.length,
);
</script>

<template>
    <div>
        <p class="text-sm text-gray-600">
            With <code>from</code> + <code>size</code>, page 500 makes every
            shard sort 500 &times; size documents, and Elasticsearch refuses
            anything past 10,000. <code>search_after</code> continues from the
            last hit instead, so every page costs the same, and a
            <em>point in time</em> keeps the data stable while you page.
        </p>

        <div class="mt-4 flex flex-wrap items-end gap-3">
            <label class="text-sm">
                <span class="text-gray-600">Sort</span>
                <select
                    v-model="sort"
                    class="mt-1 block rounded border border-gray-300 px-3 py-1.5"
                >
                    <option value="price_asc">Price: low to high</option>
                    <option value="price_desc">Price: high to low</option>
                    <option value="rating_desc">Rating</option>
                    <option value="name_asc">Name</option>
                </select>
            </label>
            <label class="text-sm">
                <span class="text-gray-600">Page size</span>
                <select
                    v-model.number="size"
                    class="mt-1 block rounded border border-gray-300 px-3 py-1.5"
                >
                    <option
                        v-for="option in [10, 20, 50]"
                        :key="option"
                        :value="option"
                    >
                        {{ option }}
                    </option>
                </select>
            </label>
            <button
                type="button"
                class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="loading"
                @click="start"
            >
                Start
            </button>
            <button
                type="button"
                class="rounded border border-gray-300 px-4 py-2 text-sm disabled:opacity-50"
                :disabled="loading || cursor === null"
                @click="next"
            >
                Next page
            </button>
            <button
                type="button"
                class="rounded border border-gray-300 px-4 py-2 text-sm disabled:opacity-50"
                :disabled="loading"
                @click="walkAll"
            >
                Walk all pages
            </button>
        </div>

        <p
            v-if="error"
            class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <dl v-if="pages > 0" class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="rounded border border-gray-200 bg-white p-3">
                <dt class="text-xs uppercase text-gray-400">Pages fetched</dt>
                <dd class="mt-1 text-xl font-semibold">{{ pages }}</dd>
            </div>
            <div class="rounded border border-gray-200 bg-white p-3">
                <dt class="text-xs uppercase text-gray-400">Documents read</dt>
                <dd class="mt-1 text-xl font-semibold">{{ items.length }}</dd>
            </div>
            <div class="rounded border border-gray-200 bg-white p-3">
                <dt class="text-xs uppercase text-gray-400">
                    Sorted per shard<br />(from + size, this page)
                </dt>
                <dd class="mt-1 text-xl font-semibold">{{ fromSizeWindow }}</dd>
            </div>
            <div class="rounded border border-gray-200 bg-white p-3">
                <dt class="text-xs uppercase text-gray-400">
                    Sorted per shard<br />(search_after)
                </dt>
                <dd class="mt-1 text-xl font-semibold">{{ size }}</dd>
            </div>
        </dl>

        <p v-if="pages > 0" class="mt-2 text-xs text-gray-500">
            Average response: {{ averageMs.toFixed(1) }} ms.
            {{
                cursor === null
                    ? "The last page was reached and the point in time was closed."
                    : "More pages are available."
            }}
            With only 1000 products the speed difference is invisible; the
            window column shows what it costs at scale.
        </p>

        <ol v-if="lastItems.length > 0" class="mt-3 space-y-1 text-sm">
            <li
                v-for="(item, index) in lastItems"
                :key="item.id"
                class="flex justify-between rounded border border-gray-100 bg-white px-3 py-1.5"
            >
                <span
                    ><span class="mr-2 text-gray-400"
                        >{{
                            items.length - lastItems.length + index + 1
                        }}.</span
                    >{{ item.name }}</span
                >
                <span class="text-gray-600"
                    >{{ item.price.toFixed(2) }} &middot;
                    {{ item.rating }}</span
                >
            </li>
        </ol>
    </div>
</template>
