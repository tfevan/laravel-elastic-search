export type SortOption = 'relevance' | 'price_asc' | 'price_desc' | 'rating_desc' | 'name_asc';

export interface FilterOptions {
    categories: string[];
    brands: string[];
}

export interface FilterState {
    q: string;
    category: string;
    brands: string[];
    minPrice: number | null;
    maxPrice: number | null;
    sort: SortOption;
}