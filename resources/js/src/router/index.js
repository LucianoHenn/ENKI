import { createRouter, createWebHistory } from 'vue-router';

import pagesRoutes from './_pages.router';
import authRoutes from './_auth.router';
import dashboardRoutes from './_dashboard.router';
import themeRoutes from './_theme.router';

const routes = [
    ...pagesRoutes,
    ...authRoutes,
    ...dashboardRoutes,
    ...themeRoutes,
    {
        path: '/:pathMatch(.*)*',
        component: () => import('../layouts/auth-layout.vue'),
        children: [
            {
                path: '',
                name: '404',
                component: () => import('../views/pages/error404.vue'),
            }
        ]
    }
];

const router = new createRouter({
    // mode: 'history',
    history: createWebHistory(),
    linkExactActiveClass: 'active',
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { left: 0, top: 0 };
        }
    },
});

export default router;
