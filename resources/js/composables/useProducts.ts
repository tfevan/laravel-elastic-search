import { onMounted, ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { ValidationErrors } from '../api/client';
import type { PaginatedResponse } from '../types/api';
import type { Product, ProductIndexSummary, ProductInput } from '../types/product';

export function useProducts() {
    const products = ref<Product[]>([]);
    const page = ref<number>(1);
    const lastPage = ref<number>(1);
    const summary = ref<ProductIndexSummary | null>(null);
    const loading = ref<boolean>(false);
    const errors = ref<ValidationErrors>({});
    const message = ref<string | null>(null);

    async function loadSummary(): Promise<void> {
        summary.value = (await api.get<{ data: ProductIndexSummary }>('/products/index-summary')).data;
    }

    async function load(targetPage: number = page.value): Promise<void> {
        loading.value = true;
        message.value = null;

        try {
            const response = await api.get<PaginatedResponse<Product>>(`/products?page=${targetPage}`);
            products.value = response.data;
            page.value = response.meta.current_page;
            lastPage.value = response.meta.last_page;
            await loadSummary();
        } catch {
            message.value = 'Could not load products. Is Elasticsearch running?';
        } finally {
            loading.value = false;
        }
    }

    async function save(input: ProductInput, id: number | null): Promise<boolean> {
        errors.value = {};
        message.value = null;

        try {
            if (id === null) {
                await api.post('/products', input);
            } else {
                await api.put(`/products/${id}`, input);
            }

            await load(id === null ? 1 : page.value);

            return true;
        } catch (exception) {
            if (exception instanceof ApiError && exception.status === 422) {
                errors.value = exception.errors;
            } else {
                message.value = 'Could not save the product.';
            }

            return false;
        }
    }

    async function remove(id: number): Promise<void> {
        message.value = null;

        try {
            await api.delete(`/products/${id}`);
            await load();
        } catch {
            message.value = 'Could not delete the product.';
        }
    }

    onMounted(() => load(1));

    return { products, page, lastPage, summary, loading, errors, message, load, save, remove };
}