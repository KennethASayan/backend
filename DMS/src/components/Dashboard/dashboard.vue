<script setup lang="ts">
import { ref } from 'vue'
import {
  DocumentTextIcon,
  ClockIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  CalendarIcon,
  CogIcon,
  DocumentChartBarIcon,
} from '@heroicons/vue/24/solid'
import { useRouter } from 'vue-router' // Imported useRouter

import DueDocumentsDrawer from './DueDocumentsDrawer.vue'
import AddDocumentModal from './AddDocumentModal.vue'
import CalendarModal from './CalendarModal.vue'
import IncomingDocumentDrawer from './IncomingDocuments/Incomingdocument(foradding)/IncomingDocumentDrawer.vue'
import InternalDocumentDrawer from './InternalDocuments(approval)/Internaldocument(foradding)/InternalDocumentDrawer.vue'
import FinancialDocumentsDrawer from './FinancialDocuments/Financialdocument(foradding)/FinancialDocumentDrawer.vue'
import InternalDocumentActivityDrawer from './InternalDocuments(activity)/Internaldocument(foradding)/InternalDocumentActivityDrawer.vue'
import button from '@/components/ui/button/Button.vue'
import SystemLogsDrawer from '@/components/Dashboard/Admin/SystemLogs/SystemLogsDrawer.vue'
import type { DashboardStat, RecentActivity, QuickAction } from '@/types/index'

// Initialize router
const router = useRouter()

const isInternalDocumentActivityDrawerOpen = ref(false)
const isDueDocumentsDrawerOpen = ref(false)
const isInternalDocumentDrawerOpen = ref(false)
const isFinancialDocumentDrawerOpen = ref(false)
const isAddDocumentModalOpen = ref(false)
const isCalendarModalOpen = ref(false)
const isIncomingDrawerOpen = ref(false)
const isSystemLogsDrawerOpen = ref(false)

const DashboardStats = ref<DashboardStat[]>([
  {
    id: 1,
    name: 'Total Documents received',
    value: '128',
    description: 'In this Month',
    icon: DocumentTextIcon,
    bgColorClass: 'bg-blue-600',
    iconColorClass: 'text-white',
    trend: '+12%',
  },
  {
    id: 2,
    name: 'Pending Documents',
    value: '89',
    description: 'Awaiting Action',
    icon: ClockIcon,
    bgColorClass: 'bg-gray-800',
    iconColorClass: 'text-white',
    trend: '+1%',
  },
  {
    id: 3,
    name: 'Acted',
    value: '1,159',
    description: 'Completed',
    icon: CheckCircleIcon,
    bgColorClass: 'bg-green-600',
    iconColorClass: 'text-white',
    trend: '+9%',
  },
  {
    id: 4,
    name: 'Overdue',
    value: '12',
    description: 'Needs Attention',
    icon: ExclamationTriangleIcon,
    bgColorClass: 'bg-red-600',
    iconColorClass: 'text-white',
    trend: '-2%',
  },
])

const recentActivity = ref<RecentActivity[]>([
  {
    id: 1,
    document: 'Document #2025-07-0485',
    action: 'was updated',
    time: '2 minutes ago',
  },
  {
    id: 2,
    document: 'Document #2025-07-0486',
    action: 'was added',
    time: '15 minutes ago',
  },
  {
    id: 3,
    document: 'Document #2025-07-0484',
    action: 'was approved',
    time: '1 hour ago',
  },
  {
    id: 4,
    document: '2025-07-0483',
    action: 'deadline extended',
    time: '3 hours ago',
  },
])

const quickActions = ref<QuickAction[]>([
  {
    id: 1,
    name: 'Add Document',
    icon: DocumentTextIcon,
    action: () => openAddDocumentModal(),
    iconColorClass: 'text-blue-500',
  },
  {
    id: 2,
    name: 'View Calendar',
    icon: CalendarIcon,
    action: () => openCalendarModal(),
    iconColorClass: 'text-green-500',
  },
  {
    id: 3,
    name: 'System Logs',
    icon: CogIcon,
    action: () => openSystemLogsDrawer(),
    iconColorClass: 'text-gray-600',
  },
  {
    id: 4,
    name: 'Generate Report',
    icon: DocumentChartBarIcon,
    action: () => router.push('/Generate-reports/Summary-Incoming-Documents'), 
    iconColorClass: 'text-orange-500',
  },
])

// New functions to open and close the Internal Document Activity Drawer
const openInternalDocumentActivityDrawer = () => {
  isInternalDocumentActivityDrawerOpen.value = true
}

const closeInternalDocumentActivityDrawer = () => {
  isInternalDocumentActivityDrawerOpen.value = false
}

const openDueDocumentsDrawer = () => {
  isDueDocumentsDrawerOpen.value = true
}

const closeDueDocumentsDrawer = () => {
  isDueDocumentsDrawerOpen.value = false
}

const openInternalDocumentDrawer = () => {
  isInternalDocumentDrawerOpen.value = true
}

const closeInternalDocumentDrawer = () => {
  isInternalDocumentDrawerOpen.value = false
}

const openAddDocumentModal = () => {
  isAddDocumentModalOpen.value = true
}

const closeAddDocumentModal = () => {
  isAddDocumentModalOpen.value = false
}

const openCalendarModal = () => {
  isCalendarModalOpen.value = true;
};

const closeCalendarModal = () => {
  isCalendarModalOpen.value = false;
};

// This function is updated to handle the new document type
const handleDocumentTypeSelected = (type: string) => {
  closeAddDocumentModal();
  if (type === 'Incoming Documents') {
    isIncomingDrawerOpen.value = true;
  } else if (type === 'Internal Documents (For Approval)') {
    isInternalDocumentDrawerOpen.value = true;
  } else if (type === 'Financial Documents') {
    isFinancialDocumentDrawerOpen.value = true;
  } else if (type === 'Internal Documents (Activity Reports)') {
    isInternalDocumentActivityDrawerOpen.value = true;
  }
};

const closeIncomingDrawer = () => {
  isIncomingDrawerOpen.value = false;
};

const closeFinancialDocumentDrawer = () => {
  isFinancialDocumentDrawerOpen.value = false
}

const openSystemLogsDrawer = () => {
  isSystemLogsDrawerOpen.value = true;
};

const closeSystemLogsDrawer = () => {
  isSystemLogsDrawerOpen.value = false;
};
</script>

<template>
  <div class="flex flex-col md:flex-row bg-gray-100">
    <div class="flex-1 flex flex-col">
      <main class="flex-1 overflow-y-auto p-4 md:p-6">
        <div class="flex flex-col sm:flex-row justify-end gap-3 mb-6 md:mb-8">
          <button
            @click="openAddDocumentModal"
            class="w-full sm:w-auto px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors text-sm md:text-base"
          >
            + ADD DOCUMENT
          </button>
          <button
            @click="openDueDocumentsDrawer"
            class="w-full sm:w-auto px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition-colors text-sm md:text-base"
          >
            SHOW DUE DOCUMENTS
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
          <div
            v-for="stat in DashboardStats"
            :key="stat.id"
            class="bg-white rounded-lg shadow p-4 md:p-6"
          >
            <div class="flex items-center justify-between mb-4">
              <div :class="['p-3 rounded-lg', stat.bgColorClass]">
                <component
                  :is="stat.icon"
                  :class="['w-5 h-5', stat.iconColorClass]"
                  aria-hidden="true"
                />
              </div>
              <span
                :class="[
                  stat.trend.startsWith('+') ? 'text-green-600' : 'text-red-600',
                  'text-sm font-medium',
                ]"
              >
                {{ stat.trend }}
              </span>
            </div>
            <div>
              <p class="text-2xl md:text-3xl font-semibold text-gray-900">
                {{ stat.value }}
              </p>
              <p class="text-sm text-gray-500 mt-1">{{ stat.name }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ stat.description }}</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
          <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h2>
            <div class="space-y-4">
              <div
                v-for="activity in recentActivity"
                :key="activity.id"
                class="flex items-center gap-3"
              >
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                <div>
                  <p class="text-sm text-gray-900">{{ activity.document }} {{ activity.action }}</p>
                  <p class="text-xs text-gray-500">{{ activity.time }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
              <button
                v-for="action in quickActions"
                :key="action.id"
                class="flex items-center gap-3 p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                @click="action.action"
              >
                <component
                  :is="action.icon"
                  :class="['w-6 h-6', action.iconColorClass]"
                  aria-hidden="true"
                />
                <span class="text-sm text-gray-700">{{ action.name }}</span>
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <DueDocumentsDrawer :is-open="isDueDocumentsDrawerOpen" @close="closeDueDocumentsDrawer" />
  
  <AddDocumentModal 
    :is-open="isAddDocumentModalOpen"
    @close="closeAddDocumentModal"
    @document-selected="handleDocumentTypeSelected"
  />
  
  <CalendarModal :is-open="isCalendarModalOpen" @close="closeCalendarModal" />
  
  <IncomingDocumentDrawer :is-open="isIncomingDrawerOpen" @close="closeIncomingDrawer" />

  <InternalDocumentDrawer :is-open="isInternalDocumentDrawerOpen" @close="closeInternalDocumentDrawer" />

  <FinancialDocumentsDrawer :is-open="isFinancialDocumentDrawerOpen" @close="closeFinancialDocumentDrawer" />

  <SystemLogsDrawer :is-open="isSystemLogsDrawerOpen" @close="closeSystemLogsDrawer" />

  <InternalDocumentActivityDrawer
    :is-open="isInternalDocumentActivityDrawerOpen"
    @close="closeInternalDocumentActivityDrawer"
  />
</template>