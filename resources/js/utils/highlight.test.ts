import { describe, expect, it } from 'vitest';
import { parseHighlight } from './highlight';

const START = '[[hl]]';
const END = '[[/hl]]';

describe('parseHighlight', () => {
    it('returns one plain segment when nothing is highlighted', () => {
        expect(parseHighlight('Apex Phone', START, END)).toEqual([{ text: 'Apex Phone', highlighted: false }]);
    });

    it('splits highlighted and plain parts', () => {
        expect(parseHighlight('Apex [[hl]]Smartphone[[/hl]] AB-1', START, END)).toEqual([
            { text: 'Apex ', highlighted: false },
            { text: 'Smartphone', highlighted: true },
            { text: ' AB-1', highlighted: false },
        ]);
    });

    it('handles several highlights in a row', () => {
        const segments = parseHighlight('[[hl]]Wireless[[/hl]] [[hl]]Charger[[/hl]]', START, END);

        expect(segments.filter((segment) => segment.highlighted).map((segment) => segment.text)).toEqual(['Wireless', 'Charger']);
    });

    it('keeps HTML as plain text so it can never be executed', () => {
        const segments = parseHighlight('<img src=x onerror=alert(1)> [[hl]]phone[[/hl]]', START, END);

        expect(segments[0]).toEqual({ text: '<img src=x onerror=alert(1)> ', highlighted: false });
    });

    it('treats an unclosed marker as plain text', () => {
        expect(parseHighlight('a [[hl]]b', START, END)).toEqual([
            { text: 'a ', highlighted: false },
            { text: 'b', highlighted: false },
        ]);
    });
});