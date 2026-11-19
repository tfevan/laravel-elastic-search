export interface GeoStore {
    id: number;
    name: string;
    type: string;
    area: string;
    city: string;
    latitude: number;
    longitude: number;
    distance_km: number;
}

export interface GeoRing {
    label: string;
    count: number;
}

export interface GeoResult {
    total: number;
    stores: GeoStore[];
    rings: GeoRing[];
    query: unknown;
}

export interface GeoSearch {
    latitude: number;
    longitude: number;
    radiusKm: number;
    type: string;
    q: string;
}