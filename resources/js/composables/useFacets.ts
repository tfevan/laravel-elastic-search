import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { FacetResult, FacetSelection } from '../types/facets';

function toQueryString(selection: FacetSelection): string {
    const params = new URLSearchParams();

    if (selection.q !== '') {
        params.set('q', selection.q);
    }

    selection.categories.forEach((category) => params.append('categories[]', category));
    selection.brands.forEach((brand) => params.append('brands[]', brand));

    if (selection.priceRange !== null) {
        params.set('price_range', selection.priceRange);
    }

    if (selection.minRating !== null) {
        params.set('min_rating', selection.minRating);
    }

    return params.toString();
}

export function useFacets() {
    const result = ref<FacetResult | null>(null);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    let latest = 0;

    async function search(selection: FacetSelection): Promise<void> {
        const requestId = ++latest;
        loading.value = true;
        error.value = null;

        try {
            const response = await api.get<{ data: FacetResult }>(`/facets?${toQueryString(selection)}`);

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