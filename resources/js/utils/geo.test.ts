import { describe, expect, it } from 'vitest';
import { offsetInKm } from './geo';

const gulshan = { latitude: 23.7925, longitude: 90.4078 };

describe('offsetInKm', () => {
    it('is zero for the same point', () => {
        expect(offsetInKm(gulshan, gulshan)).toEqual({ x: 0, y: 0 });
    });

    it('measures north as a positive y offset', () => {
        const point = offsetInKm(gulshan, { latitude: gulshan.latitude + 0.01, longitude: gulshan.longitude });

        expect(point.x).toBeCloseTo(0, 5);
        expect(point.y).toBeCloseTo(1.11, 1);
    });

    it('measures east as a positive x offset, shortened by the latitude', () => {
        const point = offsetInKm(gulshan, { latitude: gulshan.latitude, longitude: gulshan.longitude + 0.01 });

        expect(point.y).toBeCloseTo(0, 5);
        expect(point.x).toBeCloseTo(1.02, 1);
    });

    it('measures south and west as negative offsets', () => {
        const point = offsetInKm(gulshan, { latitude: gulshan.latitude - 0.01, longitude: gulshan.longitude - 0.01 });

        expect(point.x).toBeLessThan(0);
        expect(point.y).toBeLessThan(0);
    });
});