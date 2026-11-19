export interface Coordinates {
    latitude: number;
    longitude: number;
}

export interface Point {
    x: number;
    y: number;
}

const KM_PER_DEGREE_LATITUDE = 110.57;
const KM_PER_DEGREE_LONGITUDE_AT_EQUATOR = 111.32;

export function offsetInKm(origin: Coordinates, target: Coordinates): Point {
    const x =
        (target.longitude - origin.longitude) *
        KM_PER_DEGREE_LONGITUDE_AT_EQUATOR *
        Math.cos((origin.latitude * Math.PI) / 180);
    const y = (target.latitude - origin.latitude) * KM_PER_DEGREE_LATITUDE;

    return { x, y };
}