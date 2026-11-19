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
    {
        number: 2,
        title: 'Indexing & Auto-sync',
        path: '/products',
        level: 'beginner',
        component: () => import('./pages/chapters/Chapter02Indexing.vue'),
    },
    {
        number: 3,
        title: 'Basic Search',
        path: '/search',
        level: 'beginner',
        component: () => import('./pages/chapters/Chapter03Search.vue'),
    },
    {
        number: 4,
        title: 'Filters & Sorting',
        path: '/filters',
        level: 'beginner',
        component: () => import('./pages/chapters/Chapter04Filters.vue'),
    },
    {
        number: 5,
        title: 'Pagination',
        path: '/pagination',
        level: 'beginner',
        component: () => import('./pages/chapters/Chapter05Pagination.vue'),
    },
    {
        number: 6,
        title: 'Searchable Data',
        path: '/documents',
        level: 'beginner',
        component: () => import('./pages/chapters/Chapter06Documents.vue'),
    },
    {
        number: 7,
        title: 'Mapping & Analyzers',
        path: '/mapping',
        level: 'advanced',
        component: () => import('./pages/chapters/Chapter07Mapping.vue'),
    },
    {
        number: 8,
        title: 'Relevance & Bool Queries',
        path: '/relevance',
        level: 'advanced',
        component: () => import('./pages/chapters/Chapter08Relevance.vue'),
    },
    {
        number: 9,
        title: 'Fuzzy Search',
        path: '/fuzzy',
        level: 'advanced',
        component: () => import('./pages/chapters/Chapter09Fuzzy.vue'),
    },
    {
        number: 10,
        title: 'Autocomplete',
        path: '/autocomplete',
        level: 'advanced',
        component: () => import('./pages/chapters/Chapter10Autocomplete.vue'),
    },
    {
        number: 11,
        title: 'Highlighting',
        path: '/highlight',
        level: 'advanced',
        component: () => import('./pages/chapters/Chapter11Highlight.vue'),
    },
    {
        number: 12,
        title: 'Faceted Search',
        path: '/facets',
        level: 'advanced',
        component: () => import('./pages/chapters/Chapter12Facets.vue'),
    },
    {
        number: 13,
        title: 'Geo Search',
        path: '/geo',
        level: 'advanced',
        component: () => import('./pages/chapters/Chapter13Geo.vue'),
    },
    {
        number: 14,
        title: 'Performance',
        path: '/performance',
        level: 'advanced',
        component: () => import('./pages/chapters/Chapter14Performance.vue'),
    },
    { number: 15, title: 'Testing', path: '/testing', level: 'advanced' },
];