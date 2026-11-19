export type DeepSort = 'price_asc' | 'price_desc' | 'rating_desc' | 'name_asc';

export interface DeepItem {
    id: number;
    name: string;
    category: string;
    brand: string;
    price: number;
    rating: number;
}

export interface DeepPage {
    items: DeepItem[];
    cursor: string | null;
    took_ms: number;
}

export interface IndexingStatus {
    queued: boolean;
    queue_connection: string;
    pending_jobs: number | null;
}

export interface TouchResult extends IndexingStatus {
    touched: number;
    elapsed_ms: number;
}

export interface AliasIndex {
    name: string;
    documents: number;
    size: string;
    current: boolean;
}

export interface AliasStatus {
    alias: string;
    index: string | null;
    documents: number;
    indices: AliasIndex[];
}

export interface ReindexStep {
    step: string;
    ms: number;
}

export interface ReindexResult {
    from: string;
    to: string;
    copied: number;
    steps: ReindexStep[];
    status: AliasStatus;
}