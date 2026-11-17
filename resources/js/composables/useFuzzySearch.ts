import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { Fuzziness, FuzzyResult } from '../types/fuzzy';

export function useFuzzySearch() {
    const result = ref<FuzzyResult | null>(null);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function search(query: string, fuzziness: Fuzziness, prefixLength: number): Promise<void> {
        loading.value = true;
        error.value = null;

        const params = new URLSearchParams({ q: query, fuzziness, prefix_length: String(prefixLength) });

        try {
            result.value = (await api.get<{ data: FuzzyResult }>(`/fuzzy?${params.toString()}`)).data;
        } catch (exception) {
            result.value = null;

            if (exception instanceof ApiError && exception.status === 422) {
                error.value = Object.values(exception.errors)[0]?.[0] ?? 'This search is not valid.';
            } else if (exception instanceof ApiError && exception.status === 503) {
                error.value = 'Elasticsearch is unreachable.';
            } else {
                error.value = 'Something went wrong while searching.';
            }
        } finally {
            loading.value = false;
        }
    }

    return { result, loading, error, search };
}