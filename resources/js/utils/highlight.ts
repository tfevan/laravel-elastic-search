export interface Segment {
    text: string;
    highlighted: boolean;
}

export function parseHighlight(snippet: string, start: string, end: string): Segment[] {
    const segments: Segment[] = [];
    let rest = snippet;

    while (rest.length > 0) {
        const open = rest.indexOf(start);

        if (open === -1) {
            segments.push({ text: rest, highlighted: false });
            break;
        }

        if (open > 0) {
            segments.push({ text: rest.slice(0, open), highlighted: false });
        }

        const afterOpen = rest.slice(open + start.length);
        const close = afterOpen.indexOf(end);

        if (close === -1) {
            segments.push({ text: afterOpen, highlighted: false });
            break;
        }

        segments.push({ text: afterOpen.slice(0, close), highlighted: true });
        rest = afterOpen.slice(close + end.length);
    }

    return segments;
}