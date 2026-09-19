import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { guestOnly: true, audience: 'user' },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: { guestOnly: true, audience: 'user' },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { requiresAuth: true, audience: 'user', userOnly: true },
    },
    {
      path: '/admin/login',
      name: 'admin-login',
      component: () => import('../views/admin/AdminLoginView.vue'),
      meta: { guestOnly: true, audience: 'admin' },
    },
    {
      path: '/admin',
      redirect: { name: 'admin-dashboard' },
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: () => import('../views/admin/AdminDashboardView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true, audience: 'admin' },
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (!auth.bootstrapped) {
    await auth.bootstrap()
  }

  const isAdminRoute = to.path.startsWith('/admin') && to.name !== 'admin-login'

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    if (to.meta.requiresAdmin || isAdminRoute) {
      return { name: 'admin-login', query: { redirect: to.fullPath } }
    }

    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { name: 'dashboard' }
  }

  if (to.meta.userOnly && auth.isAuthenticated && auth.isAdmin) {
    return { name: 'admin-dashboard' }
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    if (to.meta.audience === 'admin') {
      return auth.isAdmin ? { name: 'admin-dashboard' } : { name: 'dashboard' }
    }

    if (to.meta.audience === 'user') {
      return auth.isAdmin ? { name: 'admin-dashboard' } : { name: 'dashboard' }
    }

    return { name: 'dashboard' }
  }

  return true
})

export default router
