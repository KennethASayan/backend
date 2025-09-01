// src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router'
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
import GenerateReports from '../components/Dashboard/GenerateReports/GenerateReports.vue'  // Update this import
// import PrintPDF from '@/components/document/printPDF.vue'


// Generate Reports components are lazy-loaded in the routes below
// No need to import them here - they'll be imported dynamically

const routes = [
  {
    path: '/LandingPage',
    name: 'LandingPage',
    component: LandingPage,
  },
  {
    path: '/login',
    name: 'LoginPage',
    component: LoginPage,
  },
  {
    path: '/Dashboard',
    name: 'Dashboard',
    component: Dashboard,
  },
  {
    path: '/settings',
    component: SettingsPage,
    redirect: '/settings/profile',
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
      },
      // {
      //   path: 'appearance',
      //   name: 'AppearanceSettings',
      //   component: () => import('@/components/Dashboard/Settings/Appearance.vue')
      // }
    ]
  },
  //  {
  //     path: '/print-document',
  //     name: 'PrintDocument',
  //     component: PrintPDF,
  //     props: (route) => ({ 
  //       formData: JSON.parse(route.params.formData)
  //     })
  //   },
  {
    path: '/Search-Results',
    name: 'SearchResults',
    component: SearchPage
  },
  {
    path: '/Manage-users',
    name: 'ManageUsers',
    component: ManageUsers,
  },
  {
    path: '/Incoming-documents',
    name: 'IncomingDocuments',
    component: IncomingDocuments,
  },
  {
    path: '/Internal-documents',
    name: 'InternalDocuments',
    component: InternalDocuments,
  },
  {
    path: '/Financial-documents',
    name: 'FinancialDocuments',
    component: FinancialDocuments,
  },
  {
    path: '/Activity-reports',
    name: 'ActivityReports',
    component: ActivityReports,
  },
  {
    path: '/Acted-cases',
    name: 'ActedCases',
    component: ActedCases,
  },
  {
    path: '/Generate-reports',
    name: 'GenerateReports',
    component: GenerateReports, // Now using the container component
    redirect: '/Generate-reports/Summary-Incoming-Documents',
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
    redirect: '/Landingpage',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router