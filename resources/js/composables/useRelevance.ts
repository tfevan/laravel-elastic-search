import { ref } from 'vue';
import { api, ApiError } from '../api/client';
import type { RelevanceOptions, RelevanceResult, ScoreExplanation } from '../types/relevance';

function toQueryString(options: RelevanceOptions): string {
    const params = new URLSearchParams({
        name_boost: String(options.nameBoost),
        description_boost: String(options.descriptionBoost),
        operator: options.operator,
        prefer_top_rated: options.preferTopRated ? '1' : '0',
        prefer_popular: options.preferPopular ? '1' : '0',
    });

    if (options.q !== '') {
        params.set('q', options.q);
    }

    if (options.category !== '') {
        params.set('category', options.category);
    }

    if (options.excludeBrand !== '') {
        params.set('exclude_brand', options.excludeBrand);
    }

    if (options.minPrice !== null) {
        params.set('min_price', String(options.minPrice));
    }

    if (options.maxPrice !== null) {
        params.set('max_price', String(options.maxPrice));
    }

    return params.toString();
}

function messageFor(exception: unknown, fallback: string): string {
    if (exception instanceof ApiError && exception.status === 422) {
        return Object.values(exception.errors)[0]?.[0] ?? 'These options are not valid.';
    }

    if (exception instanceof ApiError && exception.status === 503) {
        return 'Elasticsearch is unreachable.';
    }

    return fallback;
}

export function useRelevance() {
    const result = ref<RelevanceResult | null>(null);
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);
    const explainingId = ref<number | null>(null);
    const explanation = ref<ScoreExplanation | null>(null);

    async function search(options: RelevanceOptions): Promise<void> {
        loading.value = true;
        error.value = null;
        explainingId.value = null;
        explanation.value = null;

        try {
            result.value = (await api.get<{ data: RelevanceResult }>(`/relevance?${toQueryString(options)}`)).data;
        } catch (exception) {
            result.value = null;
            error.value = messageFor(exception, 'Something went wrong while searching.');
        } finally {
            loading.value = false;
        }
    }

    async function explain(productId: number, options: RelevanceOptions): Promise<void> {
        explainingId.value = productId;
        explanation.value = null;

        try {
            explanation.value = (
                await api.get<{ data: ScoreExplanation }>(`/relevance/${productId}/explain?${toQueryString(options)}`)
            ).data;
        } catch (exception) {
            explainingId.value = null;
            error.value = messageFor(exception, 'Could not explain this score.');
        }
    }

    return { result, loading, error, explainingId, explanation, search, explain };
}