import { describe, expect, it } from 'vitest';
import { pageWindow } from './pagination';

describe('pageWindow', () => {
    it('lists every page when there are few', () => {
        expect(pageWindow(2, 4)).toEqual([1, 2, 3, 4]);
    });

    it('collapses distant pages into gaps around the current page', () => {
        expect(pageWindow(50, 100)).toEqual([1, 'gap', 48, 49, 50, 51, 52, 'gap', 100]);
    });

    it('does not add a gap next to the first or last page', () => {
        expect(pageWindow(1, 100)).toEqual([1, 2, 3, 'gap', 100]);
        expect(pageWindow(100, 100)).toEqual([1, 'gap', 98, 99, 100]);
    });
});