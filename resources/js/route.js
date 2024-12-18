import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/",
            component: () => import("./pages/Login.vue"),
        },
        {
            path: "/register",
            component: () => import("./pages/Register.vue"),
        },
        {
            path: "/home",
            component: () => import("./pages/Home.vue"),
        },
        {
            path: "/categories",
            name: 'New Category',
            component: () => import("./pages/Categories.vue"),
        },
        {
            path: "/category/:id/edit",
            name: 'Edit Category',
            component: () => import("./pages/Categories.vue"),
        },
        {
            path: "/tasks",
            name: 'New Task',
            component: () => import("./pages/Tasks.vue"),
        },
        {
            path: "/task/:id/edit",
            name: 'Edit Task',
            component: () => import("./pages/Tasks.vue"),
        },
    ],
});

router.beforeEach((to, from, next) => {
    const isLoggedIn = isAuthenticated();

    if ((to.path === '/' || to.path === '/register') && isLoggedIn) {
        return next({ path: '/home' });
    }

    if (to.path !== '/' && to.path !== '/register' && !isLoggedIn) {
        return next({ path: '/' });
    }

    return next();
});

function isAuthenticated() {
    return Boolean(localStorage.getItem('logged_user'))
}

export default router;
