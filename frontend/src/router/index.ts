import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { requiresAuth: false },
  },
  {
    path: '/2fa',
    name: 'TwoFactor',
    component: () => import('@/views/auth/TwoFactorView.vue'),
    meta: { requiresAuth: false },
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { requiresAuth: false },
  },
  {
    path: '/',
    component: () => import('@/layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/DashboardView.vue'),
      },
      // Operations
      {
        path: 'operations',
        name: 'Operations',
        component: () => import('@/views/operations/OperationsListView.vue'),
      },
      {
        path: 'operations/new',
        name: 'NewOperation',
        component: () => import('@/views/operations/NewOperationView.vue'),
      },
      {
        path: 'operations/:id',
        name: 'OperationDetail',
        component: () => import('@/views/operations/OperationDetailView.vue'),
      },
      // Journal
      {
        path: 'journal',
        name: 'Journal',
        component: () => import('@/views/journal/JournalView.vue'),
      },
      // Cash Registers
      {
        path: 'cash-registers',
        name: 'CashRegisters',
        component: () => import('@/views/cashRegisters/CashRegistersView.vue'),
      },
      {
        path: 'cash-registers/transfer',
        name: 'FundTransfer',
        component: () => import('@/views/cashRegisters/FundTransferView.vue'),
      },
      // Analytics
      {
        path: 'analytics',
        name: 'Analytics',
        component: () => import('@/views/analytics/AnalyticsView.vue'),
      },
      // Remittance
      {
        path: 'remittances',
        name: 'Remittances',
        component: () => import('@/views/remittances/RemittancesView.vue'),
      },
      {
        path: 'remittances/new',
        name: 'NewRemittance',
        component: () => import('@/views/remittances/NewRemittanceView.vue'),
      },
      // Admin
      {
        path: 'admin',
        meta: { requiresAdmin: true },
        children: [
          {
            path: 'users',
            name: 'UserManagement',
            component: () => import('@/views/admin/UsersView.vue'),
          },
          {
            path: 'agencies',
            name: 'AgencyManagement',
            component: () => import('@/views/admin/AgenciesView.vue'),
          },
          {
            path: 'products',
            name: 'ProductManagement',
            component: () => import('@/views/admin/ProductsView.vue'),
          },
          {
            path: 'settings',
            name: 'Settings',
            component: () => import('@/views/admin/SettingsView.vue'),
          },
        ],
      },
      // Profile
      {
        path: 'profile',
        name: 'Profile',
        component: () => import('@/views/ProfileView.vue'),
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/NotFoundView.vue'),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // Check if route requires authentication
  if (to.meta.requiresAuth !== false) {
    if (!authStore.isAuthenticated) {
      // Try to restore session
      const isValid = await authStore.checkAuth();
      
      if (!isValid) {
        return next({
          name: 'Login',
          query: { redirect: to.fullPath },
        });
      }
    }

    // Check admin requirements
    if (to.meta.requiresAdmin && !authStore.isAdmin) {
      return next({
        name: 'Dashboard',
      });
    }
  } else {
    // Redirect to dashboard if already authenticated
    if (authStore.isAuthenticated && to.name === 'Login') {
      return next({ name: 'Dashboard' });
    }
  }

  next();
});

export default router;