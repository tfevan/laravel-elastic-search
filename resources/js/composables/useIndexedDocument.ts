import { ref } from 'vue';
import { api, ApiError } from '../api/client';

export function useIndexedDocument() {
    const productId = ref<number | null>(null);
    const document = ref<Record<string, unknown> | null>(null);
    const error = ref<string | null>(null);

    async function show(id: number): Promise<void> {
        productId.value = id;
        document.value = null;
        error.value = null;

        try {
            document.value = (await api.get<{ data: Record<string, unknown> }>(`/products/${id}/document`)).data;
        } catch (exception) {
            error.value =
                exception instanceof ApiError && exception.status === 404
                    ? 'This product is not indexed in Elasticsearch.'
                    : 'Could not load the indexed document.';
        }
    }

    return { productId, document, error, show };
}