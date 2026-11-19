export interface FacetBucket {
    key: string;
    label: string;
    count: number;
}

export interface FacetHit {
    id: number;
    score: number | null;
    name: string;
    description: string;
    category: string;
    brand: string;
    price: number;
    rating: number;
    tags: string[];
}

export interface FacetResult {
    total: number;
    hits: FacetHit[];
    facets: {
        categories: FacetBucket[];
        brands: FacetBucket[];
        price_ranges: FacetBucket[];
        ratings: FacetBucket[];
    };
    summary: {
        average_rating: number | null;
        min_price: number | null;
        max_price: number | null;
    };
    query: unknown;
}

export interface FacetSelection {
    q: string;
    categories: string[];
    brands: string[];
    priceRange: string | null;
    minRating: string | null;
}