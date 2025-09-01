<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { EyeIcon, PencilSquareIcon, MapPinIcon } from '@heroicons/vue/24/outline'
import Pagination from '@/components/ui/pagination/Pagination.vue' // Assume this is your reusable component

// Import all document data sources
import { documents as incomingDocuments } from '@/data/incomingdocuments'
import { internalDocumentsForApproval } from '@/data/internaldocs(approval)'
import { internalActivityReports } from '@/data/internaldocs(activity)'
import { financialDocuments } from '@/data/financialdocuments'
import { actedCases } from '@/data/acteddocuments'
import { sampleDocuments } from '@/data/duedocuments'

const route = useRoute()
const searchQuery = computed(() => route.query.q || '')

// Pagination state
const currentPage = ref(1)
const itemsPerPage = ref(5)

// A single array combining all document types
const allDocuments = computed(() => [
  ...incomingDocuments.map(doc => ({ ...doc, docNo: doc.number })),
  ...internalDocumentsForApproval.map(doc => ({ ...doc, docNo: doc.number })),
  ...internalActivityReports.map(doc => ({ ...doc, docNo: doc.number })),
  ...financialDocuments.map(doc => ({ ...doc, docNo: doc.number })),
  ...actedCases.map(doc => ({ ...doc, docNo: doc.number })),
  ...sampleDocuments.map(doc => ({
    ...doc,
    docNo: doc.documentNo,
    subject: doc.subject || 'No Subject'
  }))
])

// The primary list of documents to display, filtered by search query
const filteredDocuments = computed(() => {
  if (!searchQuery.value) {
    return []
  }
  const query = searchQuery.value.toLowerCase()
  return allDocuments.value.filter(doc =>
    (doc.docNo?.toLowerCase().includes(query)) ||
    (doc.subject?.toLowerCase().includes(query))
  )
})

// Slice the filtered documents for the current page
const paginatedDocuments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredDocuments.value.slice(start, end)
})

// Watch for changes in the search query and reset the current page
watch(searchQuery, () => {
  currentPage.value = 1
}, { immediate: true })

// Pagination event handlers
const handlePageChange = (page) => {
  currentPage.value = page
}

const handleItemsPerPageChange = (items) => {
  itemsPerPage.value = items
  currentPage.value = 1
}
</script>

<template>
  <div class="p-4 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Search Results</h1>
    <div v-if="searchQuery" class="mb-4">
      <span class="text-lg text-gray-600">Showing results for: </span>
      <span class="text-lg font-semibold text-green-600">"{{ searchQuery }}"</span>
    </div>
    
    <div v-if="paginatedDocuments.length > 0" class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Document No.</th>
            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="document in paginatedDocuments" :key="document.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
              {{ document.docNo }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 max-w-lg truncate text-center">
              {{ document.subject }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
              <div class="flex justify-center space-x-2">
                <button class="text-gray-500 hover:text-blue-600 mx-1 p-1 rounded-full hover:bg-gray-100 transition-colors duration-200" title="View">
                  <EyeIcon class="w-5 h-5" />
                </button>
                <button class="text-gray-500 hover:text-green-600 mx-1 p-1 rounded-full hover:bg-gray-100 transition-colors duration-200" title="Edit">
                  <PencilSquareIcon class="w-5 h-5" />
                </button>
                <button class="text-red-500 hover:text-red-600 mx-1 p-1 rounded-full hover:bg-gray-100 transition-colors duration-200" title="Track Location">
                  <MapPinIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else class="text-center py-10 text-gray-500 text-lg">
      No documents found matching your search.
    </div>
    
    <Pagination
      v-if="filteredDocuments.length > 0"
      :total-items="filteredDocuments.length"
      :initial-page="currentPage"
      :initial-items-per-page="itemsPerPage"
      @page-change="handlePageChange"
      @items-per-page-change="handleItemsPerPageChange"
    />
  </div>
</template>