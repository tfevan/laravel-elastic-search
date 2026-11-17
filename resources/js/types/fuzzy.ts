import type { RelevanceHit } from './relevance';

export type Fuzziness = '0' | '1' | '2' | 'AUTO';

export interface FuzzyResult {
    total: number;
    hits: RelevanceHit[];
    counts: Record<Fuzziness, number>;
    suggestion: string | null;
}