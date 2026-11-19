<script setup lang="ts">
import type { TestLayer } from "../../data/testingLayers";

defineProps<{ layer: TestLayer; step: number }>();
</script>

<template>
    <article class="rounded border border-gray-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">{{ step }}. {{ layer.title }}</h3>
            <span
                class="rounded px-2 py-0.5 text-xs"
                :class="
                    layer.needsElasticsearch
                        ? 'bg-indigo-100 text-indigo-800'
                        : 'bg-gray-100 text-gray-600'
                "
            >
                {{
                    layer.needsElasticsearch
                        ? "needs Elasticsearch"
                        : "no Elasticsearch"
                }}
            </span>
        </div>
        <p class="mt-1 text-sm text-gray-500">
            {{ layer.tool }} &middot; {{ layer.speed }}
        </p>
        <code
            class="mt-3 block rounded bg-gray-900 px-3 py-2 text-sm text-gray-100"
            >{{ layer.command }}</code
        >
        <ul class="mt-3 list-inside list-disc text-sm text-gray-600">
            <li v-for="item in layer.protects" :key="item">{{ item }}</li>
        </ul>
        <p class="mt-3 text-xs text-gray-400">{{ layer.example }}</p>
    </article>
</template>
