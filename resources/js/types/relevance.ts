import type { Product } from './product';

export interface RelevanceOptions {
    q: string;
    nameBoost: number;
    descriptionBoost: number;
    operator: 'or' | 'and';
    preferTopRated: boolean;
    preferPopular: boolean;
    category: string;
    excludeBrand: string;
    minPrice: number | null;
    maxPrice: number | null;
}

export interface RelevanceHit {
    id: number;
    score: number | null;
    product: Product;
}

export interface RelevanceResult {
    total: number | null;
    query: unknown;
    hits: RelevanceHit[];
}

export interface ExplanationNode {
    value: number;
    description: string;
    details?: ExplanationNode[];
}

export interface ScoreExplanation {
    matched: boolean;
    explanation: ExplanationNode | null;
}