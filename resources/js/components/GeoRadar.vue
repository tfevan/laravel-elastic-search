<script setup lang="ts">
import { computed } from "vue";
import type { GeoStore } from "../types/geo";
import { offsetInKm } from "../utils/geo";

const props = defineProps<{
    latitude: number;
    longitude: number;
    radiusKm: number;
    stores: GeoStore[];
}>();

const SIZE = 400;
const CENTER = SIZE / 2;
const RADIUS_PX = 180;

const pixelsPerKm = computed(() => RADIUS_PX / props.radiusKm);

const dots = computed(() =>
    props.stores.map((store) => {
        const offset = offsetInKm(
            { latitude: props.latitude, longitude: props.longitude },
            { latitude: store.latitude, longitude: store.longitude },
        );

        return {
            store,
            x: CENTER + offset.x * pixelsPerKm.value,
            y: CENTER - offset.y * pixelsPerKm.value,
        };
    }),
);
</script>

<template>
    <svg
        :viewBox="`0 0 ${SIZE} ${SIZE}`"
        class="w-full rounded border border-gray-200 bg-white"
        role="img"
        :aria-label="`${stores.length} stores within ${radiusKm} km`"
    >
        <circle
            :cx="CENTER"
            :cy="CENTER"
            :r="RADIUS_PX"
            class="fill-indigo-50 stroke-indigo-300"
            stroke-width="1"
        />
        <circle
            :cx="CENTER"
            :cy="CENTER"
            :r="RADIUS_PX / 2"
            class="fill-none stroke-indigo-200"
            stroke-width="1"
            stroke-dasharray="4 4"
        />
        <line
            :x1="CENTER"
            :y1="CENTER - RADIUS_PX"
            :x2="CENTER"
            :y2="CENTER + RADIUS_PX"
            class="stroke-indigo-100"
        />
        <line
            :x1="CENTER - RADIUS_PX"
            :y1="CENTER"
            :x2="CENTER + RADIUS_PX"
            :y2="CENTER"
            class="stroke-indigo-100"
        />

        <text
            :x="CENTER"
            :y="CENTER - RADIUS_PX - 4"
            text-anchor="middle"
            class="fill-gray-500 text-[10px]"
        >
            N
        </text>
        <text
            :x="CENTER + RADIUS_PX - 4"
            :y="CENTER - 6"
            text-anchor="end"
            class="fill-gray-500 text-[10px]"
        >
            {{ radiusKm }} km
        </text>
        <text
            :x="CENTER + RADIUS_PX / 2 - 4"
            :y="CENTER - 6"
            text-anchor="end"
            class="fill-gray-400 text-[10px]"
        >
            {{ (radiusKm / 2).toFixed(1) }} km
        </text>

        <circle
            v-for="dot in dots"
            :key="dot.store.id"
            :cx="dot.x"
            :cy="dot.y"
            r="5"
            class="fill-indigo-600 stroke-white"
            stroke-width="1.5"
        >
            <title>{{ dot.store.name }} ({{ dot.store.distance_km }} km)</title>
        </circle>

        <circle
            :cx="CENTER"
            :cy="CENTER"
            r="6"
            class="fill-red-500 stroke-white"
            stroke-width="2"
        >
            <title>You are here</title>
        </circle>
    </svg>
</template>
