<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useFuzzySearch } from "../../composables/useFuzzySearch";
import type { Fuzziness } from "../../types/fuzzy";

const levels: { value: Fuzziness; label: string }[] = [
    { value: "0", label: "0 (exact)" },
    { value: "1", label: "1 edit" },
    { value: "2", label: "2 edits" },
    { value: "AUTO", label: "AUTO" },
];

const examples: string[] = [
    "headphnes",
    "wirless chargr",
    "smatwatch",
    "bluetoth speker",
    "lapto",
];

const { result, loading, error, search } = useFuzzySearch();

const query = ref<string>("headphnes");
const fuzziness = ref<Fuzziness>("AUTO");
const prefixLength = ref<number>(0);

function run(value: string = query.value): void {
    query.value = value;
    void search(value, fuzziness.value, prefixLength.value);
}

onMounted(() => run());
</script>

<template>
    <section class="max-w-4xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 9 &middot; Advanced
        </p>
        <h1 class="mt-1 text-3xl font-bold">Fuzzy Search</h1>
        <p class="mt-3 text-gray-600">
            People make typos. A fuzzy query matches words within a small
            <em>edit distance</em> (insert, delete, replace or swap one letter)
            of what was typed, and a suggester can propose the spelling it
            thinks was meant.
        </p>

        <form
            class="mt-6 flex flex-wrap items-end gap-3"
            @submit.prevent="run()"
        >
            <label class="min-w-64 flex-1 text-sm">
                <span class="text-gray-600">Search (try a typo)</span>
                <input
                    v-model="query"
                    type="text"
                    class="mt-1 w-full rounded border border-gray-300 px-4 py-2"
                />
            </label>
            <label class="text-sm">
                <span class="text-gray-600">Fuzziness</span>
                <select
                    v-model="fuzziness"
                    class="mt-1 block rounded border border-gray-300 px-3 py-2"
                    @change="run()"
                >
                    <option
                        v-for="level in levels"
                        :key="level.value"
                        :value="level.value"
                    >
                        {{ level.label }}
                    </option>
                </select>
            </label>
            <label class="text-sm">
                <span class="text-gray-600">Exact prefix</span>
                <select
                    v-model.number="prefixLength"
                    class="mt-1 block rounded border border-gray-300 px-3 py-2"
                    @change="run()"
                >
                    <option
                        v-for="length in [0, 1, 2, 3]"
                        :key="length"
                        :value="length"
                    >
                        {{ length }} letter(s)
                    </option>
                </select>
            </label>
            <button
                type="submit"
                class="rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="loading"
            >
                Search
            </button>
        </form>

        <div class="mt-3 flex flex-wrap gap-2">
            <button
                v-for="example in examples"
                :key="example"
                type="button"
                class="rounded border border-gray-200 bg-white px-3 py-1 text-xs hover:border-indigo-300"
                @click="run(example)"
            >
                {{ example }}
            </button>
        </div>

        <p
            v-if="error"
            class="mt-6 rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <template v-else-if="result">
            <p
                v-if="result.suggestion"
                class="mt-6 rounded border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900"
            >
                Did you mean
                <button
                    type="button"
                    class="font-semibold underline"
                    @click="run(result.suggestion)"
                >
                    {{ result.suggestion }}</button
                >?
            </p>

            <h2 class="mt-6 text-sm font-semibold">
                Matches by fuzziness for "{{ query }}"
            </h2>
            <dl class="mt-2 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div
                    v-for="level in levels"
                    :key="level.value"
                    class="rounded border p-3"
                    :class="
                        level.value === fuzziness
                            ? 'border-indigo-400 bg-indigo-50'
                            : 'border-gray-200 bg-white'
                    "
                >
                    <dt class="text-xs uppercase text-gray-400">
                        {{ level.label }}
                    </dt>
                    <dd class="mt-1 text-xl font-semibold">
                        {{ result.counts[level.value] }}
                    </dd>
                </div>
            </dl>

            <h2 class="mt-8 text-lg font-semibold">
                {{ result.total }} result(s)
            </h2>
            <p v-if="result.total === 0" class="mt-2 text-sm text-gray-500">
                Nothing matched. Try a higher fuzziness or fewer exact prefix
                letters.
            </p>

            <ul class="mt-3 space-y-3">
                <li
                    v-for="hit in result.hits"
                    :key="hit.id"
                    class="flex items-start justify-between gap-4 rounded border border-gray-200 bg-white p-4"
                >
                    <div>
                        <p class="font-medium">{{ hit.product.name }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ hit.product.category }} &middot;
                            {{ hit.product.brand }}
                        </p>
                    </div>
                    <p class="shrink-0 text-sm font-semibold text-indigo-700">
                        {{ hit.score?.toFixed(3) ?? "n/a" }}
                    </p>
                </li>
            </ul>
        </template>

        <div
            class="mt-10 rounded border border-gray-200 bg-white p-5 text-sm text-gray-600"
        >
            <h2 class="font-semibold text-gray-900">
                How <code>AUTO</code> decides
            </h2>
            <ul class="mt-2 list-inside list-disc space-y-1">
                <li>Words of 0-2 letters must match exactly.</li>
                <li>Words of 3-5 letters allow 1 edit.</li>
                <li>Words of 6 or more letters allow 2 edits.</li>
            </ul>
            <p class="mt-3">
                <strong>Exact prefix</strong> requires the first letters to be
                right, which is the cheapest way to make fuzzy search faster and
                more precise. Fuzzy queries are expensive, so keep
                <code>AUTO</code> and a prefix of 1 or 2.
            </p>
        </div>
    </section>
</template>
