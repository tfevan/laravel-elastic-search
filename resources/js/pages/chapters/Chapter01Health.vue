<script setup lang="ts">
import { computed } from "vue";
import { useClusterHealth } from "../../composables/useClusterHealth";
import type { ClusterStatus } from "../../types/health";

const { health, loading, error, reload } = useClusterHealth();

const badgeClasses: Record<ClusterStatus, string> = {
    green: "bg-green-100 text-green-800",
    yellow: "bg-yellow-100 text-yellow-800",
    red: "bg-red-100 text-red-800",
};

const statusBadge = computed(() =>
    health.value ? badgeClasses[health.value.cluster.status] : "",
);
</script>

<template>
    <section class="max-w-4xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 1 &middot; Beginner
        </p>
        <h1 class="mt-1 text-3xl font-bold">Connection &amp; Health</h1>
        <p class="mt-3 text-gray-600">
            Laravel talks to Elasticsearch through the official PHP client. This
            page calls a small API endpoint that reads the cluster status, the
            server version and the list of indices.
        </p>

        <button
            type="button"
            class="mt-6 rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
            :disabled="loading"
            @click="reload"
        >
            {{ loading ? "Checking..." : "Check again" }}
        </button>

        <p
            v-if="error"
            class="mt-6 rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <template v-else-if="health">
            <dl class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <span
                            class="rounded px-2 py-0.5 text-sm font-medium"
                            :class="statusBadge"
                        >
                            {{ health.cluster.status }}
                        </span>
                    </dd>
                </div>
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">Cluster</dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ health.cluster.name }}
                    </dd>
                </div>
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">Version</dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ health.version }}
                    </dd>
                </div>
                <div class="rounded border border-gray-200 bg-white p-4">
                    <dt class="text-xs uppercase text-gray-400">Nodes</dt>
                    <dd class="mt-1 text-sm font-medium">
                        {{ health.cluster.nodes }}
                    </dd>
                </div>
            </dl>

            <p
                v-if="health.cluster.status === 'yellow'"
                class="mt-3 text-xs text-gray-500"
            >
                Yellow is normal on a single node: replica shards have no second
                node to live on.
            </p>

            <h2 class="mt-8 text-lg font-semibold">Indices</h2>
            <table
                class="mt-3 w-full overflow-hidden rounded border border-gray-200 bg-white text-left text-sm"
            >
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Health</th>
                        <th class="px-4 py-2">Documents</th>
                        <th class="px-4 py-2">Size</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="index in health.indices"
                        :key="index.name"
                        class="border-t border-gray-100"
                    >
                        <td class="px-4 py-2 font-medium">{{ index.name }}</td>
                        <td class="px-4 py-2">{{ index.health }}</td>
                        <td class="px-4 py-2">{{ index.documents }}</td>
                        <td class="px-4 py-2">{{ index.size }}</td>
                    </tr>
                    <tr v-if="health.indices.length === 0">
                        <td colspan="4" class="px-4 py-4 text-gray-500">
                            No indices yet. Run
                            <code>php artisan migrate:fresh --seed</code>.
                        </td>
                    </tr>
                </tbody>
            </table>
        </template>
    </section>
</template>
