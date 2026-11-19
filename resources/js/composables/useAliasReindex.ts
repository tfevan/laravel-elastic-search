import { onMounted, ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { AliasStatus, ReindexResult } from '../types/performance';

export function useAliasReindex() {
    const status = ref<AliasStatus | null>(null);
    const lastRun = ref<ReindexResult | null>(null);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function run(action: () => Promise<void>): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            await action();
        } catch (exception) {
            error.value =
                exception instanceof ApiError && exception.status === 409
                    ? exception.message
                    : 'Something went wrong. If a previous run stopped half way, press Reset and try again.';
        } finally {
            loading.value = false;
        }
    }

    async function refresh(): Promise<void> {
        await run(async () => {
            status.value = (await api.get<{ data: AliasStatus }>('/performance/catalog')).data;
        });
    }

    async function reindex(): Promise<void> {
        await run(async () => {
            lastRun.value = (await api.post<{ data: ReindexResult }>('/performance/catalog/reindex', {})).data;
            status.value = lastRun.value.status;
        });
    }

    async function rollback(): Promise<void> {
        await run(async () => {
            lastRun.value = null;
            status.value = (await api.post<{ data: AliasStatus }>('/performance/catalog/rollback', {})).data;
        });
    }

    async function reset(): Promise<void> {
        await run(async () => {
            lastRun.value = null;
            status.value = (await api.delete('/performance/catalog').then(() => api.get<{ data: AliasStatus }>('/performance/catalog'))).data;
        });
    }

    onMounted(refresh);

    return { status, lastRun, loading, error, reindex, rollback, reset };
}