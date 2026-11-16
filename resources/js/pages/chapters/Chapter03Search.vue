<script setup lang="ts">
import { ref } from "vue";
import { useProductSearch } from "../../composables/useProductSearch";

interface Example {
    query: string;
    description: string;
}

const examples: Example[] = [
    {
        query: "wireless",
        description: "A plain word, searched in every field.",
    },
    {
        query: '"wireless headphones"',
        description: "Quotes match the exact phrase.",
    },
    {
        query: "brand:Apex AND category:Audio",
        description: "Field search with AND.",
    },
    {
        query: "name:(smartwatch OR tracker)",
        description: "Group several words on one field.",
    },
    {
        query: "name:head*",
        description: 'Wildcard: words starting with "head".',
    },
    {
        query: "price:[100 TO 500]",
        description: "A numeric range, both ends included.",
    },
    {
        query: "rating:>=4.5 AND category:Wearables",
        description: "Comparison operators.",
    },
    {
        query: "tags:gaming AND NOT brand:Nova",
        description: "Exclude results with NOT.",
    },
];

const { results, searched, loading, error, search } = useProductSearch();
const query = ref<string>("");

function run(value: string): void {
    query.value = value;
    void search(value);
}
</script>

<template>
    <section class="max-w-4xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 3 &middot; Beginner
        </p>
        <h1 class="mt-1 text-3xl font-bold">Basic Search</h1>
        <p class="mt-3 text-gray-600">
            <code>Product::search('...')</code> sends your text to Elasticsearch
            as a query string. Besides plain words, the query string language
            lets you target fields, combine conditions and filter ranges. Type a
            query or click an example.
        </p>

        <form class="mt-6 flex gap-3" @submit.prevent="run(query)">
            <input
                v-model="query"
                type="text"
                placeholder="Search products..."
                class="w-full rounded border border-gray-300 px-4 py-2"
            />
            <button
                type="submit"
                class="rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="loading"
            >
                Search
            </button>
        </form>

        <ul class="mt-4 grid gap-2 md:grid-cols-2">
            <li v-for="example in examples" :key="example.query">
                <button
                    type="button"
                    class="w-full rounded border border-gray-200 bg-white p-3 text-left hover:border-indigo-300"
                    @click="run(example.query)"
                >
                    <code class="text-sm text-indigo-700">{{
                        example.query
                    }}</code>
                    <span class="mt-1 block text-xs text-gray-500">{{
                        example.description
                    }}</span>
                </button>
            </li>
        </ul>

        <p
            v-if="error"
            class="mt-6 rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <template v-else-if="searched">
            <h2 class="mt-8 text-lg font-semibold">
                {{ results.length }} result(s)
            </h2>
            <p v-if="results.length === 20" class="text-xs text-gray-500">
                Showing the first 20. Pagination comes in chapter 5.
            </p>

            <ul class="mt-3 space-y-3">
                <li
                    v-for="product in results"
                    :key="product.id"
                    class="rounded border border-gray-200 bg-white p-4"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-medium">{{ product.name }}</p>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ product.description }}
                            </p>
                        </div>
                        <p class="shrink-0 font-semibold">
                            {{ product.price.toFixed(2) }}
                        </p>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        {{ product.category }} &middot;
                        {{ product.brand }} &middot; rating {{ product.rating }}
                        <span
                            v-for="tag in product.tags"
                            :key="tag"
                            class="ml-1 rounded bg-gray-100 px-1.5 py-0.5"
                            >{{ tag }}</span
                        >
                    </p>
                </li>
            </ul>
        </template>
    </section>
</template>
