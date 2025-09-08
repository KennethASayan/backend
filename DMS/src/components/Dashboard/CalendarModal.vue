<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/solid';
import { sampleDocuments } from '@/data/duedocuments';
import type { Document } from '@/types/index';
import Dialog from '@/components/ui/Dialog/Dialog.vue';
const props = defineProps<{
  isOpen: boolean;
}>();

const emit = defineEmits(['close']);

const currentDate = ref(new Date());

const currentMonth = computed(() => currentDate.value.toLocaleString('en-US', { month: 'long' }));
const currentYear = computed(() => currentDate.value.getFullYear());

const daysInMonth = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();
  return new Date(year, month + 1, 0).getDate();
});

const firstDayOfMonth = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();
  return new Date(year, month, 1).getDay();
});

const calendarDays = computed(() => {
  const days = [];
  for (let i = 0; i < firstDayOfMonth.value; i++) {
    days.push(null);
  }
  for (let i = 1; i <= daysInMonth.value; i++) {
    days.push(i);
  }
  return days;
});

const goToPreviousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
  // After changing month, reset the selected date to the new month's first day
  selectDay(1);
};

const goToNextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
  // After changing month, reset the selected date to the new month's first day
  selectDay(1);
};

const close = () => {
  emit('close');
};

// --- Integration of sampleDocuments data ---

const documentsForCurrentMonth = computed<Document[]>(() => {
  const startOfMonth = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1);
  const endOfMonth = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0, 23, 59, 59, 999);

  return sampleDocuments.filter((doc) => {
    const dueDate = new Date(doc.dueDate);
    return dueDate >= startOfMonth && dueDate <= endOfMonth;
  });
});

const highlightedDates = computed<number[]>(() => {
  const dates = new Set<number>();
  documentsForCurrentMonth.value.forEach((doc) => {
    const dueDate = new Date(doc.dueDate);
    dates.add(dueDate.getDate());
  });
  return Array.from(dates);
});

const isHighlighted = (day: number | null) => {
  return day !== null && highlightedDates.value.includes(day);
};

const selectedDayReminders = ref<Document[]>([]);
const selectedDate = ref<Date | null>(null);

const selectDay = (day: number | null) => {
  if (day === null) {
    selectedDayReminders.value = [];
    selectedDate.value = null;
    return;
  }

  // Create a new date object for the selected day in the current month
  const newSelectedDate = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth(),
    day,
  );
  selectedDate.value = newSelectedDate;

  // Filter reminders for this newly selected date
  selectedDayReminders.value = sampleDocuments.filter((doc) => {
    const dueDate = new Date(doc.dueDate);
    return (
      dueDate.getDate() === newSelectedDate.getDate() &&
      dueDate.getMonth() === newSelectedDate.getMonth() &&
      dueDate.getFullYear() === newSelectedDate.getFullYear()
    );
  });
};

// A central function to initialize or reset the calendar to today's date
const initializeCalendar = () => {
  const today = new Date();
  currentDate.value = today;
  selectDay(today.getDate());
};

// Use watch to call the central function only when the dialog opens
watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      initializeCalendar();
    }
  },
  { immediate: true },
);

const reminderCurrentDate = computed(() => {
  if (selectedDate.value) {
    const options: Intl.DateTimeFormatOptions = { weekday: 'long', month: 'long', day: 'numeric' };
    return selectedDate.value.toLocaleDateString('en-US', options);
  }
  // Default to today's date if no date is selected, although this should be handled by initializeCalendar
  const today = new Date();
  const options: Intl.DateTimeFormatOptions = { weekday: 'long', month: 'long', day: 'numeric' };
  return today.toLocaleDateString('en-US', options);
});
</script>

<template>
<Dialog
  :is-open="props.isOpen"
  @close="close"
  title="Document Calendar"
  title-description="View and manage deadlines and schedules for documents."
  type="modal"
  :close-on-overlay-click="true"
 >
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 h-[330px] overflow-hidden">
 <div class="flex flex-col h-full">
  <div class="flex items-center justify-between mb-4">
  <button @click="goToPreviousMonth" class="p-1 rounded-full hover:bg-gray-100">
   <ChevronLeftIcon class="h-5 w-5 text-gray-700" />
  </button>
  <span class="text-lg font-semibold text-gray-800"
   >{{ currentMonth }} {{ currentYear }}</span
  >
  <button @click="goToNextMonth" class="p-1 rounded-full hover:bg-gray-100">
   <ChevronRightIcon class="h-5 w-5 text-gray-700" />
  </button>
  </div>

  <div class="grid grid-cols-7 text-center text-sm font-medium text-gray-500 mb-2">
  <span>Sun</span>
  <span>Mon</span>
  <span>Tue</span>
  <span>Wed</span>
  <span>Thu</span>
  <span>Fri</span>
  <span>Sat</span>
  </div>

  <div class="grid grid-cols-7 gap-1">
  <div
   v-for="(day, index) in calendarDays"
   :key="index"
   :class="[
   'p-2 rounded-md flex items-center justify-center text-sm cursor-pointer',
   day === null ? 'bg-gray-50 text-gray-300' : 'text-gray-800 bg-white hover:bg-blue-50',
   isHighlighted(day) ? 'bg-red-100 text-red-700 font-semibold' : '',
   selectedDate &&
   day === selectedDate.getDate() &&
   currentDate.getMonth() === selectedDate.getMonth() &&
   currentDate.getFullYear() === selectedDate.getFullYear()
    ? 'border-2 border-blue-500'
    : '',
   ]"
   @click="selectDay(day)"
  >
   {{ day }}
  </div>
  </div>
 </div>

 <div
  class="flex flex-col border-t md:border-t-0 md:border-l pt-4 md:pt-0 md:pl-6 h-full overflow-y-auto"
 >
  <div class="flex justify-between items-center mb-2">
  <p class="text-sm font-medium text-gray-700">Reminders: {{ reminderCurrentDate }}</p>
  </div>
  <div v-if="selectedDayReminders.length > 0">
  <div
   v-for="reminder in selectedDayReminders"
   :key="reminder.id"
   class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-2 rounded-md"
  >
   <p class="font-semibold text-sm">
   {{
    new Date(reminder.dueDate).toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    })
   }}
   </p>
   <p class="text-xs">
   DOCUMENT ({{ reminder.documentNo }}) Deadline: {{ reminder.subject }}
   </p>
   <p class="text-xs">Final Action Office: {{ reminder.finalActionOffice }}</p>
  </div>
  </div>
  <div v-else class="text-gray-500 text-sm">No Due Documents for the selected day.</div>
 </div>
 </div>

 <template #footer>
 <button
  type="button"
  class="inline-flex justify-center text-end rounded-md border border-transparent bg-blue-100 px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
  @click="close"
 >
  Close
 </button>
 </template>
</Dialog>
</template>
