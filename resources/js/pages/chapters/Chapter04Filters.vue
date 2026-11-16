<script setup lang="ts">
import { computed, onMounted, reactive } from "vue";
import { useProductFilters } from "../../composables/useProductFilters";
import type { FilterState, SortOption } from "../../types/filters";

const sortOptions: { value: SortOption; label: string }[] = [
    { value: "relevance", label: "Relevance" },
    { value: "price_asc", label: "Price: low to high" },
    { value: "price_desc", label: "Price: high to low" },
    { value: "rating_desc", label: "Rating: best first" },
    { value: "name_asc", label: "Name: A to Z" },
];

const { options, results, loading, error, apply } = useProductFilters();

const state = reactive<FilterState>({
    q: "",
    category: "",
    brands: [],
    minPrice: null,
    maxPrice: null,
    sort: "relevance",
});

const scoutQuery = computed(() => {
    const clauses: string[] = [];

    if (state.q !== "") {
        clauses.push(`(${state.q})`);
    }

    if (state.minPrice !== null || state.maxPrice !== null) {
        clauses.push(
            `price:[${state.minPrice ?? "*"} TO ${state.maxPrice ?? "*"}]`,
        );
    }

    return clauses.join(" AND ");
});

const codePreview = computed(() => {
    const lines = [`Product::search('${scoutQuery.value}')`];

    if (state.category !== "") {
        lines.push(`    ->where('category', '${state.category}')`);
    }

    if (state.brands.length > 0) {
        lines.push(
            `    ->whereIn('brand', [${state.brands.map((brand) => `'${brand}'`).join(", ")}])`,
        );
    }

    const orders: Partial<Record<SortOption, string>> = {
        price_asc: "->orderBy('price', 'asc')",
        price_desc: "->orderBy('price', 'desc')",
        rating_desc: "->orderBy('rating', 'desc')",
        name_asc: "->orderBy('name.keyword', 'asc')",
    };

    if (orders[state.sort]) {
        lines.push(`    ${orders[state.sort]}`);
    }

    lines.push("    ->take(20)->get();");

    return lines.join("\n");
});

function run(): void {
    void apply(state);
}

function reset(): void {
    Object.assign(state, {
        q: "",
        category: "",
        brands: [],
        minPrice: null,
        maxPrice: null,
        sort: "relevance",
    });
    run();
}

onMounted(run);
</script>

<template>
    <section class="max-w-5xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 4 &middot; Beginner
        </p>
        <h1 class="mt-1 text-3xl font-bold">Filters &amp; Sorting</h1>
        <p class="mt-3 text-gray-600">
            Filters narrow the results without affecting relevance, and
            <code>orderBy</code> changes their order. Change any control and
            compare the Scout code below with the results.
        </p>

        <form
            class="mt-6 grid gap-4 rounded border border-gray-200 bg-white p-5 md:grid-cols-2"
            @submit.prevent="run"
        >
            <label class="block text-sm md:col-span-2">
                <span class="text-gray-600">Search text (optional)</span>
                <input
                    v-model="state.q"
                    type="text"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                />
            </label>

            <label class="block text-sm">
                <span class="text-gray-600">Category</span>
                <select
                    v-model="state.category"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    @change="run"
                >
                    <option value="">All categories</option>
                    <option
                        v-for="category in options.categories"
                        :key="category"
                        :value="category"
                    >
                        {{ category }}
                    </option>
                </select>
            </label>

            <label class="block text-sm">
                <span class="text-gray-600">Sort by</span>
                <select
                    v-model="state.sort"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    @change="run"
                >
                    <option
                        v-for="sort in sortOptions"
                        :key="sort.value"
                        :value="sort.value"
                    >
                        {{ sort.label }}
                    </option>
                </select>
            </label>

            <label class="block text-sm">
                <span class="text-gray-600">Min price</span>
                <input
                    v-model.number="state.minPrice"
                    type="number"
                    min="0"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                />
            </label>

            <label class="block text-sm">
                <span class="text-gray-600">Max price</span>
                <input
                    v-model.number="state.maxPrice"
                    type="number"
                    min="0"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                />
            </label>

            <fieldset class="md:col-span-2">
                <legend class="text-sm text-gray-600">Brands</legend>
                <div class="mt-1 flex flex-wrap gap-4">
                    <label
                        v-for="brand in options.brands"
                        :key="brand"
                        class="flex items-center gap-1.5 text-sm"
                    >
                        <input
                            v-model="state.brands"
                            type="checkbox"
                            :value="brand"
                            @change="run"
                        />
                        {{ brand }}
                    </label>
                </div>
            </fieldset>

            <div class="flex gap-3 md:col-span-2">
                <button
                    type="submit"
                    class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                    :disabled="loading"
                >
                    Apply
                </button>
                <button
                    type="button"
                    class="rounded border border-gray-300 px-4 py-2 text-sm"
                    @click="reset"
                >
                    Reset
                </button>
            </div>
        </form>

        <h2 class="mt-8 text-lg font-semibold">The Scout code behind it</h2>
        <pre
            class="mt-2 overflow-x-auto rounded bg-gray-900 p-4 text-xs text-green-300"
            >{{ codePreview }}</pre
        >

        <p
            v-if="error"
            class="mt-6 rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <template v-else>
            <h2 class="mt-8 text-lg font-semibold">
                {{ results.length }} result(s)
            </h2>
            <ul class="mt-3 space-y-3">
                <li
                    v-for="product in results"
                    :key="product.id"
                    class="flex items-start justify-between gap-4 rounded border border-gray-200 bg-white p-4"
                >
                    <div>
                        <p class="font-medium">{{ product.name }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ product.category }} &middot;
                            {{ product.brand }} &middot; rating
                            {{ product.rating }}
                        </p>
                    </div>
                    <p class="shrink-0 font-semibold">
                        {{ product.price.toFixed(2) }}
                    </p>
                </li>
            </ul>
        </template>
    </section>
</template>
