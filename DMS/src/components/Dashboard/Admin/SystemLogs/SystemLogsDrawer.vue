<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import Dialog from '@/components/ui/Dialog/Dialog.vue'
import Pagination from '@/components/ui/pagination/Pagination.vue'
import button from '@/components/ui/button/Button.vue'
// Import the icon you want to use
import { CalendarDaysIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

import { Users } from '@/data/users.ts';
const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits(['close'])

const searchQuery = ref<string>('');
const currentLogsPage = ref<number>(1);
const logsItemsPerPage = ref<number>(10);

const allSystemLogs = computed(() => {
  const activities = [
    'Updated document #2025-08-07-0485',
    'Added new document #2025-08-07-0486',
    'Approved document #2025-08-07-0484',
    'Extended deadline for document #2025-08-07-0483',
    'Deleted obsolete document #2025-07-06-0421',
    'Uploaded supporting documents',
    'Database maintenance completed',
  ];
  
  const now = new Date();
  
  return Users.map((user, index) => {
    const activityIndex = index % activities.length;
    const logActivity = activities[activityIndex];
    
    const logDate = new Date(now.getTime() - (index * 15 * 60 * 1000));
    const formattedTime = new Intl.DateTimeFormat('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: 'numeric',
      hour12: true,
    }).format(logDate);
    
    return {
      id: user.id,
      user: user.fullName,
      activity: logActivity,
      time: formattedTime,
    };
  });
});

const filteredLogs = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return allSystemLogs.value.filter(log =>
    log.user.toLowerCase().includes(query) ||
    log.activity.toLowerCase().includes(query)
  );
});

const paginatedLogs = computed(() => {
  const start = (currentLogsPage.value - 1) * logsItemsPerPage.value;
  const end = start + logsItemsPerPage.value;
  return filteredLogs.value.slice(start, end);
});

const totalFilteredLogs = computed(() => filteredLogs.value.length);

const handlePageChange = (page: number) => {
  currentLogsPage.value = page;
};

const handleItemsPerPageChange = (newItemsPerPage: number) => {
  logsItemsPerPage.value = newItemsPerPage;
  currentLogsPage.value = 1;
};

const closeDrawer = () => {
  emit('close');
  searchQuery.value = '';
  currentLogsPage.value = 1;
};

watch(searchQuery, () => {
  currentLogsPage.value = 1;
});
</script>

<template>
  <Dialog
    :isOpen="props.isOpen"
    type="drawer"
    title="System Logs"
    title-description="Track all system activities and user actions" :icon="CalendarDaysIcon" position="right"
    :closeOnOverlayClick="true"
    @close="closeDrawer"
    drawer-md-width="925px"
  >
    <div class="flex flex-col h-full">
      <div class="flex justify-end items-center mb-4">
        <div class="relative w-full">
          <input
            type="text"
            placeholder="Search logs..."
            class="w-full pl-10 pr-4 py-2 text-black text-sm border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500"
            v-model="searchQuery"
          />
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
        </div>
      </div>

      <div class="flex-grow bg-gray-50 overflow-y-auto p-2 border rounded-lg">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
              <tr>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="paginatedLogs.length === 0">
                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                  No logs found.
                </td>
              </tr>
              <tr v-for="(log, index) in paginatedLogs" :key="log.id">
                <td class="px-6 py-4 whitespace-nowraptext-sm text-gray-900">
                  <div class="flex  ">
                    <div class="h-8 w-8 rounded-full flex items-center justify-center text-white font-semibold text-xs"
                         :style="{ backgroundColor: ['#4b5563', '#10b981', '#3b82f6', '#f97316', '#ef4444', '#06b6d4', '#6366f1', '#eab308', '#22c55e', '#f59e0b'][index % 10] }">
                      {{ log.user.split(' ').map(n => n[0]).join('') }}
                    </div>
                    <div class="ml-4 ">{{ log.user }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ log.activity }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ log.time }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <Pagination
        :total-items="totalFilteredLogs"
        :initial-page="currentLogsPage"
        :initial-items-per-page="logsItemsPerPage"
        @page-change="handlePageChange"
        @items-per-page-change="handleItemsPerPageChange"
      />
    </div>

    <template #footer>
      <div class="flex justify-between items-center w-full">
        <span class="text-sm text-gray-500">
          Total: {{ totalFilteredLogs }} logs.
        </span>
        <button
          @click="closeDrawer"
          class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors"
        >
          Close
        </button>
      </div>
    </template>
  </Dialog>
</template>