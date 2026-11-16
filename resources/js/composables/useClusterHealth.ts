import { onMounted, ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { ClusterHealth } from '../types/health';

export function useClusterHealth() {
    const health = ref<ClusterHealth | null>(null);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function load(): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const response = await api.get<{ data: ClusterHealth }>('/health');
            health.value = response.data;
        } catch (exception) {
            error.value =
                exception instanceof ApiError && exception.status === 503
                    ? 'Elasticsearch is unreachable. Make sure it is running on the configured host.'
                    : 'Something went wrong while loading the cluster health.';
        } finally {
            loading.value = false;
        }
    }

    onMounted(load);

    return { health, loading, error, reload: load };
}