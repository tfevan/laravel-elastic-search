<script setup lang="ts">
import type { FacetBucket } from "../types/facets";

defineProps<{
    title: string;
    buckets: FacetBucket[];
    selected: string[];
}>();

const emit = defineEmits<{ toggle: [key: string] }>();
</script>

<template>
    <fieldset>
        <legend
            class="text-xs font-semibold uppercase tracking-wide text-gray-400"
        >
            {{ title }}
        </legend>
        <ul class="mt-2 space-y-1">
            <li v-for="bucket in buckets" :key="bucket.key">
                <label
                    class="flex cursor-pointer items-center justify-between gap-2 text-sm"
                    :class="
                        bucket.count === 0 && !selected.includes(bucket.key)
                            ? 'text-gray-400'
                            : 'text-gray-800'
                    "
                >
                    <span class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            :checked="selected.includes(bucket.key)"
                            @change="emit('toggle', bucket.key)"
                        />
                        {{ bucket.label }}
                    </span>
                    <span class="text-xs text-gray-500">{{
                        bucket.count
                    }}</span>
                </label>
            </li>
        </ul>
    </fieldset>
</template>
