export type ClusterStatus = 'green' | 'yellow' | 'red';

export interface IndexSummary {
    name: string;
    health: string;
    status: string;
    documents: number;
    size: string;
}

export interface ClusterHealth {
    cluster: {
        name: string;
        status: ClusterStatus;
        nodes: number;
    };
    version: string;
    indices: IndexSummary[];
}