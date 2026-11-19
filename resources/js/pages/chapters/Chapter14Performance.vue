<script setup lang="ts">
import AliasReindexDemo from "../../components/performance/AliasReindexDemo.vue";
import DeepPaginationDemo from "../../components/performance/DeepPaginationDemo.vue";
import QueueIndexingDemo from "../../components/performance/QueueIndexingDemo.vue";
</script>

<template>
    <section class="max-w-4xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 14 &middot; Advanced
        </p>
        <h1 class="mt-1 text-3xl font-bold">Performance</h1>
        <p class="mt-3 text-gray-600">
            Three techniques that matter once an index grows or traffic picks
            up: paging deeply without a cost that grows with the page number,
            keeping Elasticsearch out of your request path, and rebuilding an
            index without any downtime.
        </p>

        <h2 class="mt-10 text-xl font-semibold">
            1. Deep pagination with <code>search_after</code>
        </h2>
        <div class="mt-3"><DeepPaginationDemo /></div>

        <h2 class="mt-12 text-xl font-semibold">2. Queued indexing</h2>
        <div class="mt-3"><QueueIndexingDemo /></div>

        <h2 class="mt-12 text-xl font-semibold">
            3. Zero-downtime reindex with an alias
        </h2>
        <div class="mt-3"><AliasReindexDemo /></div>
    </section>
</template>
