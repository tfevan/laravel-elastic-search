import { describe, expect, it } from 'vitest';
import { pitfalls, testLayers } from './testingLayers';

describe('testing layers', () => {
    it('has unique ids', () => {
        const ids = testLayers.map((layer) => layer.id);

        expect(new Set(ids).size).toBe(ids.length);
    });

    it('gives every layer a command and something it protects', () => {
        for (const layer of testLayers) {
            expect(layer.command).not.toBe('');
            expect(layer.protects.length).toBeGreaterThan(0);
        }
    });

    it('requires Elasticsearch only for the integration layer', () => {
        const needing = testLayers.filter((layer) => layer.needsElasticsearch).map((layer) => layer.id);

        expect(needing).toEqual(['integration']);
    });

    it('lists pitfalls', () => {
        expect(pitfalls.length).toBeGreaterThan(0);
    });
});