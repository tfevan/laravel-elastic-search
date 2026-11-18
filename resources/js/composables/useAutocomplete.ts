import { onBeforeUnmount, ref, watch } from 'vue';
import { api, ApiError } from '../api/client';
import type { Suggestion } from '../types/autocomplete';

const MIN_LENGTH = 2;

export function useAutocomplete(delay: number = 250) {
    const query = ref<string>('');
    const suggestions = ref<Suggestion[]>([]);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);
    const tookMs = ref<number | null>(null);
    const sent = ref<number>(0);
    const cancelled = ref<number>(0);

    let timer: ReturnType<typeof setTimeout> | undefined;
    let controller: AbortController | null = null;
    let skipNext = false;

    function cancelPending(): void {
        clearTimeout(timer);

        if (controller !== null) {
            controller.abort();
            controller = null;
        }
    }

    async function fetchSuggestions(text: string): Promise<void> {
        if (controller !== null) {
            controller.abort();
            cancelled.value++;
        }

        const current = new AbortController();
        controller = current;
        loading.value = true;
        error.value = null;
        sent.value++;

        const started = performance.now();

        try {
            const response = await api.get<{ data: Suggestion[] }>(`/autocomplete?q=${encodeURIComponent(text)}`, current.signal);
            suggestions.value = response.data;
            tookMs.value = Math.round(performance.now() - started);
        } catch (exception) {
            if (exception instanceof DOMException && exception.name === 'AbortError') {
                return;
            }

            suggestions.value = [];
            error.value =
                exception instanceof ApiError && exception.status === 503
                    ? 'Elasticsearch is unreachable.'
                    : 'Could not load suggestions.';
        } finally {
            if (controller === current) {
                loading.value = false;
                controller = null;
            }
        }
    }

    function setQuery(value: string, silent: boolean = false): void {
        skipNext = silent;
        query.value = value;
    }

    watch(query, (value) => {
        if (skipNext) {
            skipNext = false;

            return;
        }

        const text = value.trim();

        cancelPending();

        if (text.length < MIN_LENGTH) {
            suggestions.value = [];
            loading.value = false;

            return;
        }

        timer = setTimeout(() => void fetchSuggestions(text), delay);
    });

    onBeforeUnmount(cancelPending);

    return { query, suggestions, loading, error, tookMs, sent, cancelled, setQuery };
}