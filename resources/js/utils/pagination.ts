export type PageItem = number | 'gap';

export function pageWindow(current: number, last: number, radius: number = 2): PageItem[] {
    const wanted = new Set<number>([1, last]);

    for (let page = current - radius; page <= current + radius; page++) {
        if (page >= 1 && page <= last) {
            wanted.add(page);
        }
    }

    const sorted = [...wanted].sort((a, b) => a - b);
    const items: PageItem[] = [];

    sorted.forEach((page, index) => {
        if (index > 0 && page - sorted[index - 1] > 1) {
            items.push('gap');
        }

        items.push(page);
    });

    return items;
}