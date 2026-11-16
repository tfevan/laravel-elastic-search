import type { Component } from 'vue';

export type ChapterLevel = 'beginner' | 'advanced';

export interface Chapter {
    number: number;
    title: string;
    path: string;
    level: ChapterLevel;
    component?: () => Promise<Component>;
}

export const chapters: Chapter[] = [
    {
    number: 1,
    title: 'Connection & Health',
    path: '/health',
    level: 'beginner',
    component: () => import('./pages/chapters/Chapter01Health.vue'),
},
    { number: 2, title: 'Indexing & Auto-sync', path: '/products', level: 'beginner' },
    { number: 3, title: 'Basic Search', path: '/search', level: 'beginner' },
    { number: 4, title: 'Filters & Sorting', path: '/filters', level: 'beginner' },
    { number: 5, title: 'Pagination', path: '/pagination', level: 'beginner' },
    { number: 6, title: 'Searchable Data', path: '/documents', level: 'beginner' },
    { number: 7, title: 'Mapping & Analyzers', path: '/mapping', level: 'advanced' },
    { number: 8, title: 'Relevance & Bool Queries', path: '/relevance', level: 'advanced' },
    { number: 9, title: 'Fuzzy Search', path: '/fuzzy', level: 'advanced' },
    { number: 10, title: 'Autocomplete', path: '/autocomplete', level: 'advanced' },
    { number: 11, title: 'Highlighting', path: '/highlight', level: 'advanced' },
    { number: 12, title: 'Faceted Search', path: '/facets', level: 'advanced' },
    { number: 13, title: 'Geo Search', path: '/geo', level: 'advanced' },
    { number: 14, title: 'Performance', path: '/performance', level: 'advanced' },
    { number: 15, title: 'Testing', path: '/testing', level: 'advanced' },
];