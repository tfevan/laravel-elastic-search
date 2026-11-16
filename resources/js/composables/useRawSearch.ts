import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { RawSearchResult } from '../types/document';

export function useRawSearch() {
    const result = ref<RawSearchResult | null>(null);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function search(query: string): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            result.value = (await api.get<{ data: RawSearchResult }>(`/documents/raw?q=${encodeURIComponent(query)}`)).data;
        } catch (exception) {
            result.value = null;
            error.value =
                exception instanceof ApiError && exception.status === 422
                    ? 'This query has invalid syntax.'
                    : 'Something went wrong while searching.';
        } finally {
            loading.value = false;
        }
    }

    return { result, loading, error, search };
}