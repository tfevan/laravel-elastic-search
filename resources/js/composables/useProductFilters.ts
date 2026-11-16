import { onMounted, ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { FilterOptions, FilterState } from '../types/filters';
import type { Product } from '../types/product';

function toQueryString(state: FilterState): string {
    const params = new URLSearchParams();

    if (state.q !== '') {
        params.set('q', state.q);
    }

    if (state.category !== '') {
        params.set('category', state.category);
    }

    state.brands.forEach((brand) => params.append('brands[]', brand));

    if (state.minPrice !== null) {
        params.set('min_price', String(state.minPrice));
    }

    if (state.maxPrice !== null) {
        params.set('max_price', String(state.maxPrice));
    }

    params.set('sort', state.sort);

    return params.toString();
}

export function useProductFilters() {
    const options = ref<FilterOptions>({ categories: [], brands: [] });
    const results = ref<Product[]>([]);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function loadOptions(): Promise<void> {
        try {
            options.value = (await api.get<{ data: FilterOptions }>('/filters/options')).data;
        } catch {
            error.value = 'Could not load the filter options.';
        }
    }

    async function apply(state: FilterState): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            results.value = (await api.get<{ data: Product[] }>(`/filters?${toQueryString(state)}`)).data;
        } catch (exception) {
            results.value = [];

            if (exception instanceof ApiError && exception.status === 422) {
                error.value = Object.values(exception.errors)[0]?.[0] ?? 'These filters are not valid.';
            } else if (exception instanceof ApiError && exception.status === 503) {
                error.value = 'Elasticsearch is unreachable.';
            } else {
                error.value = 'Something went wrong while filtering.';
            }
        } finally {
            loading.value = false;
        }
    }

    onMounted(loadOptions);

    return { options, results, loading, error, apply };
}