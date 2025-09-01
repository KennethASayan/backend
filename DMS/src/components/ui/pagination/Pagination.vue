<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import type { PaginationProps, PaginationEmits } from '../../../types/index'

const props = withDefaults(defineProps<PaginationProps>(), {
  initialPage: 1,
  initialItemsPerPage: 5,
})

const emit = defineEmits<PaginationEmits>()

// Use internal refs for currentPage and itemsPerPage, but initialize from props
const currentPage = ref(props.initialPage)
const itemsPerPage = ref(props.initialItemsPerPage)

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(props.totalItems / itemsPerPage.value)) // Ensure at least 1 page if totalItems is 0
})

const visiblePages = computed(() => {
  const pages: number[] = []
  const total = totalPages.value
  const current = currentPage.value

  // Show 4 pages at a time
  let start = Math.max(1, current - 1)
  let end = Math.min(start + 3, total)

  // If we're near the end, adjust start to ensure 4 pages are shown if possible
  if (end - start + 1 < 4 && total >= 4) {
    // Only adjust if fewer than 4 pages are currently shown and there are enough total pages
    start = Math.max(1, total - 3)
    end = total // Ensure end is total if we're forcing last 4
  }
  // Ensure start is never less than 1
  start = Math.max(1, start)

  // Generate sequential page numbers
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    emit('page-change', currentPage.value)
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    emit('page-change', currentPage.value)
  }
}

const setCurrentPage = (page: number) => {
  if (page !== currentPage.value) {
    currentPage.value = page
    emit('page-change', currentPage.value)
  }
}

const onItemsPerPageChange = (newVal: string) => { // Changed type to string to match select element value
  const oldItemsPerPage = itemsPerPage.value
  const parsedValue = parseInt(newVal) // newVal is already a string here

  itemsPerPage.value = parsedValue
  emit('items-per-page-change', parsedValue)

  const firstItemIndexOnOldPage = (currentPage.value - 1) * oldItemsPerPage
  let newPage = Math.floor(firstItemIndexOnOldPage / parsedValue) + 1

  newPage = Math.min(newPage, totalPages.value || 1)
  newPage = Math.max(1, newPage)

  if (currentPage.value !== newPage) {
    currentPage.value = newPage
    emit('page-change', newPage)
  }
}

// This watcher now only reacts to external changes to initialPage/initialItemsPerPage props
// and ensures our internal refs stay in sync.
watch(
  () => props.initialPage, // Corrected to watch initialPage
  (newPage) => {
    if (currentPage.value !== newPage) {
      currentPage.value = newPage
    }
  },
)

watch(
  () => props.initialItemsPerPage,
  (newItems) => {
    if (itemsPerPage.value !== newItems) {
      itemsPerPage.value = newItems
      // When initialItemsPerPage changes externally, we need to re-evaluate currentPage
      // based on the new count, similar to onItemsPerPageChange
      // Calculate first item index based on the *old* currentPage and *new* itemsPerPage
      const oldFirstItemIndex = (currentPage.value - 1) * newItems
      let newPage = Math.floor(oldFirstItemIndex / newItems) + 1

      if (totalPages.value === 0) {
        newPage = 1
      } else {
        newPage = Math.min(newPage, totalPages.value)
      }
      newPage = Math.max(1, newPage)

      if (currentPage.value !== newPage) {
        currentPage.value = newPage
        emit('page-change', newPage)
      }
    }
  },
)

// Watch for changes in totalItems (important for dynamic data)
watch(
  () => props.totalItems,
  () => {
    // If the current page is now out of bounds for the new totalItems, adjust it
    if (currentPage.value > totalPages.value && totalPages.value > 0) {
      currentPage.value = totalPages.value
      emit('page-change', currentPage.value)
    } else if (totalPages.value === 0 && currentPage.value !== 1) {
      currentPage.value = 1
      emit('page-change', currentPage.value)
    }
  },
  { immediate: true },
)
</script>

<template>
  <div
    class="px-4 py-3 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 sm:px-6 gap-y-4"
  >
    <div class="text-sm text-gray-700">
      Showing
      <span class="font-semibold">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
      to
      <span class="font-semibold">{{ Math.min(currentPage * itemsPerPage, totalItems) }}</span>
      of
      <span class="font-semibold">{{ totalItems }}</span>
      results
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-2 w-full sm:w-auto">
      <div class="flex space-x-2">
        <button
          class="relative inline-flex items-center w-8 h-8 justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          :disabled="currentPage === 1"
          @click="previousPage"
        >
          <span class="sr-only">Previous</span>
          <ChevronLeftIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </button>

        <button
          v-for="page in visiblePages"
          :key="page"
          class="relative inline-flex items-center w-8 h-8 justify-center rounded-md border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
          :class="{
            'z-10 bg-green-500 border-green-500 text-white hover:bg-green-600':
              page === currentPage,
            'border-gray-300 bg-white text-gray-700 hover:bg-gray-50': page !== currentPage,
            // Removed 'cursor-default': page === '...' as visiblePages only returns numbers
          }"
          @click="setCurrentPage(page)"
          :disabled="false"
        >
          {{ page }}
        </button>

        <button
          class="relative inline-flex items-center w-8 h-8 justify-center rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          :disabled="currentPage === totalPages"
          @click="nextPage"
        >
          <span class="sr-only">Next</span>
          <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </button>
      </div>

      <div class="flex items-center">
        <select
          :value="itemsPerPage"
          @change="onItemsPerPageChange(($event.target as HTMLSelectElement).value)"
          class="block w-full sm:w-auto pl-3 pr-10 py-2 border-[1px] text-black text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md"
        >
          <option value="5">5 / Page</option>
          <option value="10">10 / Page</option>
          <option value="25">25 / Page</option>
          <option value="50">50 / Page</option>
        </select>
      </div>
    </div>
  </div>
</template>
