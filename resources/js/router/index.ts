import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';
import { chapters } from '../chapters';
import Home from '../pages/Home.vue';

const chapterRoutes: RouteRecordRaw[] = chapters.flatMap((chapter) =>
    chapter.component
        ? [{ path: chapter.path, name: `chapter-${chapter.number}`, component: chapter.component }]
        : [],
);

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', name: 'home', component: Home },
        ...chapterRoutes,
        { path: '/:pathMatch(.*)*', redirect: '/' },
    ],
});