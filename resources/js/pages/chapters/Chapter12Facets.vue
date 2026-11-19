<script setup lang="ts">
import { computed, reactive, ref, watch } from "vue";
import FacetList from "../../components/FacetList.vue";
import { useFacets } from "../../composables/useFacets";
import type { FacetBucket, FacetSelection } from "../../types/facets";

const { result, loading, error, search } = useFacets();

const selection = reactive<FacetSelection>({
    q: "",
    categories: [],
    brands: [],
    priceRange: null,
    minRating: null,
});
const draft = ref<string>("");

watch(selection, () => void search(selection), { deep: true, immediate: true });

function toggleMany(list: string[], key: string): void {
    const index = list.indexOf(key);

    if (index === -1) {
        list.push(key);
    } else {
        list.splice(index, 1);
    }
}

function toggleOne(current: string | null, key: string): string | null {
    return current === key ? null : key;
}

function labelOf(buckets: FacetBucket[] | undefined, key: string): string {
    return buckets?.find((bucket) => bucket.key === key)?.label ?? key;
}

const chips = computed(() => {
    const facets = result.value?.facets;
    const items: { label: string; remove: () => void }[] = [];

    selection.categories.forEach((category) =>
        items.push({
            label: category,
            remove: () => toggleMany(selection.categories, category),
        }),
    );
    selection.brands.forEach((brand) =>
        items.push({
            label: brand,
            remove: () => toggleMany(selection.brands, brand),
        }),
    );

    if (selection.priceRange !== null) {
        items.push({
            label: `Price: ${labelOf(facets?.price_ranges, selection.priceRange)}`,
            remove: () => (selection.priceRange = null),
        });
    }

    if (selection.minRating !== null) {
        items.push({
            label: labelOf(facets?.ratings, selection.minRating),
            remove: () => (selection.minRating = null),
        });
    }

    return items;
});

function submit(): void {
    selection.q = draft.value.trim();
}

function clearAll(): void {
    Object.assign(selection, {
        q: "",
        categories: [],
        brands: [],
        priceRange: null,
        minRating: null,
    });
    draft.value = "";
}
</script>

<template>
    <section class="max-w-6xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 12 &middot; Advanced
        </p>
        <h1 class="mt-1 text-3xl font-bold">Faceted Search</h1>
        <p class="mt-3 text-gray-600">
            Facets are <em>aggregations</em>: Elasticsearch counts the matching
            documents per category, brand, price range and rating while it
            searches. Tick several values and watch the other counts follow.
        </p>

        <form class="mt-6 flex gap-3" @submit.prevent="submit">
            <input
                v-model="draft"
                type="text"
                placeholder="Search products (optional)..."
                class="w-full rounded border border-gray-300 px-4 py-2"
            />
            <button
                type="submit"
                class="rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700"
            >
                Search
            </button>
        </form>

        <div
            v-if="chips.length > 0 || selection.q !== ''"
            class="mt-4 flex flex-wrap items-center gap-2"
        >
            <span
                v-if="selection.q !== ''"
                class="rounded bg-gray-100 px-2 py-1 text-xs"
                >Text: {{ selection.q }}</span
            >
            <button
                v-for="chip in chips"
                :key="chip.label"
                type="button"
                class="rounded bg-indigo-50 px-2 py-1 text-xs text-indigo-800 hover:bg-indigo-100"
                @click="chip.remove()"
            >
                {{ chip.label }} &times;
            </button>
            <button
                type="button"
                class="text-xs text-gray-500 underline"
                @click="clearAll"
            >
                Clear all
            </button>
        </div>

        <p
            v-if="error"
            class="mt-6 rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <div
            v-else-if="result"
            class="mt-6 grid gap-8 lg:grid-cols-4"
            :class="{ 'opacity-60': loading }"
        >
            <aside class="space-y-6 lg:col-span-1">
                <FacetList
                    title="Category"
                    :buckets="result.facets.categories"
                    :selected="selection.categories"
                    @toggle="toggleMany(selection.categories, $event)"
                />
                <FacetList
                    title="Brand"
                    :buckets="result.facets.brands"
                    :selected="selection.brands"
                    @toggle="toggleMany(selection.brands, $event)"
                />
                <FacetList
                    title="Price"
                    :buckets="result.facets.price_ranges"
                    :selected="
                        selection.priceRange === null
                            ? []
                            : [selection.priceRange]
                    "
                    @toggle="
                        selection.priceRange = toggleOne(
                            selection.priceRange,
                            $event,
                        )
                    "
                />
                <FacetList
                    title="Rating"
                    :buckets="result.facets.ratings"
                    :selected="
                        selection.minRating === null
                            ? []
                            : [selection.minRating]
                    "
                    @toggle="
                        selection.minRating = toggleOne(
                            selection.minRating,
                            $event,
                        )
                    "
                />
            </aside>

            <div class="lg:col-span-3">
                <dl class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div class="rounded border border-gray-200 bg-white p-3">
                        <dt class="text-xs uppercase text-gray-400">Results</dt>
                        <dd class="mt-1 text-xl font-semibold">
                            {{ result.total }}
                        </dd>
                    </div>
                    <div class="rounded border border-gray-200 bg-white p-3">
                        <dt class="text-xs uppercase text-gray-400">
                            Average rating
                        </dt>
                        <dd class="mt-1 text-xl font-semibold">
                            {{
                                result.summary.average_rating?.toFixed(2) ?? "-"
                            }}
                        </dd>
                    </div>
                    <div class="rounded border border-gray-200 bg-white p-3">
                        <dt class="text-xs uppercase text-gray-400">
                            Cheapest
                        </dt>
                        <dd class="mt-1 text-xl font-semibold">
                            {{ result.summary.min_price?.toFixed(2) ?? "-" }}
                        </dd>
                    </div>
                    <div class="rounded border border-gray-200 bg-white p-3">
                        <dt class="text-xs uppercase text-gray-400">
                            Most expensive
                        </dt>
                        <dd class="mt-1 text-xl font-semibold">
                            {{ result.summary.max_price?.toFixed(2) ?? "-" }}
                        </dd>
                    </div>
                </dl>

                <p v-if="result.total === 0" class="mt-6 text-sm text-gray-500">
                    No product matches these filters.
                </p>

                <ul class="mt-4 space-y-3">
                    <li
                        v-for="hit in result.hits"
                        :key="hit.id"
                        class="flex items-start justify-between gap-4 rounded border border-gray-200 bg-white p-4"
                    >
                        <div>
                            <p class="font-medium">{{ hit.name }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ hit.category }} &middot;
                                {{ hit.brand }} &middot; rating {{ hit.rating }}
                                <span
                                    v-for="tag in hit.tags"
                                    :key="tag"
                                    class="ml-1 rounded bg-gray-100 px-1.5 py-0.5"
                                    >{{ tag }}</span
                                >
                            </p>
                        </div>
                        <p class="shrink-0 font-semibold">
                            {{ hit.price.toFixed(2) }}
                        </p>
                    </li>
                </ul>
                <p
                    v-if="result.total > result.hits.length"
                    class="mt-3 text-xs text-gray-500"
                >
                    Showing the first {{ result.hits.length }}.
                </p>

                <details class="mt-6">
                    <summary class="cursor-pointer text-sm font-semibold">
                        The Elasticsearch request that was sent
                    </summary>
                    <pre
                        class="mt-2 max-h-96 overflow-auto rounded bg-gray-900 p-4 text-xs text-green-300"
                        >{{ JSON.stringify(result.query, null, 2) }}</pre
                    >
                </details>
            </div>
        </div>
    </section>
</template>
