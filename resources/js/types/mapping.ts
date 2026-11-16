export interface FieldMapping {
    type: string;
    analyzer?: string;
    search_analyzer?: string;
    scaling_factor?: number;
    fields?: Record<string, { type: string }>;
}

export interface IndexMapping {
    mapping: {
        dynamic?: string;
        properties: Record<string, FieldMapping>;
    };
    settings: {
        shards: number;
        replicas: number;
        analyzers: string[];
        filters: string[];
    };
}

export interface AnalyzeToken {
    token: string;
    position: number;
    type: string;
}