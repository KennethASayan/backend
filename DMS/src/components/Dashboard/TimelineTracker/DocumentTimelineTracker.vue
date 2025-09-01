<script setup lang="ts">
import { ref, defineProps, defineEmits } from 'vue'
import Dialog from '@/components/ui/Dialog/Dialog.vue'
import { Users } from '@/data/users'
import { Timeline } from 'ant-design-vue'
import { CheckCircleOutlined, ClockCircleOutlined, UserOutlined } from '@ant-design/icons-vue'

const props = defineProps({
  isOpen: Boolean,
  documentId: String,
  documentType: String
})

const emit = defineEmits(['close'])

const timelineData = ref([
  {
    id: 1,
    office: 'ADMIN',
    status: 'complete',
    description: 'Initial document review and processing',
    user: Users.find(u => u.division === 'Admin'),
    compliance: 'Compliant',
    referral: 'Document Referral',
    processedBy: 'Administrator',
    date: '2025-08-20T10:00:00Z' // Added missing 'date' property
  },
  {
    id: 2,
    office: 'ORED',
    status: 'complete',
    description: 'Office of the regional of executive director review',
    user: Users.find(u => u.fullName === 'Ricky Boy Diez'),
    compliance: 'Compliant',
    referral: 'Document Referral',
    processedBy: 'Executive Director',
    date: '2025-08-21T11:30:00Z' // Added missing 'date' property
  },
  {
    id: 3,
    office: 'OARD',
    status: 'process',
    description: 'Office assistant regional director processing',
    user: Users.find(u => u.fullName === 'Norhata T. Imam'),
    compliance: 'Under Review',
    referral: 'Document Referral',
    processedBy: 'Mr. JohnDoe',
    date: '2025-08-22T14:45:00Z' // Added missing 'date' property
  },
  {
    id: 4,
    office: 'FINAL ACTION OFFICE',
    status: 'complete',
    description: 'Final review and approval completed',
    user: Users.find(u => u.division === 'Records Section'),
    compliance: 'Compliant',
    referral: 'Document Referral',
    processedBy: 'Admin',
    date: '2025-08-23T09:15:00Z' // Added missing 'date' property
  }
])

const getStatusColor = (status: string) => {
  switch (status) {
    case 'complete':
      return 'green'
    case 'process':
      return 'blue'
    case 'wait':
      return 'gold'
    default:
      return 'gray'
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    hour12: true
  })
}
</script>

<template>
  <Dialog
    :is-open="isOpen"
    type="drawer"
    position="right"
    title="Document Timeline Tracker"
    :description="`Tracking Document: ${documentId}`"
    :close-on-overlay-click="false"
    @close="emit('close')"
    class="w-full max-w-2xl"
  >

    <div class="p-6 overflow-y-auto max-h-[85vh]">
      <a-timeline>
        <a-timeline-item
          v-for="item in timelineData"
          :key="item.id"
          :color="getStatusColor(item.status)"
        >
          <template #dot>
            <check-circle-outlined v-if="item.status === 'complete'" />
            <clock-circle-outlined v-else />
          </template>

          <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center space-x-2">
                <span class="font-medium text-gray-900">{{ item.office }}</span>
                <a-tag :color="getStatusColor(item.status)">
                  {{ item.status === 'complete' ? 'Completed' :
                      item.status === 'process' ? 'In Progress' : 'Pending' }}
                </a-tag>
              </div>
            </div>
              <span class="text-sm text-gray-500">{{ item.date }}</span>
            <div class="flex items-center space-x-2 mb-2">
              <UserOutlined class="text-gray-500 mb-1"/>
              <p class="text-sm text-gray-600">{{ item.user?.fullName || 'Unknown User' }}</p>
            </div>
            <p class="text-gray-600 mb-3">{{ item.description }}</p>

            <a-card class="bg-gray-50" size="small">
              <div class="flex justify-between mb-2">
                <span class="text-sm text-gray-600">{{ item.referral }}</span>
                <span class="text-sm text-gray-600">Compliance to Instruction</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Processed by {{ item.processedBy }}</span>
                <a-tag :color="item.compliance === 'Compliant' ? 'success' : 'warning'">
                  {{ item.compliance }}
                </a-tag>
              </div>
            </a-card>
          </div>
        </a-timeline-item>
      </a-timeline>

      <div class="mt-6 text-sm text-gray-500 flex justify-center items-center">
        <clock-circle-outlined class="mr-1" />
        Last updated: {{ formatDate(timelineData[timelineData.length - 1].date) }}
      </div>
    </div>
  </Dialog>
</template>
