export interface Product {
    id: number;
    name: string;
    description: string;
    category: string;
    brand: string;
    price: number;
    rating: number;
    tags: string[];
}

export type ProductInput = Omit<Product, 'id'>;

export interface IndexSummary {
    database: number;
    elasticsearch: number;
}

export interface ProductIndexSummary {
    database: number;
    elasticsearch: number;
}