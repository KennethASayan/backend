<script setup lang="ts">
import { ref, computed } from 'vue'
import { DocumentIcon, EyeIcon } from '@heroicons/vue/24/outline'
import Pagination from '@/components/ui/pagination/Pagination.vue'

// Import the acted cases data from the new file
import { actedCases } from '@/data/acteddocuments'

// Pagination state
const currentPage = ref(1)
const itemsPerPage = ref(5)

// Use the imported data directly without ref()
const documents = actedCases

// Computed properties for pagination
const paginatedDocuments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return documents.slice(start, start + itemsPerPage.value)
})

// Pagination event handlers
const handlePageChange = (page: number) => {
  currentPage.value = page
}

const handleItemsPerPageChange = (items: number) => {
  itemsPerPage.value = items
  currentPage.value = 1
}
</script>

<template>
  <div class="bg-white rounded-lg shadow p-3 sm:p-4 md:p-6 font-sans">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6">
      <h2 class="text-lg sm:text-xl font-medium text-gray-900 mb-2 sm:mb-0">
        Acted Cases
      </h2>

    </div>

    <!-- Desktop Table View -->
    <div class="hidden lg:block overflow-x-auto rounded-lg">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg"
            >
              Document No.
            </th>
            <th
              class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Subject
            </th>
            <th
              class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg"
            >
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <tr v-for="doc in paginatedDocuments" :key="doc.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center">
              <span class="inline-flex items-center">
                <DocumentIcon class="w-4 h-4 mr-1 text-gray-400" />
                {{ doc.number }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-700 text-center max-w-xs">
              <div class="truncate" :title="doc.subject">{{ doc.subject }}</div>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <div class="flex justify-center">
                <button
                  class="text-gray-500 hover:text-blue-600 p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                  title="View Case"
                >
                  <EyeIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Tablet View -->
    <div class="hidden md:block lg:hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                Case Details
              </th>
              <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="doc in paginatedDocuments" :key="doc.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-3 py-4">
                <div class="flex items-start space-x-3">
                  <DocumentIcon class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" />
                  <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-gray-900">{{ doc.number }}</div>
                    <div class="text-sm text-gray-500 mt-1 line-clamp-2">{{ doc.subject }}</div>
                  </div>
                </div>
              </td>
              <td class="px-3 py-4 text-center">
                <button
                  class="text-gray-500 hover:text-blue-600 p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                  title="View Case"
                >
                  <EyeIcon class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
      <div
        v-for="doc in paginatedDocuments"
        :key="doc.id"
        class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200"
      >
        <!-- Header -->
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center space-x-3 min-w-0 flex-1">
            <DocumentIcon class="w-6 h-6 text-gray-400 flex-shrink-0" />
            <div class="min-w-0 flex-1">
              <div class="text-sm font-semibold text-gray-900">{{ doc.number }}</div>
              <div class="text-xs text-gray-500 mt-0.5">Case Number</div>
            </div>
          </div>
          <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full whitespace-nowrap">
            Acted
          </span>
        </div>

        <!-- Subject -->
        <div class="mb-4">
          <div class="text-sm font-medium text-gray-700 mb-2">Subject</div>
          <div class="text-sm text-gray-600 leading-relaxed">{{ doc.subject }}</div>
        </div>

        <!-- Action -->
        <div class="flex justify-end pt-3 border-t border-gray-100">
          <button
            class="flex items-center justify-center text-gray-500 hover:text-blue-600 px-4 py-3 rounded-lg hover:bg-blue-50 transition-all duration-200 min-w-[88px]"
            title="View Case Details"
          >
            <EyeIcon class="w-5 h-5 mr-2" />
            <span class="text-sm font-medium">View Case</span>
          </button>
        </div>
      </div>

      <!-- Mobile Empty State -->
      <div v-if="paginatedDocuments.length === 0" class="text-center py-12">
        <DocumentIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
        <div class="text-sm text-gray-500">No acted cases found</div>
        <div class="text-xs text-gray-400 mt-1">Completed cases will appear here</div>
      </div>
    </div>

    <!-- Pagination - Responsive -->
    <div class="mt-4 sm:mt-6">
      <Pagination
        :total-items="documents.length"
        :initial-page="currentPage"
        :initial-items-per-page="itemsPerPage"
        @page-change="handlePageChange"
        @items-per-page-change="handleItemsPerPageChange"
      />
    </div>
  </div>
</template>
