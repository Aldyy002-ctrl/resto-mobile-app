import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import TabsPage from '../views/TabsPage.vue'
import authService from '@/services/auth';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    component: () => import('@/views/Tab1Page.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    component: () => import('@/views/Tab2Page.vue'),
    meta: { requiresGuest: true }
  },
  // Dashboard Pelanggan
  {
    path: '/pelanggan',
    component: () => import('@/views/dashboards/PelangganDashboard.vue'),
    meta: { requiresAuth: true, role: 'pelanggan' }
  },
  // Dashboard Kasir
  {
    path: '/kasir',
    component: () => import('@/views/dashboards/KasirDashboard.vue'),
    meta: { requiresAuth: true, role: 'kasir' }
  },
  // Dashboard Kitchen
  {
    path: '/kitchen',
    component: () => import('@/views/dashboards/KitchenDashboard.vue'),
    meta: { requiresAuth: true, role: 'koki' }
  },
  // Dashboard Owner
  {
    path: '/owner',
    component: () => import('@/views/dashboards/OwnerDashboard.vue'),
    meta: { requiresAuth: true, role: 'owner' }
  },
  // Dashboard Manajer
  {
    path: '/manajer',
    component: () => import('@/views/dashboards/ManajerDashboard.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  // Fallback
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login'
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = authService.isAuthenticated();
  const user = authService.getUser();
  
  // Redirect authenticated users away from login/register
  if (to.meta.requiresGuest && isAuthenticated) {
    return next(getDashboardPath(user?.role));
  }
  
  // Redirect unauthenticated users to login
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next('/login');
  }
  
  // Check role authorization
  if (to.meta.requiresAuth && to.meta.role) {
    if (user?.role !== to.meta.role) {
      // Redirect to correct dashboard
      return next(getDashboardPath(user?.role));
    }
  }
  
  next();
});

// Helper function to get dashboard path based on role
function getDashboardPath(role: string | undefined): string {
  switch (role) {
    case 'pelanggan':
      return '/pelanggan';
    case 'kasir':
      return '/kasir';
    case 'koki':
      return '/kitchen';
    case 'owner':
      return '/owner';
    case 'admin':
      return '/manajer';
    default:
      return '/login';
  }
}

export default router
