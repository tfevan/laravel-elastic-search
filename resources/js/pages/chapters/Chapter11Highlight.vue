<script setup lang="ts">
import { onMounted, ref } from "vue";
import HighlightedText from "../../components/HighlightedText.vue";
import { useHighlightSearch } from "../../composables/useHighlightSearch";

interface Example {
    query: string;
    fuzzy: boolean;
    note: string;
}

const examples: Example[] = [
    { query: "wireless", fuzzy: false, note: "A plain word." },
    {
        query: "mobile",
        fuzzy: false,
        note: 'A synonym: "Smartphone" is highlighted.',
    },
    { query: "headphnes", fuzzy: true, note: "A typo, matched by fuzziness." },
    {
        query: "battery life",
        fuzzy: false,
        note: "A match inside the description.",
    },
];

const { result, loading, error, search } = useHighlightSearch();

const query = ref<string>("wireless");
const fuzzy = ref<boolean>(false);

function run(): void {
    void search(query.value, fuzzy.value);
}

function tryExample(example: Example): void {
    query.value = example.query;
    fuzzy.value = example.fuzzy;
    run();
}

onMounted(run);
</script>

<template>
    <section class="max-w-4xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 11 &middot; Advanced
        </p>
        <h1 class="mt-1 text-3xl font-bold">Highlighting</h1>
        <p class="mt-3 text-gray-600">
            Elasticsearch can tell you <em>which</em> words made a document
            match. The highlighter returns the field text with the matched words
            wrapped in markers, so the page can mark them up.
        </p>

        <form
            class="mt-6 flex flex-wrap items-center gap-3"
            @submit.prevent="run"
        >
            <input
                v-model="query"
                type="text"
                class="min-w-64 flex-1 rounded border border-gray-300 px-4 py-2"
            />
            <label class="flex items-center gap-2 text-sm">
                <input v-model="fuzzy" type="checkbox" @change="run" />
                Typo tolerant
            </label>
            <button
                type="submit"
                class="rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="loading"
            >
                Search
            </button>
        </form>

        <ul class="mt-3 grid gap-2 md:grid-cols-2">
            <li v-for="example in examples" :key="example.query">
                <button
                    type="button"
                    class="w-full rounded border border-gray-200 bg-white p-3 text-left hover:border-indigo-300"
                    @click="tryExample(example)"
                >
                    <code class="text-sm text-indigo-700">{{
                        example.query
                    }}</code>
                    <span class="mt-1 block text-xs text-gray-500">{{
                        example.note
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

        <template v-else-if="result">
            <h2 class="mt-8 text-lg font-semibold">
                {{ result.total }} result(s)
            </h2>

            <ul class="mt-3 space-y-3">
                <li
                    v-for="hit in result.hits"
                    :key="hit.id"
                    class="rounded border border-gray-200 bg-white p-4"
                >
                    <p class="font-medium">
                        <HighlightedText
                            :snippet="hit.highlight.name"
                            :fallback="hit.product.name"
                            :start="result.markers.start"
                            :end="result.markers.end"
                        />
                    </p>
                    <p class="mt-1 text-sm text-gray-600">
                        <HighlightedText
                            :snippet="hit.highlight.description"
                            :fallback="hit.product.description"
                            :start="result.markers.start"
                            :end="result.markers.end"
                        />
                    </p>
                    <p class="mt-2 text-xs text-gray-500">
                        {{ hit.product.category }} &middot;
                        {{ hit.product.brand }} &middot;
                        {{ hit.product.price.toFixed(2) }}
                    </p>
                </li>
            </ul>
        </template>

        <div
            class="mt-10 rounded border border-gray-200 bg-white p-5 text-sm text-gray-600"
        >
            <h2 class="font-semibold text-gray-900">
                Why not <code>v-html</code>?
            </h2>
            <p class="mt-2">
                By default the highlighter wraps matches in
                <code>&lt;em&gt;</code> but leaves the rest of the field
                untouched. If a product name contained
                <code>&lt;img src=x onerror=alert(1)&gt;</code>, rendering the
                snippet with <code>v-html</code> would run that script. Here the
                API uses plain-text markers and the page splits the text into
                segments and prints each one with <code v-pre>{{}}</code>, which
                always escapes HTML.
            </p>
        </div>
    </section>
</template>
