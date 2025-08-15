export default [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/auth/LoginView.vue')
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('../views/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/operations',
    name: 'Operations',
    component: () => import('../views/operations/OperationsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/operations/create',
    name: 'CreateOperation',
    component: () => import('../views/operations/CreateOperationView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/caisse',
    name: 'Caisse',
    component: () => import('../views/caisse/CaisseView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/remittance',
    name: 'Remittance',
    component: () => import('../views/remittance/RemittanceView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/analytics',
    name: 'Analytics',
    component: () => import('../views/analytics/AnalyticsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/admin',
    name: 'Admin',
    component: () => import('../views/admin/AdminView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  }
]
