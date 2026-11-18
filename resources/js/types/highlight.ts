import type { Product } from './product';

export interface HighlightHit {
    id: number;
    score: number | null;
    product: Product;
    highlight: {
        name: string | null;
        description: string | null;
    };
}

export interface HighlightResult {
    total: number;
    markers: {
        start: string;
        end: string;
    };
    hits: HighlightHit[];
}