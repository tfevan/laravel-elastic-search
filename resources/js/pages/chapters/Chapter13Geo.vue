<script setup lang="ts">
import { ref, watch } from "vue";
import GeoRadar from "../../components/GeoRadar.vue";
import { useGeoSearch } from "../../composables/useGeoSearch";
import { dhakaLocations, storeTypes } from "../../data/dhakaLocations";
import type { GeoSearch } from "../../types/geo";

const { result, loading, error, search } = useGeoSearch();

const state = ref<GeoSearch>({
    latitude: dhakaLocations[0].latitude,
    longitude: dhakaLocations[0].longitude,
    radiusKm: 3,
    type: "",
    q: "",
});
const preset = ref<string>(dhakaLocations[0].name);
const locationMessage = ref<string | null>(null);

watch(state, () => void search(state.value), { deep: true, immediate: true });

function choosePreset(): void {
    const location = dhakaLocations.find((item) => item.name === preset.value);

    if (location) {
        state.value.latitude = location.latitude;
        state.value.longitude = location.longitude;
    }
}

function useMyLocation(): void {
    locationMessage.value = null;

    if (!("geolocation" in navigator)) {
        locationMessage.value = "Your browser does not support geolocation.";

        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            preset.value = "";
            state.value.latitude = Number(position.coords.latitude.toFixed(5));
            state.value.longitude = Number(
                position.coords.longitude.toFixed(5),
            );
        },
        () => {
            locationMessage.value =
                "Could not read your location. Allow location access or choose an area.";
        },
    );
}
</script>

<template>
    <section class="max-w-6xl">
        <p class="text-sm font-medium text-indigo-600">
            Chapter 13 &middot; Advanced
        </p>
        <h1 class="mt-1 text-3xl font-bold">Geo Search</h1>
        <p class="mt-3 text-gray-600">
            A <code>geo_point</code> field stores a latitude and longitude.
            Elasticsearch can then filter documents within a distance of a point
            (<code>geo_distance</code>) and sort them by that distance. The
            stores below are spread over Dhaka.
        </p>

        <div
            class="mt-6 grid gap-4 rounded border border-gray-200 bg-white p-5 md:grid-cols-3"
        >
            <label class="block text-sm">
                <span class="text-gray-600">Start from</span>
                <select
                    v-model="preset"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    @change="choosePreset"
                >
                    <option
                        v-for="location in dhakaLocations"
                        :key="location.name"
                        :value="location.name"
                    >
                        {{ location.name }}
                    </option>
                    <option value="">Custom</option>
                </select>
            </label>
            <label class="block text-sm">
                <span class="text-gray-600">Latitude</span>
                <input
                    v-model.number="state.latitude"
                    type="number"
                    step="0.0001"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    @input="preset = ''"
                />
            </label>
            <label class="block text-sm">
                <span class="text-gray-600">Longitude</span>
                <input
                    v-model.number="state.longitude"
                    type="number"
                    step="0.0001"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                    @input="preset = ''"
                />
            </label>

            <label class="block text-sm">
                <span class="text-gray-600"
                    >Radius: {{ state.radiusKm }} km</span
                >
                <input
                    v-model.number="state.radiusKm"
                    type="range"
                    min="1"
                    max="15"
                    step="0.5"
                    class="w-full"
                />
            </label>
            <label class="block text-sm">
                <span class="text-gray-600">Store type</span>
                <select
                    v-model="state.type"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                >
                    <option value="">Any</option>
                    <option
                        v-for="type in storeTypes"
                        :key="type"
                        :value="type"
                    >
                        {{ type }}
                    </option>
                </select>
            </label>
            <label class="block text-sm">
                <span class="text-gray-600">Name contains</span>
                <input
                    v-model="state.q"
                    type="text"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-1.5"
                />
            </label>

            <div class="md:col-span-3">
                <button
                    type="button"
                    class="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50"
                    @click="useMyLocation"
                >
                    Use my location
                </button>
                <span
                    v-if="locationMessage"
                    class="ml-3 text-sm text-yellow-800"
                    >{{ locationMessage }}</span
                >
                <span class="ml-3 text-xs text-gray-500"
                    >The sample stores are in Dhaka, so results outside it will
                    be empty.</span
                >
            </div>
        </div>

        <p
            v-if="error"
            class="mt-6 rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700"
        >
            {{ error }}
        </p>

        <div
            v-else-if="result"
            class="mt-6 grid gap-6 lg:grid-cols-2"
            :class="{ 'opacity-60': loading }"
        >
            <div>
                <GeoRadar
                    :latitude="state.latitude"
                    :longitude="state.longitude"
                    :radius-km="state.radiusKm"
                    :stores="result.stores"
                />

                <h2 class="mt-6 text-sm font-semibold">
                    Stores by distance (ignores the radius)
                </h2>
                <dl class="mt-2 grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div
                        v-for="ring in result.rings"
                        :key="ring.label"
                        class="rounded border border-gray-200 bg-white p-3"
                    >
                        <dt class="text-xs uppercase text-gray-400">
                            {{ ring.label }}
                        </dt>
                        <dd class="mt-1 text-xl font-semibold">
                            {{ ring.count }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div>
                <h2 class="text-lg font-semibold">
                    {{ result.total }} store(s) within {{ state.radiusKm }} km
                </h2>
                <p v-if="result.total === 0" class="mt-2 text-sm text-gray-500">
                    Nothing nearby. Increase the radius or change the filters.
                </p>

                <ol class="mt-3 space-y-2">
                    <li
                        v-for="store in result.stores"
                        :key="store.id"
                        class="flex items-center justify-between gap-4 rounded border border-gray-200 bg-white px-4 py-3"
                    >
                        <div>
                            <p class="text-sm font-medium">{{ store.name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ store.type }} &middot; {{ store.area }}
                            </p>
                        </div>
                        <p
                            class="shrink-0 text-sm font-semibold text-indigo-700"
                        >
                            {{ store.distance_km.toFixed(2) }} km
                        </p>
                    </li>
                </ol>
                <p
                    v-if="result.total > result.stores.length"
                    class="mt-2 text-xs text-gray-500"
                >
                    Showing the nearest {{ result.stores.length }}.
                </p>
            </div>
        </div>

        <details v-if="result" class="mt-8">
            <summary class="cursor-pointer text-sm font-semibold">
                The Elasticsearch request that was sent
            </summary>
            <pre
                class="mt-2 max-h-96 overflow-auto rounded bg-gray-900 p-4 text-xs text-green-300"
                >{{ JSON.stringify(result.query, null, 2) }}</pre
            >
        </details>
    </section>
</template>
