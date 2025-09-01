<script setup lang="ts">
import { ref, computed } from 'vue'
import DropdownMenu from '@/components/ui/dropdownmenu/DropdownMenu.vue'
import {
  januaryData,
  februaryData,
  marchData,
  aprilData,
  mayData,
  juneData,
  julyData,
  augustData,
  septemberData,
  octoberData,
  novemberData,
  decemberData,
} from '@/data/Month'
import { ChevronDownIcon, CalendarIcon, PrinterIcon, ArrowLeftIcon } from '@heroicons/vue/24/solid'
import { firstSemesterData, secondSemesterData } from '@/data/Semester'
import html2canvas from 'html2canvas'
import { jsPDF } from 'jspdf'

// Reactive state variables
const activeTab = ref('monthly')
const convertMode = ref('days')
const selectedYear = ref('2025')
const selectedMonth = ref<string | null>(null)
const pdfContainerRef = ref<HTMLDivElement | null>(null)
const isGeneratingPDF = ref(false) // 👈 New reactive variable for loading state

// Get the current date and format it for display
const currentDate = new Date().toLocaleString('en-US', {
  year: 'numeric',
  month: 'long',
  day: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
  hour12: true,
})

// Define month data
const months = [
  { id: 'january', name: 'January' },
  { id: 'february', name: 'February' },
  { id: 'march', name: 'March' },
  { id: 'april', name: 'April' },
  { id: 'may', name: 'May' },
  { id: 'june', name: 'June' },
  { id: 'july', name: 'July' },
  { id: 'august', name: 'August' },
  { id: 'september', name: 'September' },
  { id: 'october', name: 'October' },
  { id: 'november', name: 'November' },
  { id: 'december', name: 'December' },
]

// Map month IDs to their data files
const monthlyData = {
  january: januaryData,
  february: februaryData,
  march: marchData,
  april: aprilData,
  may: mayData,
  june: juneData,
  july: julyData,
  august: augustData,
  september: septemberData,
  october: octoberData,
  november: novemberData,
  december: decemberData,
}

// Function to parse a value string and convert it between days and hours
const parseAndConvert = (value: string, convertToHours: boolean) => {
  const regex = /\((\d+)\)\s\((\d+)\)\s([\d.]+)/
  const match = value.match(regex)
  if (match) {
    const num = parseFloat(match[1])
    const actedNum = parseFloat(match[2])
    let average = parseFloat(match[3])
    if (convertToHours) {
      // Convert from days to hours by multiplying by 8
      average = average * 8
    } else {
      // Convert from hours to days by dividing by 8
      average = average / 8
    }
    return `(${num}) (${actedNum}) ${average.toFixed(2)}`
  }
  return value
}

// Methods to handle UI interactions
const setActiveTab = (tab: string) => {
  activeTab.value = tab
  if (tab === 'semester') {
    selectedMonth.value = null
  }
}

const selectMonth = (monthId: string) => {
  selectedMonth.value = monthId
}

const goBackToMonths = () => {
  selectedMonth.value = null
}

const getMonthName = (monthId: string) => {
  return months.find((month) => month.id === monthId)?.name || ''
}

// Computed properties for dynamic content
const convertButtonText = computed(() => {
  return convertMode.value === 'hours' ? 'CONVERT TO DAYS' : 'CONVERT TO HOURS'
})

// List of keys to convert from days to hours or vice-versa
const keysToConvert = [
  'admin',
  'finance',
  'legal',
  'pmd',
  'cdd',
  'ed',
  'lpdd',
  'smd',
  'rscig',
  'oardtsTaskForce',
  'totalAve',
]

// Reusable function to convert table data
const convertTableData = (data: any[]) => {
  const convertToHours = convertMode.value === 'hours'
  return data.map((row) => {
    const newRow = { ...row }
    keysToConvert.forEach((key) => {
      if (
        typeof newRow[key] === 'string' &&
        newRow[key].includes('(') &&
        newRow[key].includes(')')
      ) {
        newRow[key] = parseAndConvert(newRow[key], convertToHours)
      }
    })
    return newRow
  })
}

// Computed properties for converting data based on the selected mode
const convertedMonthlyData = computed(() => {
  if (!selectedMonth.value) {
    return []
  }
  const data = monthlyData[selectedMonth.value as keyof typeof monthlyData] || []
  return convertTableData(data)
})

const convertedFirstSemesterData = computed(() => {
  return convertTableData(firstSemesterData)
})

const convertedSecondSemesterData = computed(() => {
  return convertTableData(secondSemesterData)
})

// Column headers for dynamic mobile view
const tableHeaders = [
  { key: 'documentAction', label: 'Document Action Information' },
  { key: 'admin', label: 'Admin' },
  { key: 'finance', label: 'Finance' },
  { key: 'legal', label: 'Legal' },
  { key: 'pmd', label: 'PMD' },
  { key: 'cdd', label: 'CDD' },
  { key: 'ed', label: 'ED' },
  { key: 'lpdd', label: 'LPDD' },
  { key: 'smd', label: 'SMD' },
  { key: 'rscig', label: 'RSCIG' },
  { key: 'oardtsTaskForce', label: 'OARDTS-Task Force' },
  { key: 'totalAve', label: 'TOTAL | AVE' },
]

const generatePdf = async () => {
  // 👈 Set loading state to true
  isGeneratingPDF.value = true
  const elementToPrint = pdfContainerRef.value
  if (!elementToPrint) {
    isGeneratingPDF.value = false
    return
  }

  try {
    const doc = new jsPDF('l', 'mm', 'a4') // 'l' for landscape
    const pdfPageWidth = doc.internal.pageSize.getWidth()
    const pdfPageHeight = doc.internal.pageSize.getHeight()

    const horizontalMargin = 10 // 10mm from left and right edges
    const verticalMargin = 10 // 10mm from top and bottom edges
    const contentWidth = pdfPageWidth - 2 * horizontalMargin // Usable width for content
    const contentHeight = pdfPageHeight - 2 * verticalMargin // Usable height for content

    // Helper function to dynamically create and append a table to an HTML element
    const appendTableContentToElement = (targetElement, data, title = null) => {
      if (title) {
        const titleEl = document.createElement('h2')
        titleEl.className = 'mb-2 text-xl font-bold text-center mt-8'
        titleEl.innerText = title
        targetElement.appendChild(titleEl)
      }

      const table = document.createElement('table')
      table.className = 'w-full border border-spacing-0 text-xs text-black'
      const thead = document.createElement('thead')
      const headerRow = document.createElement('tr')
      const headers = [
        'Document Action Information',
        'Admin',
        'Finance',
        'Legal',
        'PMD',
        'CDD',
        'ED',
        'LPDD',
        'SMD',
        'RSCIG',
        'OARDTS-Task Force',
        'TOTAL | AVE',
      ]
      headers.forEach((hText) => {
        const th = document.createElement('th')
        th.className = 'p-3 text-center font-medium border text-sm'
        th.innerText = hText
        headerRow.appendChild(th)
      })
      thead.appendChild(headerRow)
      table.appendChild(thead)

      const tbody = document.createElement('tbody')
      data.forEach((rowData) => {
        const row = document.createElement('tr')
        row.className = 'border-b'
        const rowValues = [
          rowData.documentAction,
          rowData.admin,
          rowData.finance,
          rowData.legal,
          rowData.pmd,
          rowData.cdd,
          rowData.ed,
          rowData.lpdd,
          rowData.smd,
          rowData.rscig,
          rowData.oardtsTaskForce,
          rowData.totalAve,
        ]
        rowValues.forEach((value) => {
          const td = document.createElement('td')
          td.className = 'p-3 text-center border text-xs'
          td.innerText = value
          row.appendChild(td)
        })
        tbody.appendChild(row)
      })
      table.appendChild(tbody)
      targetElement.appendChild(table)
    }

    // Re-usable function to generate a full PDF page from given data and title
    const generateAndAddPdfPage = async (data, title, addMainHeader = false) => {
      elementToPrint.innerHTML = '' // Clear previous content

      // Main header for all types of reports, placed once at the top if requested
      if (addMainHeader) {
        const mainHeaderDiv = document.createElement('div')
        mainHeaderDiv.className = 'text-center font-bold text-lg mb-4 text-black'
        mainHeaderDiv.innerHTML = `<h2 class="mb-2 text-xl font-bold">Document Monitoring System</h2>
<p class="text-sm text-gray-600">Database Population </p>
<p class="text-sm text-gray-600">Date Generated: ${currentDate}</p>
<p class="text-sm text-gray-600">Generated by: Kenneth Sayan</p>`
        elementToPrint.appendChild(mainHeaderDiv)
      }

      // Specific report title
      const reportTitleDiv = document.createElement('div')
      reportTitleDiv.className = 'text-center font-bold text-lg mb-4 text-black'
      reportTitleDiv.innerHTML = `<h1 class="text-2xl font-bold">${title}</h1>`
      elementToPrint.appendChild(reportTitleDiv)

      appendTableContentToElement(elementToPrint, data)

      // 🖼️ The crucial change for clear printing:
      // Increase the scale for better resolution. A value of 3 or 4 is ideal for clear text.
      const canvas = await html2canvas(elementToPrint, { scale: 3 })
      const imgData = canvas.toDataURL('image/jpeg', 1.0)

      // Calculate image dimensions to fit the PDF page while maintaining aspect ratio
      const imgRatio = canvas.width / canvas.height
      let imgWidth = contentWidth
      let imgHeight = imgWidth / imgRatio

      // If the image is too tall, scale it down to fit the page height
      if (imgHeight > contentHeight) {
        imgHeight = contentHeight
        imgWidth = imgHeight * imgRatio
      }

      // Add the image to the PDF with calculated dimensions
      doc.addImage(imgData, 'JPEG', horizontalMargin, verticalMargin, imgWidth, imgHeight)
    }

    if (activeTab.value === 'monthly') {
      if (selectedMonth.value) {
        const month = months.find((m) => m.id === selectedMonth.value)
        if (month) {
          const data = monthlyData[month.id as keyof typeof monthlyData] || []
          const convertedData = convertTableData(data)
          await generateAndAddPdfPage(convertedData, `${month.name}`, true)
        }
      } else {
        for (const [index, month] of months.entries()) {
          const data = monthlyData[month.id as keyof typeof monthlyData] || []
          const convertedData = convertTableData(data)
          if (index > 0) {
            doc.addPage()
          }
          await generateAndAddPdfPage(convertedData, `${month.name}`, index === 0)
        }
      }
    } else if (activeTab.value === 'semester') {
      elementToPrint.innerHTML = ''

      const mainHeaderDiv = document.createElement('div')
      mainHeaderDiv.className = 'text-center font-bold text-lg mb-4 text-black'
      mainHeaderDiv.innerHTML = `<h2 class="mb-2 text-xl font-bold">Document Monitoring System</h2>
<p class="text-sm text-gray-600">Database Population </p>
<p class="text-sm text-gray-600">Date Generated: ${currentDate}</p>
<p class="text-sm text-gray-600">Generated by: Kenneth Sayan</p>`
      elementToPrint.appendChild(mainHeaderDiv)

      appendTableContentToElement(
        elementToPrint,
        convertedFirstSemesterData.value,
        'Summary of Document Action: 1st Semester',
      )
      appendTableContentToElement(
        elementToPrint,
        convertedSecondSemesterData.value,
        'Summary of Document Action: 2nd Semester',
      )

      // 🖼️ The crucial change for clear printing on semester summary
      const canvas = await html2canvas(elementToPrint, { scale: 3 })
      const imgData = canvas.toDataURL('image/jpeg', 1.0)
      const imgRatio = canvas.width / canvas.height
      let imgWidth = contentWidth
      let imgHeight = imgWidth / imgRatio

      if (imgHeight > contentHeight) {
        imgHeight = contentHeight
        imgWidth = imgHeight * imgRatio
      }

      doc.addImage(imgData, 'JPEG', horizontalMargin, verticalMargin, imgWidth, imgHeight)
    }

    const pdfBlob = doc.output('blob')
    const url = URL.createObjectURL(pdfBlob)
    window.open(url, '_blank')
  } catch (error) {
    console.error('Error generating PDF:', error)
  } finally {
    isGeneratingPDF.value = false // 👈 Set loading state to false after completion
  }
}
</script>

<template>
  <div class="bg-gray-50 p-4 lg:p-6">
    <div class="mx-auto max-w-7xl">
      <div class="mb-6 flex items-center justify-center">
        <div class="flex rounded-lg bg-white p-1 shadow-sm">
          <button
            :class="[
              'rounded-md px-4 py-2 font-medium transition-all duration-200',
              activeTab === 'monthly'
                ? 'bg-blue-600 text-white shadow-sm ring-2 ring-blue-100'
                : 'text-gray-600 hover:text-gray-900',
            ]"
            @click="setActiveTab('monthly')"
          >
            Monthly Reports
          </button>
          <button
            :class="[
              'rounded-md px-4 py-2 font-medium transition-all duration-200',
              activeTab === 'semester'
                ? 'bg-blue-600 text-white shadow-sm ring-2 ring-blue-100'
                : 'text-gray-600 hover:text-gray-900',
            ]"
            @click="setActiveTab('semester')"
          >
            Semester Summary
          </button>
        </div>
      </div>

      <div class="mb-6 rounded-lg bg-white p-4 shadow-sm">
        <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
          <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
            <DropdownMenu
              :button-label="convertButtonText"
              button-class="inline-flex items-center justify-center py-2 px-4 rounded-md font-medium text-sm cursor-pointer transition-all duration-200 bg-[#099] text-white hover:bg-[#009980]"
              align="start"
              content-class="w-48"
            >
              <template #button-content>
                <div class="flex items-center gap-2">
                  <span>{{ convertButtonText }}</span>
                  <ChevronDownIcon class="h-4 w-4" />
                </div>
              </template>
              <DropdownMenuItem
                class="flex cursor-pointer items-center justify-between p-2 hover:bg-gray-100"
                @click="convertMode = 'hours'"
              >
                <span>CONVERT TO HOURS</span>
                <span v-if="convertMode === 'hours'"></span>
              </DropdownMenuItem>
              <DropdownMenuItem
                class="flex cursor-pointer items-center justify-between p-2 hover:bg-gray-100"
                @click="convertMode = 'days'"
              >
                <span>CONVERT TO DAYS</span>
                <span v-if="convertMode === 'days'"></span>
              </DropdownMenuItem>
            </DropdownMenu>

            <DropdownMenu
              :button-label="selectedYear"
              button-class="inline-flex items-center justify-center py-2 px-4 rounded-md font-medium text-sm cursor-pointer transition-all duration-200 bg-[#099] text-white hover:bg-[#009980]"
              align="center"
              content-class="w-[85px]"
            >
              <template #button-content>
                <div class="flex items-center gap-2">
                  <span>{{ selectedYear }}</span>
                  <ChevronDownIcon class="h-4 w-4" />
                </div>
              </template>
              <DropdownMenuItem
                class="flex cursor-pointer items-center justify-between p-2 hover:bg-gray-100"
                @click="selectedYear = '2024'"
              >
                <span>2024</span>
                <span v-if="selectedYear === '2024'"></span>
              </DropdownMenuItem>
              <DropdownMenuItem
                class="flex cursor-pointer items-center justify-between p-2 hover:bg-gray-100"
                @click="selectedYear = '2025'"
              >
                <span>2025</span>
                <span v-if="selectedYear === '2025'"></span>
              </DropdownMenuItem>
            </DropdownMenu>
          </div>

          <div class="flex flex-wrap gap-1">
            <button
              v-if="selectedMonth && activeTab === 'monthly'"
              class="rounded bg-gray-500 px-4 py-2 text-sm text-white transition-colors hover:bg-gray-600"
              @click="goBackToMonths"
            >
              <div class="flex items-center justify-center gap-2">
                <ArrowLeftIcon class="h-4 w-4" />
                <span>Back to Months</span>
              </div>
            </button>
            <button
              class="rounded bg-[#ca5f5f] px-6 py-2 text-sm text-white transition-colors hover:bg-[#c44f4f]"
              @click="generatePdf"
              :disabled="isGeneratingPDF"
            >
              <div v-if="isGeneratingPDF" class="flex items-center justify-center gap-2">
                <svg
                  class="animate-spin h-4 w-4 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                <span>Printing...</span>
              </div>
              <div v-else class="flex items-center justify-center gap-2">
                <PrinterIcon class="h-4 w-4" />
                <span>Print</span>
              </div>
            </button>
          </div>
        </div>
      </div>

      <div v-if="activeTab === 'monthly'">
        <div v-if="!selectedMonth" class="rounded-lg bg-white p-6 shadow-sm">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
              v-for="month in months"
              :key="month.id"
              class="cursor-pointer rounded-lg border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md"
              @click="selectMonth(month.id)"
            >
              <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded bg-gray-100">
                  <CalendarIcon class="h-5 w-5 text-gray-500" />
                </div>
                <span class="font-medium text-gray-900">{{ month.name }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="selectedMonth" class="rounded-lg bg-white shadow-sm">
          <div class="border-b p-6">
            <div class="text-center">
              <div class="mb-4 rounded-lg bg-gray-100 p-4">
                <h2 class="mb-2 text-xl font-bold">Document Monitoring System</h2>
                <p class="text-sm text-gray-600">Database Population</p>
                <p class="text-sm text-gray-600">Date Generated: {{ currentDate }}</p>
                <p class="text-sm text-gray-600">Generated by: Kenneth Sayan</p>
              </div>
              <h1 class="text-2xl font-bold text-gray-900">
                {{ getMonthName(selectedMonth) }}
              </h1>
            </div>
          </div>

          <div class="p-6">
            <div class="lg:hidden">
              <div
                v-for="(row, rowIndex) in convertedMonthlyData"
                :key="rowIndex"
                class="mb-4 rounded-lg border border-gray-200 p-4 shadow-sm"
              >
                <div
                  v-for="(header, colIndex) in tableHeaders"
                  :key="header.key"
                  class="flex items-start justify-between py-2 border-b last:border-b-0"
                >
                  <div class="font-medium text-gray-600 w-1/2 pr-2">{{ header.label }}</div>
                  <div class="text-right text-gray-800 w-1/2">{{ row[header.key] }}</div>
                </div>
              </div>
            </div>

            <div class="hidden lg:block">
              <div
                class="no-scrollbar overflow-x-auto scrollbar scrollbar-track-gray-100 scrollbar-thumb-gray-400"
              >
                <table class="w-full border border-spacing-0 text-xs lg:text-sm">
                  <thead>
                    <tr class="border-b bg-gray-50">
                      <th class="left-0 bg-gray-50 p-3 text-left font-medium z-10">
                        Document Action Information
                      </th>
                      <th class="p-3 text-center font-medium">Admin</th>
                      <th class="p-3 text-center font-medium">Finance</th>
                      <th class="p-3 text-center font-medium">Legal</th>
                      <th class="p-3 text-center font-medium">PMD</th>
                      <th class="p-3 text-center font-medium">CDD</th>
                      <th class="p-3 text-center font-medium">ED</th>
                      <th class="p-3 text-center font-medium">LPDD</th>
                      <th class="p-3 text-center font-medium">SMD</th>
                      <th class="p-3 text-center font-medium">RSCIG</th>
                      <th class="p-3 text-center font-medium">OARDTS-Task Force</th>
                      <th class="p-3 text-center font-medium">TOTAL | AVE</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="row in convertedMonthlyData"
                      :key="row.id"
                      class="border-b hover:bg-gray-50"
                    >
                      <td class="left-0 bg-white p-3 text-left hover:bg-gray-50 z-10">
                        {{ row.documentAction }}
                      </td>
                      <td class="p-3 text-center">{{ row.admin }}</td>
                      <td class="p-3 text-center">{{ row.finance }}</td>
                      <td class="p-3 text-center">{{ row.legal }}</td>
                      <td class="p-3 text-center">{{ row.pmd }}</td>
                      <td class="p-3 text-center">{{ row.cdd }}</td>
                      <td class="p-3 text-center">{{ row.ed }}</td>
                      <td class="p-3 text-center">{{ row.lpdd }}</td>
                      <td class="p-3 text-center">{{ row.smd }}</td>
                      <td class="p-3 text-center">{{ row.rscig }}</td>
                      <td class="p-3 text-center">
                        {{ row.oardtsTaskForce }}
                      </td>
                      <td class="p-3 text-center">{{ row.totalAve }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="activeTab === 'semester'">
        <div class="mb-6 rounded-lg bg-white shadow-sm">
          <div class="border-b p-6">
            <div class="text-center">
              <div class="mb-4 rounded-lg bg-gray-100 p-4">
                <h2 class="mb-2 text-xl font-bold">Document Monitoring System</h2>
                <p class="text-sm text-gray-600">Database Population</p>
                <p class="text-sm text-gray-600">Date Generated: {{ currentDate }}</p>
                <p class="text-sm text-gray-600">Generated by: Kenneth Sayan</p>
              </div>
              <h2 class="mb-2 text-xl font-bold">Summary of Document Action: 1st Semester</h2>
            </div>
          </div>
          <div class="p-6">
            <div class="lg:hidden">
              <div
                v-for="(row, rowIndex) in convertedFirstSemesterData"
                :key="rowIndex"
                class="mb-4 rounded-lg border border-gray-200 p-4 shadow-sm"
              >
                <div
                  v-for="(header, colIndex) in tableHeaders"
                  :key="header.key"
                  class="flex items-start justify-between py-2 border-b last:border-b-0"
                >
                  <div class="font-medium text-gray-600 w-1/2 pr-2">{{ header.label }}</div>
                  <div class="text-right text-gray-800 w-1/2">{{ row[header.key] }}</div>
                </div>
              </div>
            </div>

            <div class="hidden lg:block">
              <div
                class="no-scrollbar overflow-x-auto scrollbar scrollbar-track-gray-100 scrollbar-thumb-gray-400"
              >
                <table class="w-full border border-spacing-0 text-xs lg:text-sm">
                  <thead>
                    <tr class="border-b bg-gray-50">
                      <th class="left-0 bg-gray-50 p-3 text-left font-medium z-10">
                        Document Action Information
                      </th>
                      <th class="p-3 text-center font-medium">Admin</th>
                      <th class="p-3 text-center font-medium">Finance</th>
                      <th class="p-3 text-center font-medium">Legal</th>
                      <th class="p-3 text-center font-medium">PMD</th>
                      <th class="p-3 text-center font-medium">CDD</th>
                      <th class="p-3 text-center font-medium">ED</th>
                      <th class="p-3 text-center font-medium">LPDD</th>
                      <th class="p-3 text-center font-medium">SMD</th>
                      <th class="p-3 text-center font-medium">RSCIG</th>
                      <th class="p-3 text-center font-medium">OARDTS-Task Force</th>
                      <th class="p-3 text-center font-medium">TOTAL | AVE</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="row in convertedFirstSemesterData"
                      :key="row.id"
                      class="border-b hover:bg-gray-50"
                    >
                      <td class="left-0 bg-white p-3 text-left hover:bg-gray-50 z-10">
                        {{ row.documentAction }}
                      </td>
                      <td class="p-3 text-center">{{ row.admin }}</td>
                      <td class="p-3 text-center">{{ row.finance }}</td>
                      <td class="p-3 text-center">{{ row.legal }}</td>
                      <td class="p-3 text-center">{{ row.pmd }}</td>
                      <td class="p-3 text-center">{{ row.cdd }}</td>
                      <td class="p-3 text-center">{{ row.ed }}</td>
                      <td class="p-3 text-center">{{ row.lpdd }}</td>
                      <td class="p-3 text-center">{{ row.smd }}</td>
                      <td class="p-3 text-center">{{ row.rscig }}</td>
                      <td class="p-3 text-center">{{ row.oardtsTaskForce }}</td>
                      <td class="p-3 text-center">{{ row.totalAve }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-lg bg-white shadow-sm">
          <div class="border-b p-6">
            <div class="text-center">
              <h2 class="mb-2 text-xl font-bold">Summary of Document Action: 2nd Semester</h2>
            </div>
          </div>
          <div class="p-6">
            <div class="lg:hidden">
              <div
                v-for="(row, rowIndex) in convertedSecondSemesterData"
                :key="rowIndex"
                class="mb-4 rounded-lg border border-gray-200 p-4 shadow-sm"
              >
                <div
                  v-for="(header, colIndex) in tableHeaders"
                  :key="header.key"
                  class="flex items-start justify-between py-2 border-b last:border-b-0"
                >
                  <div class="font-medium text-gray-600 w-1/2 pr-2">{{ header.label }}</div>
                  <div class="text-right text-gray-800 w-1/2">{{ row[header.key] }}</div>
                </div>
              </div>
            </div>

            <div class="hidden lg:block">
              <div
                class="no-scrollbar overflow-x-auto scrollbar scrollbar-track-gray-100 scrollbar-thumb-gray-400"
              >
                <table class="w-full border border-spacing-0 text-xs lg:text-sm">
                  <thead>
                    <tr class="border-b bg-gray-50">
                      <th class="left-0 bg-gray-50 p-3 text-left font-medium z-10">
                        Document Action Information
                      </th>
                      <th class="p-3 text-center font-medium">Admin</th>
                      <th class="p-3 text-center font-medium">Finance</th>
                      <th class="p-3 text-center font-medium">Legal</th>
                      <th class="p-3 text-center font-medium">PMD</th>
                      <th class="p-3 text-center font-medium">CDD</th>
                      <th class="p-3 text-center font-medium">ED</th>
                      <th class="p-3 text-center font-medium">LPDD</th>
                      <th class="p-3 text-center font-medium">SMD</th>
                      <th class="p-3 text-center font-medium">RSCIG</th>
                      <th class="p-3 text-center font-medium">OARDTS-Task Force</th>
                      <th class="p-3 text-center font-medium">TOTAL | AVE</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="row in convertedSecondSemesterData"
                      :key="row.id"
                      class="border-b hover:bg-gray-50"
                    >
                      <td class="left-0 bg-white p-3 text-left hover:bg-gray-50 z-10">
                        {{ row.documentAction }}
                      </td>
                      <td class="p-3 text-center">{{ row.admin }}</td>
                      <td class="p-3 text-center">{{ row.finance }}</td>
                      <td class="p-3 text-center">{{ row.legal }}</td>
                      <td class="p-3 text-center">{{ row.pmd }}</td>
                      <td class="p-3 text-center">{{ row.cdd }}</td>
                      <td class="p-3 text-center">{{ row.ed }}</td>
                      <td class="p-3 text-center">{{ row.lpdd }}</td>
                      <td class="p-3 text-center">{{ row.smd }}</td>
                      <td class="p-3 text-center">{{ row.rscig }}</td>
                      <td class="p-3 text-center">{{ row.oardtsTaskForce }}</td>
                      <td class="p-3 text-center">{{ row.totalAve }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div
    class="pdf-container-hidden absolute -top-[9999px] left-0 w-[475mm] h-auto p-8 bg-white"
    ref="pdfContainerRef"
  >
    <div class="font-serif text-black">
      <div v-if="activeTab === 'semester'">
        <div class="mb-6 rounded-lg bg-white shadow-sm">
          <div class="border-b">
            <div class="text-center">
              <h2 class="mb-2 text-xl font-bold">Document Monitoring System</h2>
              <p class="text-sm text-gray-600">Database Population</p>
              <p class="text-sm text-gray-600">Date Generated: {{ currentDate }}</p>
              <p class="text-sm text-gray-600">Generated by: Kenneth Sayan</p>
              <h2 class="mb-2 text-xl font-bold">Summary of Document Action: 1st Semester</h2>
            </div>
          </div>
          <div>
            <table class="w-full border border-spacing-0 text-xs lg:text-sm">
              <thead>
                <tr class="border-b bg-gray-50">
                  <th class="p-3 text-left font-medium">Document Action Information</th>
                  <th class="p-3 text-center font-medium">Admin</th>
                  <th class="p-3 text-center font-medium">Finance</th>
                  <th class="p-3 text-center font-medium">Legal</th>
                  <th class="p-3 text-center font-medium">PMD</th>
                  <th class="p-3 text-center font-medium">CDD</th>
                  <th class="p-3 text-center font-medium">ED</th>
                  <th class="p-3 text-center font-medium">LPDD</th>
                  <th class="p-3 text-center font-medium">SMD</th>
                  <th class="p-3 text-center font-medium">RSCIG</th>
                  <th class="p-3 text-center font-medium">OARDTS-Task Force</th>
                  <th class="p-3 text-center font-medium">TOTAL | AVE</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in convertedFirstSemesterData"
                  :key="row.id"
                  class="border-b hover:bg-gray-50"
                >
                  <td class="p-3 text-left">{{ row.documentAction }}</td>
                  <td class="p-3 text-center">{{ row.admin }}</td>
                  <td class="p-3 text-center">{{ row.finance }}</td>
                  <td class="p-3 text-center">{{ row.legal }}</td>
                  <td class="p-3 text-center">{{ row.pmd }}</td>
                  <td class="p-3 text-center">{{ row.cdd }}</td>
                  <td class="p-3 text-center">{{ row.ed }}</td>
                  <td class="p-3 text-center">{{ row.lpdd }}</td>
                  <td class="p-3 text-center">{{ row.smd }}</td>
                  <td class="p-3 text-center">{{ row.rscig }}</td>
                  <td class="p-3 text-center">{{ row.oardtsTaskForce }}</td>
                  <td class="p-3 text-center">{{ row.totalAve }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="rounded-lg bg-white shadow-sm">
          <div class="border-b">
            <div class="text-center">
              <h2 class="mb-2 text-xl font-bold">Summary of Document Action: 2nd Semester</h2>
            </div>
          </div>
          <div>
            <table class="w-full border border-spacing-0 text-xs lg:text-sm">
              <thead>
                <tr class="border-b bg-gray-50">
                  <th class="p-3 text-left font-medium">Document Action Information</th>
                  <th class="p-3 text-center font-medium">Admin</th>
                  <th class="p-3 text-center font-medium">Finance</th>
                  <th class="p-3 text-center font-medium">Legal</th>
                  <th class="p-3 text-center font-medium">PMD</th>
                  <th class="p-3 text-center font-medium">CDD</th>
                  <th class="p-3 text-center font-medium">ED</th>
                  <th class="p-3 text-center font-medium">LPDD</th>
                  <th class="p-3 text-center font-medium">SMD</th>
                  <th class="p-3 text-center font-medium">RSCIG</th>
                  <th class="p-3 text-center font-medium">OARDTS-Task Force</th>
                  <th class="p-3 text-center font-medium">TOTAL | AVE</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in convertedSecondSemesterData"
                  :key="row.id"
                  class="border-b hover:bg-gray-50"
                >
                  <td class="p-3 text-left">{{ row.documentAction }}</td>
                  <td class="p-3 text-center">{{ row.admin }}</td>
                  <td class="p-3 text-center">{{ row.finance }}</td>
                  <td class="p-3 text-center">{{ row.legal }}</td>
                  <td class="p-3 text-center">{{ row.pmd }}</td>
                  <td class="p-3 text-center">{{ row.cdd }}</td>
                  <td class="p-3 text-center">{{ row.ed }}</td>
                  <td class="p-3 text-center">{{ row.lpdd }}</td>
                  <td class="p-3 text-center">{{ row.smd }}</td>
                  <td class="p-3 text-center">{{ row.rscig }}</td>
                  <td class="p-3 text-center">{{ row.oardtsTaskForce }}</td>
                  <td class="p-3 text-center">{{ row.totalAve }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
