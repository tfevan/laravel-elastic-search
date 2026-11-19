import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { DeepItem, DeepPage, DeepSort } from '../types/performance';

export function useDeepPagination() {
    const sort = ref<DeepSort>('price_asc');
    const size = ref<number>(20);
    const items = ref<DeepItem[]>([]);
    const cursor = ref<string | null>(null);
    const pages = ref<number>(0);
    const tookMs = ref<number[]>([]);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    async function load(restart: boolean): Promise<void> {
        loading.value = true;
        error.value = null;

        const params = new URLSearchParams({ sort: sort.value, size: String(size.value) });

        if (!restart && cursor.value !== null) {
            params.set('cursor', cursor.value);
        }

        try {
            const response = await api.get<{ data: DeepPage }>(`/performance/deep-pagination?${params.toString()}`);

            if (restart) {
                items.value = [];
                pages.value = 0;
                tookMs.value = [];
            }

            items.value.push(...response.data.items);
            cursor.value = response.data.cursor;
            pages.value++;
            tookMs.value.push(response.data.took_ms);
        } catch (exception) {
            error.value =
                exception instanceof ApiError && exception.status === 422
                    ? exception.message
                    : 'Something went wrong while loading the page.';
        } finally {
            loading.value = false;
        }
    }

    async function start(): Promise<void> {
        await load(true);
    }

    async function next(): Promise<void> {
        await load(false);
    }

    async function walkAll(): Promise<void> {
        await start();

        while (cursor.value !== null && error.value === null) {
            await next();
        }
    }

    return { sort, size, items, cursor, pages, tookMs, loading, error, start, next, walkAll };
}