<script setup lang="ts">
import { ref, computed } from 'vue'

// Reactive data
const selectedMonthRange = ref('all')

// Complete 12-month sample data
const allDocumentClassificationData = ref([
  { month: 'January', simple: 45, complex: 23, highlyTechnical: 12, legalConcern: 8, hotline: 15, noCompliance: 7, total: 110 },
  { month: 'February', simple: 52, complex: 28, highlyTechnical: 15, legalConcern: 10, hotline: 18, noCompliance: 9, total: 132 },
  { month: 'March', simple: 48, complex: 25, highlyTechnical: 14, legalConcern: 6, hotline: 16, noCompliance: 8, total: 117 },
  { month: 'April', simple: 41, complex: 21, highlyTechnical: 11, legalConcern: 9, hotline: 14, noCompliance: 6, total: 102 },
  { month: 'May', simple: 55, complex: 30, highlyTechnical: 18, legalConcern: 12, hotline: 20, noCompliance: 10, total: 145 },
  { month: 'June', simple: 47, complex: 24, highlyTechnical: 13, legalConcern: 7, hotline: 17, noCompliance: 8, total: 116 },
  { month: 'July', simple: 50, complex: 26, highlyTechnical: 16, legalConcern: 11, hotline: 19, noCompliance: 9, total: 131 },
  { month: 'August', simple: 44, complex: 22, highlyTechnical: 10, legalConcern: 8, hotline: 13, noCompliance: 5, total: 102 },
  { month: 'September', simple: 49, complex: 27, highlyTechnical: 15, legalConcern: 9, hotline: 18, noCompliance: 7, total: 125 },
  { month: 'October', simple: 53, complex: 29, highlyTechnical: 17, legalConcern: 13, hotline: 21, noCompliance: 11, total: 144 },
  { month: 'November', simple: 46, complex: 23, highlyTechnical: 12, legalConcern: 6, hotline: 15, noCompliance: 8, total: 110 },
  { month: 'December', simple: 51, complex: 28, highlyTechnical: 14, legalConcern: 10, hotline: 17, noCompliance: 9, total: 129 }
])

const allReferredToData = ref([
  { month: 'January', ored: 25, ardMs: 18, ardTs: 15, mgb: 22, emb: 12, rpao: 10, tfbm: 8, total: 110 },
  { month: 'February', ored: 30, ardMs: 22, ardTs: 18, mgb: 26, emb: 15, rpao: 12, tfbm: 9, total: 132 },
  { month: 'March', ored: 27, ardMs: 20, ardTs: 16, mgb: 24, emb: 13, rpao: 11, tfbm: 6, total: 117 },
  { month: 'April', ored: 23, ardMs: 16, ardTs: 14, mgb: 21, emb: 11, rpao: 9, tfbm: 8, total: 102 },
  { month: 'May', ored: 32, ardMs: 24, ardTs: 20, mgb: 28, emb: 17, rpao: 14, tfbm: 10, total: 145 },
  { month: 'June', ored: 26, ardMs: 19, ardTs: 15, mgb: 23, emb: 12, rpao: 11, tfbm: 10, total: 116 },
  { month: 'July', ored: 29, ardMs: 21, ardTs: 17, mgb: 25, emb: 14, rpao: 13, tfbm: 12, total: 131 },
  { month: 'August', ored: 24, ardMs: 17, ardTs: 13, mgb: 20, emb: 10, rpao: 10, tfbm: 8, total: 102 },
  { month: 'September', ored: 28, ardMs: 20, ardTs: 16, mgb: 24, emb: 13, rpao: 12, tfbm: 12, total: 125 },
  { month: 'October', ored: 31, ardMs: 23, ardTs: 19, mgb: 27, emb: 16, rpao: 15, tfbm: 13, total: 144 },
  { month: 'November', ored: 25, ardMs: 18, ardTs: 14, mgb: 22, emb: 11, rpao: 11, tfbm: 9, total: 110 },
  { month: 'December', ored: 28, ardMs: 21, ardTs: 17, mgb: 25, emb: 14, rpao: 13, tfbm: 11, total: 129 }
])

const allFinalActionData = ref([
  { month: 'January', admin: 12, finance: 8, legal: 6, pmd: 15, rpao: 18, cdd: 10, ed: 14, lpdd: 9, smd: 11, inremp: 7, total: 110 },
  { month: 'February', admin: 15, finance: 10, legal: 8, pmd: 18, rpao: 22, cdd: 12, ed: 16, lpdd: 11, smd: 13, inremp: 7, total: 132 },
  { month: 'March', admin: 13, finance: 9, legal: 7, pmd: 16, rpao: 20, cdd: 11, ed: 15, lpdd: 10, smd: 12, inremp: 4, total: 117 },
  { month: 'April', admin: 11, finance: 7, legal: 6, pmd: 14, rpao: 17, cdd: 9, ed: 13, lpdd: 8, smd: 10, inremp: 7, total: 102 },
  { month: 'May', admin: 16, finance: 11, legal: 9, pmd: 20, rpao: 24, cdd: 13, ed: 18, lpdd: 12, smd: 15, inremp: 7, total: 145 },
  { month: 'June', admin: 13, finance: 9, legal: 7, pmd: 16, rpao: 19, cdd: 11, ed: 15, lpdd: 10, smd: 12, inremp: 4, total: 116 },
  { month: 'July', admin: 15, finance: 10, legal: 8, pmd: 18, rpao: 21, cdd: 12, ed: 16, lpdd: 11, smd: 13, inremp: 7, total: 131 },
  { month: 'August', admin: 11, finance: 8, legal: 6, pmd: 14, rpao: 17, cdd: 9, ed: 13, lpdd: 8, smd: 10, inremp: 6, total: 102 },
  { month: 'September', admin: 14, finance: 9, legal: 7, pmd: 17, rpao: 20, cdd: 11, ed: 15, lpdd: 10, smd: 13, inremp: 9, total: 125 },
  { month: 'October', admin: 16, finance: 11, legal: 9, pmd: 20, rpao: 23, cdd: 13, ed: 17, lpdd: 12, smd: 14, inremp: 9, total: 144 },
  { month: 'November', admin: 12, finance: 8, legal: 6, pmd: 15, rpao: 18, cdd: 10, ed: 14, lpdd: 9, smd: 11, inremp: 7, total: 110 },
  { month: 'December', admin: 14, finance: 10, legal: 8, pmd: 18, rpao: 21, cdd: 12, ed: 16, lpdd: 11, smd: 13, inremp: 6, total: 129 }
])

// Filtered data based on selections
const documentClassificationData = computed(() => filterDataByMonth(allDocumentClassificationData.value))
const referredToData = computed(() => filterDataByMonth(allReferredToData.value))
const finalActionData = computed(() => filterDataByMonth(allFinalActionData.value))

const currentDate = computed(() => {
  const now = new Date();
  const options = {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  };
  return now.toLocaleString('en-US', options as Intl.DateTimeFormatOptions);
});

// Methods
const filterDataByMonth = (data: any[]) => {
  const currentMonthName = new Date().toLocaleString('default', { month: 'long' });
  const allMonths = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  const currentMonthIndex = allMonths.indexOf(currentMonthName);

  switch (selectedMonthRange.value) {
    case 'current-month':
      return data.filter(item => item.month === currentMonthName);

    case 'last-3-months':
      const last3Months = allMonths.slice(currentMonthIndex - 2, currentMonthIndex + 1);
      return data.filter(item => last3Months.includes(item.month));

    case 'last-6-months':
      const last6Months = allMonths.slice(currentMonthIndex - 5, currentMonthIndex + 1);
      return data.filter(item => last6Months.includes(item.month));

    case 'quarter-1':
      return data.filter(item => ['January', 'February', 'March'].includes(item.month));

    case 'quarter-2':
      return data.filter(item => ['April', 'May', 'June'].includes(item.month));

    case 'quarter-3':
      return data.filter(item => ['July', 'August', 'September'].includes(item.month));

    case 'quarter-4':
      return data.filter(item => ['October', 'November', 'December'].includes(item.month));

    case 'all':
    default:
      return data;
  }
}
</script>

<template>
  <div class=" bg-gray-50">
    <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6 xl:px-8 py-4 sm:py-6 lg:py-8">
      <div class="bg-white rounded-lg shadow-sm border p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
        <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:justify-between sm:items-center">
         <div>
            <h2 class="text-lg md:text-xl font-semibold text-gray-900">Report Information</h2>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">Document tracking and classification summary</p>
        </div>
          <div class="text-left sm:text-right">
            <p class="text-xs sm:text-sm text-gray-600">Generated on: {{ currentDate }}</p>
            <p class="text-xs sm:text-sm text-gray-600">Generated by: Kenneth Sayan</p>
          </div>
        </div>
      </div>

      <!-- Document Classification Table -->
      <div class="bg-white rounded-lg shadow-sm border mb-4 sm:mb-6 overflow-hidden">
        <div class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 border-b border-gray-200">
          <h3 class="text-base sm:text-lg font-medium text-gray-900">Document Classification</h3>
        </div>
        
        <!-- Mobile Card View -->
        <div class="block sm:hidden">
          <div v-for="(row, index) in documentClassificationData" :key="index" class="border-b border-gray-200 p-4 space-y-2">
            <div class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2 mb-2">{{ row.month }}</div>
            <div class="grid grid-cols-2 gap-2 text-xs">
              <div class="flex justify-between">
                <span class="text-gray-600">Simple:</span>
                <span class="font-medium">{{ row.simple }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Complex:</span>
                <span class="font-medium">{{ row.complex }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Highly Technical:</span>
                <span class="font-medium">{{ row.highlyTechnical }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Legal Concern:</span>
                <span class="font-medium">{{ row.legalConcern }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Hotline:</span>
                <span class="font-medium">{{ row.hotline }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">No Compliance:</span>
                <span class="font-medium">{{ row.noCompliance }}</span>
              </div>
            </div>
            <div class="flex justify-between pt-2 mt-2 border-t border-gray-100 bg-blue-50 -mx-4 px-4 py-2">
              <span class="text-gray-700 font-medium">Total:</span>
              <span class="font-bold text-blue-700">{{ row.total }}</span>
            </div>
          </div>
        </div>

        <!-- Desktop/Tablet Table View -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr> 
                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Simple</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Complex</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Highly Technical</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Legal Concern</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Hotline</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">No Compliance</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50">Total</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(row, index) in documentClassificationData" :key="index" :class="index % 2 === 0 ? 'bg-gray-50' : 'hover:bg-gray-50'">
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">{{ row.month }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center">{{ row.simple }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center">{{ row.complex }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden md:table-cell">{{ row.highlyTechnical }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden lg:table-cell">{{ row.legalConcern }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden lg:table-cell">{{ row.hotline }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden xl:table-cell">{{ row.noCompliance }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm font-semibold text-gray-900 text-center bg-blue-50">{{ row.total }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Referred To Table -->
      <div class="bg-white rounded-lg shadow-sm border mb-4 sm:mb-6 overflow-hidden">
        <div class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 border-b border-gray-200">
          <h3 class="text-base sm:text-lg font-medium text-gray-900">Referred To</h3>
        </div>
        
        <!-- Mobile Card View -->
        <div class="block sm:hidden">
          <div v-for="(row, index) in referredToData" :key="index" class="border-b border-gray-200 p-4 space-y-2">
            <div class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2 mb-2">{{ row.month }}</div>
            <div class="grid grid-cols-2 gap-2 text-xs">
              <div class="flex justify-between">
                <span class="text-gray-600">ORED:</span>
                <span class="font-medium">{{ row.ored }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">ARD MS:</span>
                <span class="font-medium">{{ row.ardMs }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">ARD TS:</span>
                <span class="font-medium">{{ row.ardTs }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">MGB:</span>
                <span class="font-medium">{{ row.mgb }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">EMB:</span>
                <span class="font-medium">{{ row.emb }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">RPAO:</span>
                <span class="font-medium">{{ row.rpao }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">TFBM:</span>
                <span class="font-medium">{{ row.tfbm }}</span>
              </div>
            </div>
            <div class="flex justify-between pt-2 mt-2 border-t border-gray-100 bg-blue-50 -mx-4 px-4 py-2">
              <span class="text-gray-700 font-medium">Total:</span>
              <span class="font-bold text-blue-700">{{ row.total }}</span>
            </div>
          </div>
        </div>

        <!-- Desktop/Tablet Table View -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ORED</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ARD MS</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ARD TS</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">MGB</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">EMB</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">RPAO</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">TFBM</th>
                <th class="px-2 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50">Total</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(row, index) in referredToData" :key="index" :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">{{ row.month }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center">{{ row.ored }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center">{{ row.ardMs }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center">{{ row.ardTs }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden md:table-cell">{{ row.mgb }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden lg:table-cell">{{ row.emb }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden lg:table-cell">{{ row.rpao }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden xl:table-cell">{{ row.tfbm }}</td>
                <td class="px-2 lg:px-6 py-4 whitespace-nowrap text-xs sm:text-sm font-semibold text-gray-900 text-center bg-blue-50">{{ row.total }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Final Action Offices Table -->
      <div class="bg-white rounded-lg shadow-sm border mb-4 sm:mb-6 overflow-hidden">
        <div class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 border-b border-gray-200">
          <h3 class="text-base sm:text-lg font-medium text-gray-900">Final Action Offices</h3>
        </div>
        
        <!-- Mobile Card View -->
        <div class="block sm:hidden">
          <div v-for="(row, index) in finalActionData" :key="index" class="border-b border-gray-200 p-4 space-y-2">
            <div class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2 mb-2">{{ row.month }}</div>
            <div class="grid grid-cols-2 gap-2 text-xs">
              <div class="flex justify-between">
                <span class="text-gray-600">ADMIN:</span>
                <span class="font-medium">{{ row.admin }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">FINANCE:</span>
                <span class="font-medium">{{ row.finance }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">LEGAL:</span>
                <span class="font-medium">{{ row.legal }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">PMD:</span>
                <span class="font-medium">{{ row.pmd }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">RPAO:</span>
                <span class="font-medium">{{ row.rpao }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">CDD:</span>
                <span class="font-medium">{{ row.cdd }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">ED:</span>
                <span class="font-medium">{{ row.ed }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">LPDD:</span>
                <span class="font-medium">{{ row.lpdd }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">SMD:</span>
                <span class="font-medium">{{ row.smd }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">INREMP:</span>
                <span class="font-medium">{{ row.inremp }}</span>
              </div>
            </div>
            <div class="flex justify-between pt-2 mt-2 border-t border-gray-100 bg-blue-50 -mx-4 px-4 py-2">
              <span class="text-gray-700 font-medium">Total:</span>
              <span class="font-bold text-blue-700">{{ row.total }}</span>
            </div>
          </div>
        </div>

        <!-- Desktop/Tablet Table View -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ADMIN</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">FINANCE</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">LEGAL</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">PMD</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">RPAO</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">CDD</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">ED</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">LPDD</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">SMD</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">INREMP</th>
                <th class="px-1 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50">TOTAL</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(row, index) in finalActionData" :key="index" :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                <td class="px-2 lg:px-4 py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">{{ row.month }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center">{{ row.admin }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center">{{ row.finance }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center">{{ row.legal }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center">{{ row.pmd }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden md:table-cell">{{ row.rpao }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden lg:table-cell">{{ row.cdd }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden lg:table-cell">{{ row.ed }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden xl:table-cell">{{ row.lpdd }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden xl:table-cell">{{ row.smd }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 text-center hidden xl:table-cell">{{ row.inremp }}</td>
                <td class="px-1 lg:px-3 py-4 whitespace-nowrap text-xs sm:text-sm font-semibold text-gray-900 text-center bg-blue-50">{{ row.total }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>