<script setup lang="ts">
import { computed, onMounted, reactive } from "vue";
import ExplanationTree from "../../components/ExplanationTree.vue";
import { useProductFilters } from "../../composables/useProductFilters";
import { useRelevance } from "../../composables/useRelevance";
import type { RelevanceOptions } from "../../types/relevance";

const { options: filterOptions } = useProductFilters();
const { result, loading, error, explainingId, explanation, search, explain } =
    useRelevance();

const defaults = (): RelevanceOptions => ({
    q: "wireless headphones",
    nameBoost: 3,
    descriptionBoost: 1,
    operator: "or",
    preferTopRated: false,
    preferPopular: false,
    category: "",
    excludeBrand: "",
    minPrice: null,
    maxPrice: null,
});

const state = reactive<RelevanceOptions>(defaults());

const presets: { label: string; apply: Partial<RelevanceOptions> }[] = [
    {
        label: "Name matters most",
        apply: { nameBoost: 5, descriptionBoost: 1 },
    },
    { label: "Equal weight", apply: { nameBoost: 1, descriptionBoost: 1 } },
    {
        label: "Prefer top-rated and popular",
        apply: { preferTopRated: true, preferPopular: true },
    },
];

const maxScore = computed(() =>
    Math.max(...(result.value?.hits.map((hit) => hit.score ?? 0) ?? [1]), 1),
);

function run(): void {
    void search(state);
}

function applyPreset(preset: Partial<RelevanceOptions>): void {
    Object.assign(state, defaults(), { q: state.q }, preset);
    run();
}

onMounted(run);
</script>

<template>
    <section class="max-w-6xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 8 &middot; Advanced
        </p>
        <h1 class="mt-1 text-3xl font-bold">Relevance &amp; Bool Queries</h1>
        <p class="mt-3 text-gray-600">
            Elasticsearch ranks matches with a relevance <code>_score</code>. A
            <code>bool</code> query combines clauses with different roles:
            <strong>must</strong> matches and scores,
            <strong>should</strong> only adds to the score,
            <strong>filter</strong> narrows without scoring and
            <strong>must_not</strong> excludes.
        </p>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                v-for="preset in presets"
                :key="preset.label"
                type="button"
                class="rounded border border-gray-200 bg-white px-3 py-1 text-xs hover:border-indigo-300"
                @click="applyPreset(preset.apply)"
            >
                {{ preset.label }}
            </button>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <form
                class="space-y-4 rounded border border-gray-200 bg-white p-5 lg:col-span-1"
                @submit.prevent="run"
            >
                <label class="block text-sm">
                    <span class="text-gray-600">must: text</span>
                    <input
                        v-model="state.q"
                        type="text"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    />
                </label>

                <label class="block text-sm">
                    <span class="text-gray-600"
                        >name boost: {{ state.nameBoost }}</span
                    >
                    <input
                        v-model.number="state.nameBoost"
                        type="range"
                        min="0.5"
                        max="10"
                        step="0.5"
                        class="w-full"
                    />
                </label>

                <label class="block text-sm">
                    <span class="text-gray-600"
                        >description boost: {{ state.descriptionBoost }}</span
                    >
                    <input
                        v-model.number="state.descriptionBoost"
                        type="range"
                        min="0.5"
                        max="10"
                        step="0.5"
                        class="w-full"
                    />
                </label>

                <label class="block text-sm">
                    <span class="text-gray-600">Words must match</span>
                    <select
                        v-model="state.operator"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    >
                        <option value="or">any word (or)</option>
                        <option value="and">all words (and)</option>
                    </select>
                </label>

                <fieldset class="space-y-1 text-sm">
                    <legend class="text-gray-600">should: boost when</legend>
                    <label class="flex items-center gap-2"
                        ><input
                            v-model="state.preferTopRated"
                            type="checkbox"
                        />
                        rating is 4.5 or more</label
                    >
                    <label class="flex items-center gap-2"
                        ><input v-model="state.preferPopular" type="checkbox" />
                        tagged "popular"</label
                    >
                </fieldset>

                <label class="block text-sm">
                    <span class="text-gray-600">filter: category</span>
                    <select
                        v-model="state.category"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    >
                        <option value="">Any</option>
                        <option
                            v-for="category in filterOptions.categories"
                            :key="category"
                            :value="category"
                        >
                            {{ category }}
                        </option>
                    </select>
                </label>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <label class="block">
                        <span class="text-gray-600">filter: min price</span>
                        <input
                            v-model.number="state.minPrice"
                            type="number"
                            min="0"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                        />
                    </label>
                    <label class="block">
                        <span class="text-gray-600">max price</span>
                        <input
                            v-model.number="state.maxPrice"
                            type="number"
                            min="0"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                        />
                    </label>
                </div>

                <label class="block text-sm">
                    <span class="text-gray-600">must_not: brand</span>
                    <select
                        v-model="state.excludeBrand"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    >
                        <option value="">None</option>
                        <option
                            v-for="brand in filterOptions.brands"
                            :key="brand"
                            :value="brand"
                        >
                            {{ brand }}
                        </option>
                    </select>
                </label>

                <button
                    type="submit"
                    class="w-full rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                    :disabled="loading"
                >
                    Search
                </button>
            </form>

            <div class="lg:col-span-2">
                <p
                    v-if="error"
                    class="rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                >
                    {{ error }}
                </p>

                <template v-else-if="result">
                    <p class="text-sm text-gray-600">
                        {{ result.total }} match(es), top
                        {{ result.hits.length }} by score.
                    </p>

                    <ul class="mt-3 space-y-3">
                        <li
                            v-for="hit in result.hits"
                            :key="hit.id"
                            class="rounded border border-gray-200 bg-white p-4"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-medium">
                                        {{ hit.product.name }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ hit.product.category }} &middot;
                                        {{ hit.product.brand }} &middot; rating
                                        {{ hit.product.rating }} &middot;
                                        {{ hit.product.price.toFixed(2) }}
                                        <span
                                            v-for="tag in hit.product.tags"
                                            :key="tag"
                                            class="ml-1 rounded bg-gray-100 px-1.5 py-0.5"
                                            >{{ tag }}</span
                                        >
                                    </p>
                                </div>
                                <p
                                    class="shrink-0 text-sm font-semibold text-indigo-700"
                                >
                                    {{ hit.score?.toFixed(3) ?? "n/a" }}
                                </p>
                            </div>

                            <div class="mt-2 h-1.5 rounded bg-gray-100">
                                <div
                                    class="h-1.5 rounded bg-indigo-500"
                                    :style="{
                                        width: `${((hit.score ?? 0) / maxScore) * 100}%`,
                                    }"
                                ></div>
                            </div>

                            <button
                                type="button"
                                class="mt-2 text-xs text-indigo-600 hover:underline"
                                @click="explain(hit.id, state)"
                            >
                                Why this score?
                            </button>

                            <div
                                v-if="explainingId === hit.id"
                                class="mt-2 rounded bg-gray-50 p-3"
                            >
                                <p
                                    v-if="!explanation"
                                    class="text-xs text-gray-500"
                                >
                                    Loading...
                                </p>
                                <ul v-else-if="explanation.explanation">
                                    <ExplanationTree
                                        :node="explanation.explanation"
                                    />
                                </ul>
                            </div>
                        </li>
                    </ul>

                    <details class="mt-6">
                        <summary class="cursor-pointer text-sm font-semibold">
                            The Elasticsearch query that was sent
                        </summary>
                        <pre
                            class="mt-2 max-h-96 overflow-auto rounded bg-gray-900 p-4 text-xs text-green-300"
                            >{{ JSON.stringify(result.query, null, 2) }}</pre
                        >
                    </details>
                </template>
            </div>
        </div>
    </section>
</template>
