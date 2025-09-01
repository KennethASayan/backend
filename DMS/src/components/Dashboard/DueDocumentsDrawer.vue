<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import Dialog from '@/components/ui/Dialog/Dialog.vue'
import Pagination from '@/components/ui/pagination/Pagination.vue'
import { ClockIcon as ClockIconOutline, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

// Assume Document and sampleDocuments are imported from a data source
interface Document {
  id: number;
  documentNo: string;
  subject: string;
  dueDate: Date;
  finalActionOffice: string;
}

const sampleDocuments: Document[] = [
  { id: 1, documentNo: 'D-001', subject: 'Budget Proposal for Q4', dueDate: new Date('2025-08-13T10:00:00Z'), finalActionOffice: 'Finance Department' },
  { id: 2, documentNo: 'D-002', subject: 'Marketing Campaign Plan Review', dueDate: new Date('2025-08-14T10:00:00Z'), finalActionOffice: 'Marketing Team' },
  { id: 3, documentNo: 'D-003', subject: 'Software Update Release Notes', dueDate: new Date('2025-08-13T10:00:00Z'), finalActionOffice: 'Development' },
  { id: 4, documentNo: 'D-004', subject: 'Employee Performance Review', dueDate: new Date('2025-08-15T10:00:00Z'), finalActionOffice: 'Human Resources' },
  { id: 5, documentNo: 'D-005', subject: 'Client Contract Renewal', dueDate: new Date('2025-08-14T10:00:00Z'), finalActionOffice: 'Legal Department' },
  { id: 6, documentNo: 'D-006', subject: 'Supplier Agreement Negotiation', dueDate: new Date('2025-08-13T10:00:00Z'), finalActionOffice: 'Procurement' },
  { id: 7, documentNo: 'D-007', subject: 'IT Infrastructure Upgrade Plan', dueDate: new Date('2025-08-14T10:00:00Z'), finalActionOffice: 'IT Department' },
  { id: 8, documentNo: 'D-008', subject: 'Quarterly Report Submission', dueDate: new Date('2025-08-13T10:00:00Z'), finalActionOffice: 'Executive Office' },
  { id: 9, documentNo: 'D-009', subject: 'Product Launch Checklist', dueDate: new Date('2025-08-14T10:00:00Z'), finalActionOffice: 'Product Management' },
  { id: 10, documentNo: 'D-010', subject: 'Brand Strategy Document', dueDate: new Date('2025-08-13T10:00:00Z'), finalActionOffice: 'Branding Team' },
  { id: 11, documentNo: 'D-011', subject: 'Another Due Today Doc', dueDate: new Date('2025-08-13T10:00:00Z'), finalActionOffice: 'Operations' },
  { id: 12, documentNo: 'D-012', subject: 'Another Due Tomorrow Doc', dueDate: new Date('2025-08-14T10:00:00Z'), finalActionOffice: 'Support' },
];

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits(['close'])

const reactiveDocuments = ref<Document[]>(sampleDocuments);

const searchQuery = ref<string>('');

const currentDueDocumentsPage = ref<number>(1)
const dueDocumentsItemsPerPage = ref<number>(5)

const getLocalizedDate = (date: Date): Date => {
  return new Date(date.toLocaleString("en-US", { timeZone: "Asia/Manila" }));
};

const isSameDay = (date1: Date, date2: Date): boolean => {
  const d1 = getLocalizedDate(date1);
  const d2 = getLocalizedDate(date2);
  d1.setHours(0, 0, 0, 0);
  d2.setHours(0, 0, 0, 0);
  return d1.getTime() === d2.getTime();
};

const isDueToday = (dueDate: Date): boolean => {
  const todayInCDO = getLocalizedDate(new Date());
  return isSameDay(dueDate, todayInCDO);
};

const isDueTomorrow = (dueDate: Date): boolean => {
  const todayInCDO = getLocalizedDate(new Date());
  const tomorrowInCDO = new Date(todayInCDO);
  tomorrowInCDO.setDate(todayInCDO.getDate() + 1);
  return isSameDay(dueDate, tomorrowInCDO);
};

const filteredDocumentsBySearch = computed<Document[]>(() => {
  if (!searchQuery.value) {
    return reactiveDocuments.value;
  }
  const query = searchQuery.value.toLowerCase();
  return reactiveDocuments.value.filter(doc =>
    doc.subject.toLowerCase().includes(query) ||
    doc.documentNo.toLowerCase().includes(query) ||
    doc.finalActionOffice.toLowerCase().includes(query)
  );
});

const allDueTodayDocuments = computed<Document[]>(() => {
  return filteredDocumentsBySearch.value.filter(doc => isDueToday(doc.dueDate));
});

const allDueTomorrowDocuments = computed<Document[]>(() => {
  return filteredDocumentsBySearch.value.filter(doc => isDueTomorrow(doc.dueDate));
});

const paginatedDueTodayDocuments = computed<Document[]>(() => {
  const start = (currentDueDocumentsPage.value - 1) * dueDocumentsItemsPerPage.value;
  const end = start + dueDocumentsItemsPerPage.value;
  return allDueTodayDocuments.value.slice(start, end);
});

const paginatedDueTomorrowDocuments = computed<Document[]>(() => {
  const start = (currentDueDocumentsPage.value - 1) * dueDocumentsItemsPerPage.value;
  const end = start + dueDocumentsItemsPerPage.value;
  return allDueTomorrowDocuments.value.slice(start, end);
});

const totalDueTodayCount = computed<number>(() => {
  return allDueTodayDocuments.value.length;
});

const totalDueTomorrowCount = computed<number>(() => {
  return allDueTomorrowDocuments.value.length;
});

const totalDueDocuments = computed<number>(() => allDueTodayDocuments.value.length + allDueTomorrowDocuments.value.length);


const handleDueDocumentsPageChange = (page: number) => {
  currentDueDocumentsPage.value = page
}

const handleDueDocumentsItemsPerPageChange = (newItemsPerPage: number) => {
  dueDocumentsItemsPerPage.value = newItemsPerPage
}

const closeDrawer = () => {
  emit('close')
  searchQuery.value = '';
  currentDueDocumentsPage.value = 1;
}

watch(searchQuery, () => {
  currentDueDocumentsPage.value = 1;
});
</script>

<template>
    <Dialog
      :isOpen="props.isOpen"
      type="drawer"
      title="Due Documents"
      title-description="Review documents with upcoming or past-due deadlines."
      :icon="ClockIconOutline"
      position="right"
      :closeOnOverlayClick="true"
      @close="closeDrawer"
    >

    <div class="flex flex-col h-full">
      <div class="flex justify-end items-center mb-4">
        <div class="relative w-full">
          <input
            type="text"
            placeholder="Search..."
            class="w-full pl-10 pr-4 py-2 text-black text-sm border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500"
            v-model="searchQuery"
          />
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
        </div>
      </div>

      <div class="flex-grow bg-gray-50 overflow-y-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="flex flex-col">
            <div class="bg-teal-500 text-white p-3 rounded-t-lg font-semibold text-center top-0">
              <h3 class="text-lg">DOCUMENTS DUE TODAY</h3>
              <p class="text-sm">{{ totalDueTodayCount }} documents require action today</p>
            </div>
            <div class="space-y-4 py-4">
              <div
                class="bg-white rounded-lg shadow p-4 border border-gray-200"
                v-for="doc in paginatedDueTodayDocuments" :key="doc.id"
              >
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-2">
                  <span class="text-xs text-gray-500">{{ doc.dueDate.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' }) }}</span>
                  <span class="text-xs text-red-500 flex items-center gap-1 mt-2 sm:mt-0">
                    <ClockIconOutline class="w-3 h-3" /> Due Today
                  </span>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ doc.subject }}</h3>
                <p class="text-xs text-gray-600 mb-2">
                  Final Action Office: {{ doc.finalActionOffice }}
                </p>
                <div class="flex justify-end">
                  <a href="#" class="text-xs text-blue-500 hover:underline">View Details</a>
                </div>
              </div>
              <div v-if="paginatedDueTodayDocuments.length === 0" class="text-center text-gray-500 py-4">
                <span v-if="searchQuery && totalDueTodayCount === 0">No 'Due Today' documents match your search.</span>
                <span v-else-if="!searchQuery && totalDueTodayCount === 0">No documents due today.</span>
                <span v-else>No 'Due Today' documents on this page.</span>
              </div>
            </div>
          </div>

          <div class="flex flex-col">
            <div class="bg-blue-500 text-white p-3 rounded-t-lg font-semibold text-center top-0">
              <h3 class="text-lg">DOCUMENTS DUE TOMORROW</h3>
              <p class="text-sm">{{ totalDueTomorrowCount }} documents require action tomorrow</p>
            </div>
            <div class="space-y-4 py-4">
              <div
                class="bg-white rounded-lg shadow p-4 border border-gray-200"
                v-for="doc in paginatedDueTomorrowDocuments" :key="doc.id"
              >
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-2">
                  <span class="text-xs text-gray-500">{{ doc.dueDate.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' }) }}</span>
                  <span class="text-xs text-orange-500 flex items-center gap-1 mt-2 sm:mt-0">
                    <ClockIconOutline class="w-3 h-3" /> Due Tomorrow
                  </span>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ doc.subject }}</h3>
                <p class="text-xs text-gray-600 mb-2">
                  Final Action Office: {{ doc.finalActionOffice }}
                </p>
                <div class="flex justify-end">
                  <a href="#" class="text-xs text-blue-500 hover:underline">View Details</a>
                </div>
              </div>
              <div v-if="paginatedDueTomorrowDocuments.length === 0" class="text-center text-gray-500 py-4">
                <span v-if="searchQuery && totalDueTomorrowCount === 0">No 'Due Tomorrow' documents match your search.</span>
                <span v-else-if="!searchQuery && totalDueTomorrowCount === 0">No documents due tomorrow.</span>
                <span v-else>No 'Due Tomorrow' documents on this page.</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <Pagination
        :total-items="totalDueDocuments"
        :initial-page="currentDueDocumentsPage"
        :initial-items-per-page="dueDocumentsItemsPerPage"
        @page-change="handleDueDocumentsPageChange"
        @items-per-page-change="handleDueDocumentsItemsPerPageChange"
      />
    </div>
    <template #footer>
      <div class="flex justify-between items-center w-full">
        <span class="text-sm text-gray-500"
          >Total: {{ totalDueDocuments }} documents requiring attention.</span
        >
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
