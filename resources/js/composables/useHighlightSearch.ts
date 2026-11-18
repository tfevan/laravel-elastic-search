import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { HighlightResult } from '../types/highlight';

export function useHighlightSearch() {
    const result = ref<HighlightResult | null>(null);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function search(query: string, fuzzy: boolean): Promise<void> {
        loading.value = true;
        error.value = null;

        const params = new URLSearchParams({ q: query, fuzzy: fuzzy ? '1' : '0' });

        try {
            result.value = (await api.get<{ data: HighlightResult }>(`/highlight?${params.toString()}`)).data;
        } catch (exception) {
            result.value = null;
            error.value =
                exception instanceof ApiError && exception.status === 503
                    ? 'Elasticsearch is unreachable.'
                    : 'Something went wrong while searching.';
        } finally {
            loading.value = false;
        }
    }

    return { result, loading, error, search };
}