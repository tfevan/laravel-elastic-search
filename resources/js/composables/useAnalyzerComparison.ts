import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { AnalyzeToken } from '../types/mapping';

export const analyzers: string[] = ['standard', 'english', 'product_index', 'product_search'];

export function useAnalyzerComparison() {
    const results = ref<Record<string, AnalyzeToken[]>>({});
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function compare(text: string): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const entries = await Promise.all(
                analyzers.map(async (analyzer) => {
                    const response = await api.get<{ data: { tokens: AnalyzeToken[] } }>(
                        `/mapping/analyze?analyzer=${analyzer}&text=${encodeURIComponent(text)}`,
                    );

                    return [analyzer, response.data.tokens] as const;
                }),
            );

            results.value = Object.fromEntries(entries);
        } catch (exception) {
            results.value = {};
            error.value =
                exception instanceof ApiError && exception.status === 404
                    ? exception.message
                    : 'Could not analyze the text.';
        } finally {
            loading.value = false;
        }
    }

    return { results, loading, error, compare };
}