<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import Pagination from '@/components/ui/pagination/Pagination.vue'
import { sampleDocuments } from '@/data/duedocuments'
import { MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/outline'
import type { Document, FilterType, SearchableFields } from '@/type/index'
import DropdownMenu from '@/components/ui/dropdownmenu/DropdownMenu.vue'
import Button from '@/components/ui/button/Button.vue'
import { useLoading } from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'

// State
const searchTerm = ref('')
const currentFilter = ref<FilterType>('all')
const currentPage = ref(1)
const itemsPerPage = ref(5)
const allDocuments = ref<Document[]>([])

const $loading = useLoading()
let loader: ReturnType<typeof $loading.show> | null = null

onMounted(async () => {
  loader = $loading.show({ loader: 'dots', color: '#006400', backgroundColor: '#ffffff' })
  await new Promise((resolve) => setTimeout(resolve, 800))
  allDocuments.value = sampleDocuments
  loader?.hide()
})

// Search and filter logic
const searchableFields: SearchableFields[] = ['documentNo', 'subject', 'finalActionOffice']

const filteredDocuments = computed(() => {
  let filtered = allDocuments.value

  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase()
    filtered = filtered.filter((doc) =>
      searchableFields.some((field) => doc[field].toLowerCase().includes(search)),
    )
  }

  if (currentFilter.value !== 'all') {
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const tomorrow = new Date(today)
    tomorrow.setDate(today.getDate() + 1)

    filtered = filtered.filter((doc) => {
      const docDate = new Date(doc.dueDate)
      docDate.setHours(0, 0, 0, 0)
      switch (currentFilter.value) {
        case 'today':
          return docDate.getTime() === today.getTime()
        case 'tomorrow':
          return docDate.getTime() === tomorrow.getTime()
        default:
          return true
      }
    })
  }

  return filtered
})

// Pagination
const paginatedDocuments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredDocuments.value.slice(start, start + itemsPerPage.value)
})

// Event handlers
const applyFilter = async (filter: FilterType) => {
  loader = $loading.show({ loader: 'dots', color: '#006400', backgroundColor: '#ffffff' })
  await new Promise((resolve) => setTimeout(resolve, 500))
  currentFilter.value = filter
  currentPage.value = 1
  loader?.hide()
}

let searchTimeout: NodeJS.Timeout
const handleSearch = (event: Event) => {
  const target = event.target as HTMLInputElement
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    searchTerm.value = target.value
    currentPage.value = 1
  }, 300)
}

const handlePageChange = async (page: number) => {
  loader = $loading.show({ loader: 'dots', color: '#006400', backgroundColor: '#ffffff' })
  await new Promise((resolve) => setTimeout(resolve, 400))
  currentPage.value = page
  loader?.hide()
}

const handleItemsPerPageChange = async (items: number) => {
  loader = $loading.show({ loader: 'dots', color: '#006400', backgroundColor: '#ffffff' })
  await new Promise((resolve) => setTimeout(resolve, 400))
  itemsPerPage.value = items
  currentPage.value = 1
  loader?.hide()
}

const formatDate = (date: Date): string => {
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(new Date(date))
}

const getStatusClass = (dueDate: Date): string => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const tomorrow = new Date(today)
  tomorrow.setDate(today.getDate() + 1)
  const docDate = new Date(dueDate)
  docDate.setHours(0, 0, 0, 0)

  if (docDate.getTime() === today.getTime()) return 'bg-red-50 text-red-700'
  if (docDate.getTime() === tomorrow.getTime()) return 'bg-amber-50 text-amber-700'
  if (docDate < today) return 'bg-red-50 text-red-700 font-semibold'
  return ''
}

const getStatusText = (dueDate: Date): string => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const tomorrow = new Date(today)
  tomorrow.setDate(today.getDate() + 1)
  const docDate = new Date(dueDate)
  docDate.setHours(0, 0, 0, 0)

  if (docDate.getTime() === today.getTime()) return 'Due Today'
  if (docDate.getTime() === tomorrow.getTime()) return 'Due Tomorrow'
  if (docDate < today) return 'Overdue'
  return ''
}
</script>

<template>
  <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
      <Button @click="applyFilter('all')"> All documents </Button>
    </div>

    <div
      class="flex items-center justify-between gap-4 mb-6 flex-col items-stretch md:flex-row md:items-center"
    >
      <div class="flex-1 relative">
        <MagnifyingGlassIcon
          class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
        />
        <input
          type="text"
          placeholder="Search documents..."
          class="w-half pl-10 pr-3 py-2 border border-gray-300 rounded-md text-sm outline-none transition-colors duration-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/10"
          @input="handleSearch"
        />
      </div>

      <div class="relative w-full md:w-auto filter-container">
        <DropdownMenu button-label="Filter" :button-icon="FunnelIcon">

          <button
            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
            @click="applyFilter('today')"
          >
            <span
              class="w-4 h-4 rounded-full border-2 mr-2 flex-shrink-0"
              :class="{
                'border-green-700': currentFilter === 'today',
                'border-gray-400': currentFilter !== 'today',
              }"
            ></span>
            Documents Due Today
          </button>
          <button
            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
            @click="applyFilter('tomorrow')"
          >
            <span
              class="w-4 h-4 rounded-full border-2 mr-2 flex-shrink-0"
              :class="{
                'border-green-700': currentFilter === 'tomorrow',
                'border-gray-400': currentFilter !== 'tomorrow',
              }"
            ></span>
            Documents Due Tomorrow
          </button>
        </DropdownMenu>
      </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden overflow-x-auto">
      <table class="w-full border-collapse min-w-[600px]">
        <thead>
          <tr>
            <th
              v-for="header in [
                'Document No.',
                'Subject',
                'Final Action Office',
                'Due Date',
                'Status',
              ]"
              :key="header"
              class="bg-gray-50 py-3 px-4 text-center font-semibold text-gray-700 border-b border-gray-200"
            >
              {{ header }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="document in paginatedDocuments" :key="document.id" class="hover:bg-gray-50">
            <td class="py-4 px-4 text-center text-gray-800 border-b border-gray-100">
              {{ document.documentNo }}
            </td>
            <td class="py-4 px-4 text-center text-gray-800 border-b border-gray-100">
              {{ document.subject }}
            </td>
            <td class="py-4 px-4 text-center text-gray-800 border-b border-gray-100">
              {{ document.finalActionOffice }}
            </td>
            <td class="py-4 px-4 text-center text-gray-800 border-b border-gray-100">
              {{ formatDate(document.dueDate) }}
            </td>
            <td class="py-4 px-4 text-center text-gray-800 border-b border-gray-100">
              <span
                class="px-2 py-1 rounded text-xs font-medium"
                :class="getStatusClass(document.dueDate)"
              >
                {{ getStatusText(document.dueDate) }}
              </span>
            </td>
          </tr>
          <tr v-if="paginatedDocuments.length === 0">
            <td colspan="5" class="py-12 px-4 text-center text-gray-500">
              {{ searchTerm ? 'No documents found matching your search' : 'No documents found' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination
      :total-items="filteredDocuments.length"
      :initial-page="1"
      :initial-items-per-page="5"
      @page-change="handlePageChange"
      @items-per-page-change="handleItemsPerPageChange"
    />
  </div>
</template>
