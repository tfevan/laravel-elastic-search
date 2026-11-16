import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { Product } from '../types/product';

export function useProductSearch() {
    const results = ref<Product[]>([]);
    const searched = ref<boolean>(false);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function search(query: string): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const response = await api.get<{ data: Product[] }>(`/search?q=${encodeURIComponent(query)}`);
            results.value = response.data;
            searched.value = true;
        } catch (exception) {
            results.value = [];

            if (exception instanceof ApiError && exception.status === 422) {
                error.value = 'This query has invalid syntax. Check brackets and quotes.';
            } else if (exception instanceof ApiError && exception.status === 503) {
                error.value = 'Elasticsearch is unreachable.';
            } else {
                error.value = 'Something went wrong while searching.';
            }
        } finally {
            loading.value = false;
        }
    }

    return { results, searched, loading, error, search };
}