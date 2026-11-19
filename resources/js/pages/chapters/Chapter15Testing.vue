<script setup lang="ts">
import TestLayerCard from "../../components/testing/TestLayerCard.vue";
import { pitfalls, testLayers } from "../../data/testingLayers";
</script>

<template>
    <section class="max-w-4xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 15 &middot; Advanced
        </p>
        <h1 class="mt-1 text-3xl font-bold">Testing</h1>
        <p class="mt-3 text-gray-600">
            Search code is easy to break silently: a mapping change, an analyzer
            tweak or a wrong boost still returns
            <em>some</em> results. This project tests it in three layers, from
            fast and isolated to slow and realistic.
        </p>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <TestLayerCard
                v-for="(layer, index) in testLayers"
                :key="layer.id"
                :layer="layer"
                :step="index + 1"
            />
        </div>

        <h2 class="mt-10 text-xl font-semibold">
            How the integration tests stay safe
        </h2>
        <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-gray-600">
            <li v-for="pitfall in pitfalls" :key="pitfall">{{ pitfall }}</li>
        </ul>

        <h2 class="mt-10 text-xl font-semibold">What CI runs</h2>
        <ol
            class="mt-3 list-inside list-decimal space-y-1 text-sm text-gray-600"
        >
            <li>
                <strong>backend</strong> (PHP 8.3 and 8.4): Pint, Larastan,
                feature tests.
            </li>
            <li>
                <strong>frontend</strong>: vue-tsc, Vitest, production build.
            </li>
            <li>
                <strong>integration</strong>: a real Elasticsearch 9 service
                container and the tests in <code>tests/Integration</code>.
            </li>
        </ol>
    </section>
</template>
