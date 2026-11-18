<script setup lang="ts">
import { computed } from "vue";
import { parseHighlight } from "../utils/highlight";

const props = defineProps<{
    snippet: string | null;
    fallback: string;
    start: string;
    end: string;
}>();

const segments = computed(() =>
    parseHighlight(props.snippet ?? props.fallback, props.start, props.end),
);
</script>

<template>
    <span>
        <template v-for="(segment, index) in segments" :key="index">
            <mark
                v-if="segment.highlighted"
                class="rounded bg-yellow-200 px-0.5"
                >{{ segment.text }}</mark
            >
            <template v-else>{{ segment.text }}</template>
        </template>
    </span>
</template>
