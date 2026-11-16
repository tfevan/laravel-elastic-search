<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { usePaginatedSearch } from "../../composables/usePaginatedSearch";
import { pageWindow } from "../../utils/pagination";

const perPageOptions: number[] = [10, 20, 50];

const { results, meta, loading, error, load } = usePaginatedSearch();

const query = ref<string>("");
const page = ref<number>(1);
const perPage = ref<number>(10);

const pages = computed(() =>
    meta.value ? pageWindow(meta.value.current_page, meta.value.last_page) : [],
);
const offset = computed(() => (page.value - 1) * perPage.value);

function goTo(target: number): void {
    page.value = target;
    void load(query.value, page.value, perPage.value);
}

function search(): void {
    goTo(1);
}

onMounted(search);
</script>

<template>
    <section class="max-w-4xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 5 &middot; Beginner
        </p>
        <h1 class="mt-1 text-3xl font-bold">Pagination</h1>
        <p class="mt-3 text-gray-600">
            <code>paginate()</code> asks Elasticsearch for one slice of the
            results (<code>from</code> and <code>size</code>) and also for the
            total number of matches, so a pager can be drawn.
        </p>

        <form class="mt-6 flex gap-3" @submit.prevent="search">
            <input
                v-model="query"
                type="text"
                placeholder="Search products..."
                class="w-full rounded border border-gray-300 px-4 py-2"
            />
            <select
                v-model.number="perPage"
                class="rounded border border-gray-300 px-3 py-2 text-sm"
                @change="search"
            >
                <option
                    v-for="size in perPageOptions"
                    :key="size"
                    :value="size"
                >
                    {{ size }} / page
                </option>
            </select>
            <button
                type="submit"
                class="rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="loading"
            >
                Search
            </button>
        </form>

        <pre
            class="mt-4 overflow-x-auto rounded bg-gray-900 p-4 text-xs text-green-300"
        >
Product::search('{{ query }}')->paginate({{ perPage }});

// sent to Elasticsearch for page {{ page }}:  from = {{ offset }},  size = {{
                perPage
            }}</pre
        >

        <p
            v-if="error"
            class="mt-6 rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <template v-else-if="meta">
            <p class="mt-6 text-sm text-gray-600">
                Showing
                <strong>{{ meta.from ?? 0 }}-{{ meta.to ?? 0 }}</strong> of
                <strong>{{ meta.total }}</strong> results
            </p>

            <ul class="mt-3 space-y-2">
                <li
                    v-for="(product, index) in results"
                    :key="product.id"
                    class="flex items-center justify-between gap-4 rounded border border-gray-200 bg-white px-4 py-3"
                >
                    <div>
                        <p class="text-sm font-medium">
                            <span class="mr-2 text-gray-400"
                                >{{ (meta.from ?? 1) + index }}.</span
                            >{{ product.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ product.category }} &middot; {{ product.brand }}
                        </p>
                    </div>
                    <p class="shrink-0 text-sm font-semibold">
                        {{ product.price.toFixed(2) }}
                    </p>
                </li>
            </ul>

            <nav
                class="mt-6 flex flex-wrap items-center gap-1"
                aria-label="Pagination"
            >
                <button
                    type="button"
                    class="rounded border border-gray-300 px-3 py-1 text-sm disabled:opacity-50"
                    :disabled="meta.current_page <= 1 || loading"
                    @click="goTo(meta.current_page - 1)"
                >
                    Previous
                </button>

                <template v-for="(item, index) in pages" :key="index">
                    <span v-if="item === 'gap'" class="px-2 text-gray-400"
                        >&hellip;</span
                    >
                    <button
                        v-else
                        type="button"
                        class="rounded border px-3 py-1 text-sm"
                        :class="
                            item === meta.current_page
                                ? 'border-indigo-600 bg-indigo-600 text-white'
                                : 'border-gray-300 hover:bg-gray-100'
                        "
                        :disabled="loading"
                        @click="goTo(item)"
                    >
                        {{ item }}
                    </button>
                </template>

                <button
                    type="button"
                    class="rounded border border-gray-300 px-3 py-1 text-sm disabled:opacity-50"
                    :disabled="meta.current_page >= meta.last_page || loading"
                    @click="goTo(meta.current_page + 1)"
                >
                    Next
                </button>
            </nav>
        </template>

        <p class="mt-8 text-xs text-gray-500">
            Elasticsearch refuses <code>from + size</code> above 10,000 by
            default. Try page 1001 with 10 per page: the API answers with a
            clear message. Chapter 14 shows how to go deeper with
            <code>search_after</code>.
        </p>
    </section>
</template>
