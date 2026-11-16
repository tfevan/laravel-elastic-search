import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { PaginatedResponse, PaginationMeta } from '../types/api';
import type { Product } from '../types/product';

export function usePaginatedSearch() {
    const results = ref<Product[]>([]);
    const meta = ref<PaginationMeta | null>(null);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function load(query: string, page: number, perPage: number): Promise<void> {
        loading.value = true;
        error.value = null;

        const params = new URLSearchParams({ page: String(page), per_page: String(perPage) });

        if (query !== '') {
            params.set('q', query);
        }

        try {
            const response = await api.get<PaginatedResponse<Product>>(`/pagination?${params.toString()}`);
            results.value = response.data;
            meta.value = response.meta;
        } catch (exception) {
            results.value = [];
            meta.value = null;

            if (exception instanceof ApiError && exception.status === 422) {
                error.value = Object.values(exception.errors)[0]?.[0] ?? exception.message;
            } else if (exception instanceof ApiError && exception.status === 503) {
                error.value = 'Elasticsearch is unreachable.';
            } else {
                error.value = 'Something went wrong while loading the results.';
            }
        } finally {
            loading.value = false;
        }
    }

    return { results, meta, loading, error, load };
}