<script setup lang="ts">
import { chapters } from "../chapters";
import type { ChapterLevel } from "../chapters";

const levels: { key: ChapterLevel; label: string }[] = [
    { key: "beginner", label: "Part 1: Beginner" },
    { key: "advanced", label: "Part 2: Advanced" },
];

const chaptersOf = (level: ChapterLevel) =>
    chapters.filter((chapter) => chapter.level === level);
</script>

<template>
    <div class="flex min-h-screen bg-gray-50 text-gray-900">
        <aside class="w-72 shrink-0 border-r border-gray-200 bg-white p-6">
            <RouterLink to="/" class="block text-lg font-bold"
                >Elasticsearch + Laravel</RouterLink
            >
            <p class="mt-1 text-sm text-gray-500">Scout &amp; Vue 3 showcase</p>

            <nav class="mt-8 space-y-6">
                <section v-for="level in levels" :key="level.key">
                    <h2
                        class="text-xs font-semibold uppercase tracking-wide text-gray-400"
                    >
                        {{ level.label }}
                    </h2>
                    <ul class="mt-2 space-y-1">
                        <li
                            v-for="chapter in chaptersOf(level.key)"
                            :key="chapter.number"
                        >
                            <RouterLink
                                v-if="chapter.component"
                                :to="chapter.path"
                                class="block rounded px-3 py-1.5 text-sm hover:bg-gray-100"
                                active-class="bg-indigo-50 font-medium text-indigo-700"
                            >
                                {{ chapter.number }}. {{ chapter.title }}
                            </RouterLink>
                            <span
                                v-else
                                class="block cursor-not-allowed px-3 py-1.5 text-sm text-gray-400"
                            >
                                {{ chapter.number }}. {{ chapter.title }}
                            </span>
                        </li>
                    </ul>
                </section>
            </nav>
        </aside>

        <main class="flex-1 p-10">
            <slot />
        </main>
    </div>
</template>
