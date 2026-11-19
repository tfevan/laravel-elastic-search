import { onMounted, ref } from 'vue';
import { api } from '../api/client';
import type { IndexingStatus, TouchResult } from '../types/performance';

export function useQueueIndexing() {
    const status = ref<IndexingStatus | null>(null);
    const runs = ref<TouchResult[]>([]);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function refresh(): Promise<void> {
        try {
            status.value = (await api.get<{ data: IndexingStatus }>('/performance/indexing')).data;
        } catch {
            error.value = 'Could not load the indexing status.';
        }
    }

    async function touch(count: number): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const result = (await api.post<{ data: TouchResult }>('/performance/indexing/touch', { count })).data;
            runs.value.unshift(result);
            status.value = result;
        } catch {
            error.value = 'Could not re-index the products.';
        } finally {
            loading.value = false;
        }
    }

    onMounted(refresh);

    return { status, runs, loading, error, refresh, touch };
}