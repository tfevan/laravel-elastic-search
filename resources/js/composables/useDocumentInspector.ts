import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { DocumentInspection } from '../types/document';

export function useDocumentInspector() {
    const inspection = ref<DocumentInspection | null>(null);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function inspect(productId: number): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            inspection.value = (await api.get<{ data: DocumentInspection }>(`/documents/${productId}`)).data;
        } catch (exception) {
            inspection.value = null;
            error.value =
                exception instanceof ApiError && exception.status === 404
                    ? `Product #${productId} does not exist.`
                    : 'Could not inspect this product.';
        } finally {
            loading.value = false;
        }
    }

    async function setActive(productId: number, isActive: boolean): Promise<void> {
        try {
            await api.patch(`/products/${productId}/activation`, { is_active: isActive });
            await inspect(productId);
        } catch {
            error.value = 'Could not change the product status.';
        }
    }

    return { inspection, loading, error, inspect, setActive };
}