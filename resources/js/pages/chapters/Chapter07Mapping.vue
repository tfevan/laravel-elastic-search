<script setup lang="ts">
import { computed, ref } from "vue";
import {
    analyzers,
    useAnalyzerComparison,
} from "../../composables/useAnalyzerComparison";
import { useIndexMapping } from "../../composables/useIndexMapping";

const { mapping, error: mappingError } = useIndexMapping();
const {
    results,
    loading,
    error: analyzeError,
    compare,
} = useAnalyzerComparison();

const text = ref<string>("Wireless Headphones for mobile");

const examples: string[] = [
    "Wireless Headphones for mobile",
    "mobile",
    "Smartphones for the café",
];

const rows = computed(() =>
    Object.entries(mapping.value?.mapping.properties ?? {}).map(
        ([name, field]) => ({
            name,
            type: field.type,
            notes: [
                field.analyzer ? `index: ${field.analyzer}` : null,
                field.search_analyzer
                    ? `search: ${field.search_analyzer}`
                    : null,
                field.scaling_factor
                    ? `scaling factor ${field.scaling_factor}`
                    : null,
                field.fields
                    ? `sub-fields: ${Object.keys(field.fields).join(", ")}`
                    : null,
            ]
                .filter((note): note is string => note !== null)
                .join(" · "),
        }),
    ),
);

function run(value: string = text.value): void {
    text.value = value;
    void compare(value);
}
</script>

<template>
    <section class="max-w-5xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 7 &middot; Advanced
        </p>
        <h1 class="mt-1 text-3xl font-bold">Mapping &amp; Analyzers</h1>
        <p class="mt-3 text-gray-600">
            The mapping tells Elasticsearch how to store each field. An analyzer
            decides how a text is split into the tokens that are actually
            searched. This chapter defines both in an Elastic migration.
        </p>

        <h2 class="mt-8 text-xl font-semibold">1. The products mapping</h2>
        <p
            v-if="mappingError"
            class="mt-3 rounded border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800"
        >
            {{ mappingError }}
        </p>

        <template v-else-if="mapping">
            <dl class="mt-3 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">Dynamic</dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ mapping.mapping.dynamic ?? "true" }}
                    </dd>
                </div>
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">
                        Shards / replicas
                    </dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ mapping.settings.shards }} /
                        {{ mapping.settings.replicas }}
                    </dd>
                </div>
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">Analyzers</dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ mapping.settings.analyzers.join(", ") || "none" }}
                    </dd>
                </div>
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">
                        Token filters
                    </dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ mapping.settings.filters.join(", ") || "none" }}
                    </dd>
                </div>
            </dl>

            <table
                class="mt-4 w-full overflow-hidden rounded border border-gray-200 bg-white text-left text-sm"
            >
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Field</th>
                        <th class="px-4 py-2">Type</th>
                        <th class="px-4 py-2">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in rows"
                        :key="row.name"
                        class="border-t border-gray-100"
                    >
                        <td class="px-4 py-2 font-medium">{{ row.name }}</td>
                        <td class="px-4 py-2">
                            <code>{{ row.type }}</code>
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ row.notes }}</td>
                    </tr>
                </tbody>
            </table>
        </template>

        <h2 class="mt-12 text-xl font-semibold">2. Compare analyzers</h2>
        <p class="mt-2 text-sm text-gray-600">
            Type a text and see the tokens each analyzer produces. Notice how
            <code>product_search</code> expands <em>mobile</em> into
            <em>phone</em> and <em>smartphone</em>, while <code>english</code>
            reduces words to their stems.
        </p>

        <form class="mt-3 flex gap-3" @submit.prevent="run()">
            <input
                v-model="text"
                type="text"
                class="w-full rounded border border-gray-300 px-4 py-2"
            />
            <button
                type="submit"
                class="rounded bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="loading"
            >
                Analyze
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
            v-if="analyzeError"
            class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
            {{ analyzeError }}
        </p>

        <ul v-else-if="Object.keys(results).length > 0" class="mt-4 space-y-3">
            <li
                v-for="analyzer in analyzers"
                :key="analyzer"
                class="rounded border border-gray-200 bg-white p-4"
            >
                <p class="text-xs font-semibold uppercase text-gray-400">
                    {{ analyzer }}
                </p>
                <p class="mt-2 flex flex-wrap gap-2">
                    <span
                        v-for="(token, index) in results[analyzer]"
                        :key="index"
                        class="rounded bg-indigo-50 px-2 py-0.5 text-sm text-indigo-800"
                        :title="`position ${token.position} · ${token.type}`"
                    >
                        {{ token.token }}
                    </span>
                </p>
            </li>
        </ul>
    </section>
</template>
