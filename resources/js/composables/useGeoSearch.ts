import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { GeoResult, GeoSearch } from '../types/geo';

function isValid(search: GeoSearch): boolean {
    return (
        Number.isFinite(search.latitude) &&
        Number.isFinite(search.longitude) &&
        Math.abs(search.latitude) <= 90 &&
        Math.abs(search.longitude) <= 180
    );
}

export function useGeoSearch() {
    const result = ref<GeoResult | null>(null);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    let latest = 0;

    async function search(input: GeoSearch): Promise<void> {
        if (!isValid(input)) {
            return;
        }

        const requestId = ++latest;
        loading.value = true;
        error.value = null;

        const params = new URLSearchParams({
            latitude: String(input.latitude),
            longitude: String(input.longitude),
            radius_km: String(input.radiusKm),
        });

        if (input.type !== '') {
            params.set('type', input.type);
        }

        if (input.q !== '') {
            params.set('q', input.q);
        }

        try {
            const response = await api.get<{ data: GeoResult }>(`/geo?${params.toString()}`);

            if (requestId === latest) {
                result.value = response.data;
            }
        } catch (exception) {
            if (requestId === latest) {
                error.value =
                    exception instanceof ApiError && exception.status === 503
                        ? 'Elasticsearch is unreachable.'
                        : 'Something went wrong while searching.';
            }
        } finally {
            if (requestId === latest) {
                loading.value = false;
            }
        }
    }

    return { result, loading, error, search };
}