<script setup lang="ts">
import { useAliasReindex } from "../../composables/useAliasReindex";

const { status, lastRun, loading, error, reindex, rollback, reset } =
    useAliasReindex();
</script>

<template>
    <div>
        <p class="text-sm text-gray-600">
            Mapping or analyzer changes need a new index. If the application
            searches an <em>alias</em> instead of an index name, you can build
            the new version on the side, copy the documents, and move the alias
            in one atomic request. Searches never see a missing or half-filled
            index. This demo keeps a copy of the products under the alias
            <code>catalog</code>.
        </p>

        <div class="mt-4 flex flex-wrap gap-3">
            <button
                type="button"
                class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                :disabled="loading"
                @click="reindex"
            >
                Reindex to a new version
            </button>
            <button
                type="button"
                class="rounded border border-gray-300 px-4 py-2 text-sm disabled:opacity-50"
                :disabled="loading"
                @click="rollback"
            >
                Roll back
            </button>
            <button
                type="button"
                class="rounded border border-red-300 px-4 py-2 text-sm text-red-700 disabled:opacity-50"
                :disabled="loading"
                @click="reset"
            >
                Reset demo
            </button>
        </div>

        <p
            v-if="error"
            class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <div
            v-if="status"
            class="mt-4 rounded border border-gray-200 bg-white p-4 text-sm"
        >
            <p>
                Alias <code class="font-semibold">{{ status.alias }}</code>
                <span v-if="status.index">
                    points to
                    <code class="font-semibold text-indigo-700">{{
                        status.index
                    }}</code>
                    ({{ status.documents }} documents)</span
                >
                <span v-else class="text-gray-500">
                    does not exist yet. Run a reindex to create it.</span
                >
            </p>

            <table
                v-if="status.indices.length > 0"
                class="mt-3 w-full text-left"
            >
                <thead class="text-xs uppercase text-gray-400">
                    <tr>
                        <th class="py-1">Index</th>
                        <th class="py-1">Documents</th>
                        <th class="py-1">Size</th>
                        <th class="py-1">Alias</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="index in status.indices"
                        :key="index.name"
                        class="border-t border-gray-100"
                    >
                        <td class="py-1.5 font-medium">{{ index.name }}</td>
                        <td class="py-1.5">{{ index.documents }}</td>
                        <td class="py-1.5">{{ index.size }}</td>
                        <td class="py-1.5">
                            <span
                                v-if="index.current"
                                class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-800"
                                >current</span
                            >
                            <span v-else class="text-xs text-gray-400"
                                >kept for rollback</span
                            >
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="lastRun"
            class="mt-4 rounded border border-gray-200 bg-white p-4 text-sm"
        >
            <p>
                Copied <strong>{{ lastRun.copied }}</strong> documents from
                <code>{{ lastRun.from }}</code> to <code>{{ lastRun.to }}</code
                >.
            </p>
            <ol class="mt-2 space-y-1">
                <li
                    v-for="step in lastRun.steps"
                    :key="step.step"
                    class="flex justify-between"
                >
                    <span>{{ step.step }}</span>
                    <span class="font-medium">{{ step.ms }} ms</span>
                </li>
            </ol>
        </div>
    </div>
</template>
