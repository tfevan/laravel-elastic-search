export type JsonObject = Record<string, unknown>;

export interface DocumentInspection {
    index: string;
    scout_key: number | string;
    is_active: boolean;
    should_be_searchable: boolean;
    searchable_array: JsonObject;
    indexed_document: JsonObject | null;
}

export interface RawHit {
    id: string;
    score: number | null;
    source: JsonObject;
}

export interface RawSearchResult {
    total: number | null;
    hits: RawHit[];
}