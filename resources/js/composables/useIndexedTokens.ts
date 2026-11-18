import { onMounted, ref } from 'vue';
import { api } from '../api/client';
import type { AnalyzeToken } from '../types/mapping';

export function useIndexedTokens(text: string, analyzer: string) {
    const tokens = ref<AnalyzeToken[]>([]);
    const error = ref<string | null>(null);

    onMounted(async () => {
        try {
            const response = await api.get<{ data: { tokens: AnalyzeToken[] } }>(
                `/mapping/analyze?analyzer=${analyzer}&text=${encodeURIComponent(text)}`,
            );
            tokens.value = response.data.tokens;
        } catch {
            error.value = 'Could not load the indexed tokens. Did you run php artisan elastic:migrate?';
        }
    });

    return { tokens, error };
}