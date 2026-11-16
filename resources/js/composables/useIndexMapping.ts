import { onMounted, ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { IndexMapping } from '../types/mapping';

export function useIndexMapping() {
    const mapping = ref<IndexMapping | null>(null);
    const error = ref<string | null>(null);

    async function load(): Promise<void> {
        error.value = null;

        try {
            mapping.value = (await api.get<{ data: IndexMapping }>('/mapping')).data;
        } catch (exception) {
            mapping.value = null;
            error.value =
                exception instanceof ApiError && exception.status === 404
                    ? exception.message
                    : 'Could not load the index mapping.';
        }
    }

    onMounted(load);

    return { mapping, error };
}