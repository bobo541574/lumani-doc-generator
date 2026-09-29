import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import Dashboard from '@/pages/Dashboard.vue';

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'Dashboard' },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    document.title = `${to.meta.title ?? 'Lumani'} - Lumani`;
});

export default router;
