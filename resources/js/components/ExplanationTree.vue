<script setup lang="ts">
import type { ExplanationNode } from "../types/relevance";

defineProps<{ node: ExplanationNode }>();
</script>

<template>
    <li class="py-0.5">
        <span class="font-mono text-xs font-semibold text-indigo-700">{{
            node.value.toFixed(3)
        }}</span>
        <span class="ml-2 text-xs text-gray-700">{{ node.description }}</span>
        <ul v-if="node.details" class="ml-4 mt-1 border-l border-gray-200 pl-3">
            <ExplanationTree
                v-for="(child, index) in node.details"
                :key="index"
                :node="child"
            />
        </ul>
    </li>
</template>
