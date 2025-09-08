import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/components/store/authstore'
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
        meta: { requiresAuth: false },
    },
    {
        path: '/login',
        name: 'LoginPage',
        component: LoginPage,
        meta: { requiresAuth: false },
    },
    {
  path: '/forgot-password',
  name: 'ForgotPassword',
  component: () => import('@/components/forgotpassword/ForgotPassword.vue'),
  meta: { requiresAuth: false }
},
{
  path: '/reset-password',
  name: 'ResetPassword',
  component: () => import('@/components/forgotpassword/ResetPassword.vue'),
  meta: { requiresAuth: false }
},
    {
        path: '/Dashboard',
        name: 'Dashboard',
        component: Dashboard,
        meta: { requiresAuth: true },
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
                component: () => import('@/components/Dashboard/Settings/Profile.vue'),
                meta: { requiresAuth: true },
            },
            {
                path: 'password',
                name: 'Password',
                component: () => import('@/components/Dashboard/Settings/Password.vue'),
                meta: { requiresAuth: true },
            }
        ]
    },
    {
        path: '/Search-Results',
        name: 'SearchResults',
        component: SearchPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/Manage-users',
        name: 'ManageUsers',
        component: ManageUsers,
        meta: { requiresAuth: true },
    },
    {
        path: '/Incoming-documents',
        name: 'IncomingDocuments',
        component: IncomingDocuments,
        meta: { requiresAuth: true },
    },
    {
        path: '/Internal-documents',
        name: 'InternalDocuments',
        component: InternalDocuments,
        meta: { requiresAuth: true },
    },
    {
        path: '/Financial-documents',
        name: 'FinancialDocuments',
        component: FinancialDocuments,
        meta: { requiresAuth: true },
    },
    {
        path: '/Activity-reports',
        name: 'ActivityReports',
        component: ActivityReports,
        meta: { requiresAuth: true },
    },
    {
        path: '/Acted-cases',
        name: 'ActedCases',
        component: ActedCases,
        meta: { requiresAuth: true },
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
                meta: { requiresAuth: true },
            },
            {
                path: 'Incoming-Documents-Received',
                name: 'IncomingDocumentsReceived',
                component: () => import('@/components/Dashboard/GenerateReports/IncomingDocumentsReceived.vue'),
                meta: { requiresAuth: true },
            },
            {
                path: 'Time-Motion-Incoming-Documents-Referral',
                name: 'TimeMotionIncomingDocumentsReferral',
                component: () => import('@/components/Dashboard/GenerateReports/TimeMotionIncomingDocumentsReferral.vue'),
                meta: { requiresAuth: true },
            },
            {
                path: 'Time-Motion-Incoming-Documents-Final-Action',
                name: 'TimeMotionIncomingDocumentsFinalAction',
                component: () => import('@/components/Dashboard/GenerateReports/TimeMotionIncomingDocumentsFinalAction.vue'),
                meta: { requiresAuth: true },
            },
            {
                path: 'Download-Summary',
                name: 'DownloadSummary',
                component: () => import('@/components/Dashboard/GenerateReports/DownloadSummary.vue'),
                meta: { requiresAuth: true },
            },
        ]
    },
    {
        path: '/',
        redirect: '/LandingPage',
        meta: { requiresAuth: false },
    },
    {
        path: '/:catchAll(.*)',
        name: 'NotFound',
        component: () => import('@/views/notfound.vue'),
        meta: { requiresAuth: false },
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})


// Navigation Guard
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    
    try {
        // Initialize auth state if not already done
        if (!authStore.initialized.value) {
            await authStore.initAuth();
        }
        
        const isAuthenticated = authStore.isAuthenticated.value;
        const requiresAuth = to.meta.requiresAuth;

        console.log('🛡️ Router Guard:', {
            path: to.path,
            isAuthenticated,
            requiresAuth
        });

        if (to.path === '/login' && isAuthenticated) {
            console.log('➡️ Already authenticated, redirecting to Dashboard');
            next('/Dashboard');
            return;
        }

        if (requiresAuth && !isAuthenticated) {
            console.log('🔒 Authentication required, redirecting to login');
            next('/login');
            return;
        }

        // Allow navigation
        next();
    } catch (error) {
        console.error('Router guard error:', error);
        // Don't automatically redirect on error
        if (to.path !== '/login') {
            next('/login');
        } else {
            next();
        }
    }
});

export default router
