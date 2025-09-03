// src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/components/store/authStore'
import LandingPage from '../views/LandingPage.vue'
import LoginPage from '../views/LoginPage.vue'
import Dashboard from '../views/Dashboard.vue'
import SettingsPage from '../views/SettingPage.vue'
import IncomingDocuments from '../components/Dashboard/IncomingDocuments/IncomingDocuments.vue'
import InternalDocuments from '../components/Dashboard/InternalDocuments(approval)/InternalDocuments.vue'
import FinancialDocuments from '../components/Dashboard/FinancialDocuments/FinancialDocuments.vue'
import ActivityReports from '../components/Dashboard/InternalDocuments(activity)/InternalDocuments.vue'
import ActedCases from '../components/Dashboard/ActedCases/actedcases.vue'
import ManageUsers from '../components/Dashboard/Admin/Manageusers/ManageUsers.vue'
import SearchPage from '../components/Dashboard/SearchPage/SearchResults.vue'
import GenerateReports from '../components/Dashboard/GenerateReports/GenerateReports.vue'

const routes = [
  {
    path: '/LandingPage',
    name: 'LandingPage',
    component: LandingPage,
    meta: { requiresGuest: true } // Only accessible when not authenticated
  },
  {
    path: '/login',
    name: 'LoginPage',
    component: LoginPage,
    meta: { requiresGuest: true } // Only accessible when not authenticated
  },
  {
    path: '/Dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true } // Requires authentication
  },
  {
    path: '/settings',
    component: SettingsPage,
    redirect: '/settings/profile',
    meta: { requiresAuth: true },
    children: [
      {
        path: 'profile',
        name: 'ProfileSettings',
        component: () => import('@/components/Dashboard/Settings/Profile.vue')
      },
      {
        path: 'password',
        name: 'Password',
        component: () => import('@/components/Dashboard/Settings/Password.vue')
      }
    ]
  },
  {
    path: '/Search-Results',
    name: 'SearchResults',
    component: SearchPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/Manage-users',
    name: 'ManageUsers',
    component: ManageUsers,
    meta: { requiresAuth: true }
  },
  {
    path: '/Incoming-documents',
    name: 'IncomingDocuments',
    component: IncomingDocuments,
    meta: { requiresAuth: true }
  },
  {
    path: '/Internal-documents',
    name: 'InternalDocuments',
    component: InternalDocuments,
    meta: { requiresAuth: true }
  },
  {
    path: '/Financial-documents',
    name: 'FinancialDocuments',
    component: FinancialDocuments,
    meta: { requiresAuth: true }
  },
  {
    path: '/Activity-reports',
    name: 'ActivityReports',
    component: ActivityReports,
    meta: { requiresAuth: true }
  },
  {
    path: '/Acted-cases',
    name: 'ActedCases',
    component: ActedCases,
    meta: { requiresAuth: true }
  },
  {
    path: '/Generate-reports',
    name: 'GenerateReports',
    component: GenerateReports,
    redirect: '/Generate-reports/Summary-Incoming-Documents',
    meta: { requiresAuth: true },
    children: [
      {
        path: 'Summary-Incoming-Documents',
        name: 'SummaryIncomingDocuments',
        component: () => import('@/components/Dashboard/GenerateReports/SummaryIncomingDocuments.vue'),
      },
      {
        path: 'Incoming-Documents-Received',
        name: 'IncomingDocumentsReceived',
        component: () => import('@/components/Dashboard/GenerateReports/IncomingDocumentsReceived.vue'),
      },
      {
        path: 'Time-Motion-Incoming-Documents-Referral',
        name: 'TimeMotionIncomingDocumentsReferral',
        component: () => import('@/components/Dashboard/GenerateReports/TimeMotionIncomingDocumentsReferral.vue'),
      },
      {
        path: 'Time-Motion-Incoming-Documents-Final-Action',
        name: 'TimeMotionIncomingDocumentsFinalAction',
        component: () => import('@/components/Dashboard/GenerateReports/TimeMotionIncomingDocumentsFinalAction.vue'),
      },
      {
        path: 'Download-Summary',
        name: 'DownloadSummary',
        component: () => import('@/components/Dashboard/GenerateReports/DownloadSummary.vue'),
      },
    ]
  },
  {
    path: '/',
    redirect: '/LandingPage',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Global navigation guard
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Wait for auth initialization if not already done
  if (!authStore.isAuthenticated.value && !authStore.loading.value) {
    await authStore.initAuth()
  }

  const isAuthenticated = authStore.isAuthenticated.value
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)

  if (requiresAuth && !isAuthenticated) {
    // Redirect to landing page if trying to access protected route without auth
    next('/LandingPage')
  } else if (requiresGuest && isAuthenticated) {
    // Only redirect from landing page if authenticated, not from login page
    if (to.name === 'LandingPage') {
      next('/Dashboard')
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router