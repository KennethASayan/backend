<script>
import { ChevronDownIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/solid';

export default {
  name: 'DownloadSummary',
  components: {
    ChevronDownIcon,
    DocumentArrowDownIcon,
  },
  data() {
    return {
      selectedYear: '',
      selectedMonth: '',
      isDownloading: false,
      availableYears: [2019, 2020, 2021, 2022, 2023, 2024, 2025],
      availableMonths: [
        { value: '01', label: 'January' },
        { value: '02', label: 'February' },
        { value: '03', label: 'March' },
        { value: '04', label: 'April' },
        { value: '05', label: 'May' },
        { value: '06', label: 'June' },
        { value: '07', label: 'July' },
        { value: '08', label: 'August' },
        { value: '09', label: 'September' },
        { value: '10', label: 'October' },
        { value: '11', label: 'November' },
        { value: '12', label: 'December' }
      ],
      recentDownloads: [
        {
          id: 1,
          filename: 'summary_report_2024_01.pdf',
          type: 'pdf',
          date: '2024-08-10',
          size: '2.1 MB'
        },
        {
          id: 2,
          filename: 'document_export_2024_01.xlsx',
          type: 'excel',
          date: '2024-08-08',
          size: '1.8 MB'
        }
      ]
    }
  },
  computed: {
    mockStats() {
      if (!this.selectedYear || !this.selectedMonth) return {}
      
      // Seed for consistent "mock" stats based on the selected year and month
      const seed = parseInt(this.selectedYear) * 100 + parseInt(this.selectedMonth);
      const random = (s) => {
        const a = 1103515245;
        const c = 12345;
        const m = 2**31;
        s = (a * s + c) % m;
        return s / m;
      }
      
      const stats = {
        totalDocuments: Math.floor(random(seed) * 500) + 100,
        processed: Math.floor(random(seed + 1) * 400) + 50,
        pending: Math.floor(random(seed + 2) * 50) + 10,
        rejected: Math.floor(random(seed + 3) * 20) + 2
      };
      
      return stats;
    }
  },
  methods: {
    getCurrentDateTime() {
      const now = new Date()
      return now.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      })
    },
    
    selectYear(year) {
      this.selectedYear = year
      this.selectedMonth = ''
    },
    
    selectMonth(month) {
      this.selectedMonth = month
    },
    
    clearYear() {
      this.selectedYear = ''
      this.selectedMonth = ''
    },
    
    clearMonth() {
      this.selectedMonth = ''
    },
    
    clearSelection() {
      this.selectedYear = ''
      this.selectedMonth = ''
    },
    
    onYearChange() {
      this.selectedMonth = ''
    },
    
    getMonthName(monthValue) {
      const month = this.availableMonths.find(m => m.value === monthValue)
      return month ? month.label : ''
    },
    
    async downloadReport() {
      if (!this.selectedYear || !this.selectedMonth) return
      
      this.isDownloading = true
      
      try {
        // Simulate download process
        await new Promise(resolve => setTimeout(resolve, 3000))
        
        const filename = `report_${this.selectedYear}_${this.getMonthName(this.selectedMonth).toLowerCase()}.pdf`
        
        // Add to recent downloads
        this.recentDownloads.unshift({
          id: Date.now(),
          filename: filename,
          type: 'pdf',
          date: new Date().toISOString().split('T')[0],
          size: `${(Math.random() * 3 + 0.5).toFixed(1)} MB`
        })
        
        // Keep only last 5 downloads
        this.recentDownloads = this.recentDownloads.slice(0, 5)
        
        // Show success message or notification
        this.$emit('download-success', `Report for ${this.getMonthName(this.selectedMonth)} ${this.selectedYear} downloaded successfully!`)
        
      } catch (error) {
        console.error('Download error:', error)
        this.$emit('download-error', 'Failed to download report. Please try again.')
      } finally {
        this.isDownloading = false
      }
    },
    
    async redownload(download) {
      // Simulate re-download
      this.isDownloading = true
      await new Promise(resolve => setTimeout(resolve, 2000))
      this.isDownloading = false
    },
    
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
  }
}
</script>
<template>
  <div class="bg-gradient-to-br from-slate-50 to-blue-50 p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
      <div class="mb-8 border border-gray-200 bg-white rounded-lg p-6 shadow-sm">
        <div class="flex items-center justify-between mb-2">
          <h1 class="text-3xl md:text-4xl font-bold text-slate-800">Download Summary</h1>
          <div class="text-sm text-slate-500">
            as of {{ getCurrentDateTime() }}<br>
            Generated by: Kenneth Sayan
          </div>
        </div>
        <p class="text-slate-600">Generate and download comprehensive reports based on your selected criteria</p>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="border bg-gray-550 text-black px-6 py-4">
          <div class="grid grid-cols-3 gap-4">
            <div class="font-semibold text-sm uppercase tracking-wide">YEAR</div>
            <div class="font-semibold text-sm uppercase tracking-wide">MONTH</div>
            <div class="font-semibold text-sm uppercase tracking-wide text-center">ACTION</div>
          </div>
        </div>

        <div class="border-b border-slate-200 bg-slate-50">
          <div class="grid grid-cols-3 gap-4 px-6 py-4 items-center">
            <div class="relative">
              <select
                v-model="selectedYear"
                @change="onYearChange"
                class="w-full appearance-none bg-white border-2 border-slate-200 rounded-lg px-4 py-3 text-slate-700 font-medium focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all cursor-pointer hover:border-slate-300"
              >
                <option value="">Select Year</option>
                <option v-for="year in availableYears" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <ChevronDownIcon class="w-5 h-5 text-slate-400" />
              </div>
            </div>

            <div class="relative">
              <select
                v-model="selectedMonth"
                :disabled="!selectedYear"
                class="w-full appearance-none bg-white border-2 border-slate-200 rounded-lg px-4 py-3 text-slate-700 font-medium focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all cursor-pointer hover:border-slate-300 disabled:bg-slate-100 disabled:cursor-not-allowed disabled:text-slate-400"
              >
                <option value="">{{ selectedYear ? 'Select Month' : 'Select year first' }}</option>
                <option v-for="month in availableMonths" :key="month.value" :value="month.value">
                  {{ month.label }}
                </option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <ChevronDownIcon class="w-5 h-5 text-slate-400" />
              </div>
            </div>

            <div class="flex justify-center">
              <button
                @click="downloadReport"
                :disabled="!selectedYear || !selectedMonth || isDownloading"
                class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-lg hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-4 focus:ring-green-200 transition-all transform hover:scale-105 disabled:from-slate-300 disabled:to-slate-400 disabled:cursor-not-allowed disabled:transform-none flex items-center gap-2"
              >
                <svg v-if="isDownloading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <DocumentArrowDownIcon v-else class="w-4 h-4" />
                {{ isDownloading ? 'Downloading...' : 'DOWNLOAD' }}
              </button>
            </div>
          </div>
        </div>

        <div v-if="!selectedYear" class="p-6">
          <h3 class="text-lg font-semibold text-slate-700 mb-4">Available Years</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <button
              v-for="year in availableYears"
              :key="year"
              @click="selectYear(year)"
              class="p-4 text-center border-2 border-slate-200 rounded-xl hover:border-blue-300 hover:bg-blue-50 transition-all text-slate-600 hover:text-blue-600 font-medium"
            >
              {{ year }}
            </button>
          </div>
        </div>

        <div v-else-if="selectedYear && !selectedMonth" class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-700">Available Months for {{ selectedYear }}</h3>
            <button @click="clearYear" class="text-blue-600 hover:text-blue-700 font-medium">← Back to Years</button>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            <button
              v-for="month in availableMonths"
              :key="month.value"
              @click="selectMonth(month.value)"
              class="p-4 text-center border-2 border-slate-200 rounded-xl hover:border-blue-300 hover:bg-blue-50 transition-all text-slate-600 hover:text-blue-600 font-medium"
            >
              {{ month.label }}
            </button>
          </div>
        </div>

        <div v-else-if="selectedYear && selectedMonth" class="p-6">
          <div class="flex items-center justify-between mb-4 border-gray-50">
            <h3 class="text-lg font-semibold text-slate-700">Report Summary</h3>
            <div class="flex gap-2">
              <button @click="clearMonth" class="text-blue-600 hover:text-blue-700 font-medium">← Back to Months</button>
              <span class="text-slate-400">|</span>
              <button @click="clearSelection" class="text-blue-600 hover:text-blue-700 font-medium">Clear All</button>
            </div>
          </div>
          
          <div class="bg-white rounded-xl p-6 shadow-sm border border-blue-100">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ selectedYear }}</div>
                <div class="text-sm text-slate-500">Selected Year</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ getMonthName(selectedMonth) }}</div>
                <div class="text-sm text-slate-500">Selected Month</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ mockStats.totalDocuments }}</div>
                <div class="text-sm text-slate-500">Total Documents</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <div class="text-lg font-semibold text-green-700">{{ mockStats.processed }}</div>
                <div class="text-sm text-green-600">Processed</div>
              </div>
              <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <div class="text-lg font-semibold text-yellow-700">{{ mockStats.pending }}</div>
                <div class="text-sm text-yellow-600">Pending</div>
              </div>
              <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <div class="text-lg font-semibold text-red-700">{{ mockStats.rejected }}</div>
                <div class="text-sm text-red-600">Rejected</div>
              </div>
            </div>

            <div class="flex justify-center">
              <button
                @click="downloadReport"
                :disabled="isDownloading"
                class="px-8 py-3 bg-green-600 text-white font-semibold rounded-xl shadow-lg hover:from-green-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all transform hover:scale-105 disabled:from-slate-300 disabled:to-slate-400 disabled:cursor-not-allowed disabled:transform-none flex items-center gap-3"
              >
                <svg v-if="isDownloading" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <DocumentArrowDownIcon v-else class="w-5 h-5" />
                {{ isDownloading ? 'Generating Report...' : 'Download Complete Report' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>