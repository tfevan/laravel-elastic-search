<script setup lang="ts">
import { chapters } from "../chapters";

const completed = chapters.filter((chapter) => chapter.component).length;
</script>

<template>
    <section class="max-w-3xl">
        <h1 class="text-3xl font-bold">Elasticsearch with Laravel Scout</h1>
        <p class="mt-3 text-gray-600">
            A hands-on tour of Elasticsearch built with Laravel, Laravel Scout
            and Vue 3. Each chapter is a small, focused page: it starts with
            beginner Scout basics and moves on to advanced queries, mappings and
            performance.
        </p>
        <p class="mt-6 text-sm text-gray-500">
            {{ completed }} of {{ chapters.length }} chapters completed.
        </p>
    </section>
</template>
