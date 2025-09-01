<script setup lang="ts">
import { ref } from 'vue'
import html2canvas from 'html2canvas'
import { jsPDF } from 'jspdf'

// Define props to receive the data from the parent component
const props = defineProps<{
  formData: any
  uploadedFileName: string
  uploadedFinalActionFileName: string
}>()

const pdfContainerRef = ref<HTMLDivElement | null>(null)

// Format the date for display
const formatDisplayDate = (date: any): string => {
  if (!date) return ''
  if (!(date instanceof Date)) {
    date = new Date(date)
  }
  return isNaN(date.getTime()) ? '' : date.toLocaleDateString('en-US')
}

// The core function to generate the PDF
const generatePdf = async () => {
  if (!pdfContainerRef.value) return
  try {
    const canvas = await html2canvas(pdfContainerRef.value, { scale: 2 })
    const imgData = canvas.toDataURL('image/png')
    const pdf = new jsPDF('p', 'mm', 'a4')
    const imgWidth = 210
    const pageHeight = 297
    const imgHeight = (canvas.height * imgWidth) / canvas.width
    let heightLeft = imgHeight
    let position = 0
    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
    heightLeft -= pageHeight
    while (heightLeft > 0) {
      position = heightLeft - imgHeight
      pdf.addPage()
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
      heightLeft -= pageHeight
    }
    const pdfBlob = pdf.output('blob')
    const url = URL.createObjectURL(pdfBlob)
    window.open(url, '_blank')
  } catch (error) {
    console.error('Error generating PDF:', error)
  }
}

// Expose the generatePdf function to the parent component
defineExpose({
  generatePdf
})
</script>

<template>
  <div class="pdf-container-hidden absolute -top-[9999px] left-0" ref="pdfContainerRef">
    <div class="p-8 font-serif text-black">
      <div class="header-section mb-6 mt-20 grid grid-cols-[1fr_2fr] gap-4">
        <div class="flex items-center">
          <div class="logo-placeholder w-28 h-28 mr-4 ml-4 flex items-center justify-center">
            <img src="@/assets/DENRLOGO.png" alt="Logo" class="w-full h-full object-cover" />
          </div>
          <div class="header-text font-bold leading-tight">
            <h1 class="text-lg">DOCUMENT</h1>
            <h2 class="text-lg">MONITORING</h2>
            <h3 class="text-lg">SYSTEM v4</h3>
          </div>
        </div>
        <div class="grid grid-cols-2 text-sm">
          <div class="p-2">
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Creator:</span>
              <div class="border-b p-1 border-black flex-1 text-center uppercase">
                {{ formData.creator }}
              </div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Date Created:</span>
              <div class="border-b p-1 border-black flex-1 text-center">
                {{ formData.dateCreated ? formatDisplayDate(formData.dateCreated) : '' }}
              </div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Document Classification:</span>
              <div class="border-b p-1 border-black flex-1 text-center capitalize">
                {{ formData.documentClassification }}
              </div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Original Document:</span>
              <div class="border-b p-1 border-black flex-1 text-center">
                {{ uploadedFileName }}
              </div>
            </div>
          </div>
          <div class="p-2">
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Document No:</span>
              <div class="border-b p-1 border-black flex-1 text-center">
                {{ formData.documentNo }}
              </div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Time Created:</span>
              <div class="border-b p-1 border-black flex-1 text-center">
                {{ formData.timeCreated }}
              </div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Level of Priority:</span>
              <div class="border-b p-1 border-black flex-1 text-center capitalize">
                {{ formData.levelOfPriority }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="gap-4 mb-6">
        <div class="border border-black flex flex-col">
          <div class="p-2 border-b border-black">
            <span class="font-bold text-sm">SUBJECT:</span>
          </div>
          <div class="p-2 min-h-[150px] text-sm flex-1 uppercase">
            {{ formData.subject }}
          </div>
        </div>
      </div>

      <div class="p-3 space-y-4">
        <h3 class="text-sm font-bold text-gray-900 text-center">
          ROUTE OF DOCUMENT FOR APPROVAL
        </h3>
        <div class="grid grid-cols-2 gap-4">
          <div class="flex items-center space-x-2">
            <span class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">PROCUREMENT SERVICES:</span>
            <span class="w-full p-2 text-center text-xs border-b border-black">{{
              formData.procurementDateReceived ? formatDisplayDate(formData.procurementDateReceived) : ''
            }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">TIME RECEIVED / RELEASED:</span>
            <span class="w-full p-2 text-center text-xs border-b border-black">{{
              formData.procurementDateReleased
            }}</span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="flex items-center space-x-2">
            <span class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">FINANCE DIVISION:</span>
            <span class="w-full p-2 text-center text-xs border-b border-black">{{
              formData.financeDateReceived ? formatDisplayDate(formData.financeDateReceived) : ''
            }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">TIME RECEIVED / RELEASED:</span>
            <span class="w-full p-2 text-center text-xs border-b border-black">{{
              formData.financeDateReleased
            }}</span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="flex items-center space-x-2">
            <span class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">CASHIERING UNIT:</span>
            <span class="w-full p-2 text-center text-xs border-b border-black">{{
              formData.cashieringDateReceived ? formatDisplayDate(formData.cashieringDateReceived) : ''
            }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">TIME RECEIVED / RELEASED:</span>
            <span class="w-full p-2 text-center text-xs border-b border-black">{{
              formData.cashieringDateReleased
            }}</span>
          </div>
        </div>
      </div>

      <div class="p-3 space-y-4">
        <h3 class="text-xs sm:text-sm font-bold text-gray-900 w-1/2">FINAL ACTION:</h3>
        <div class="flex items-center space-x-4 justify-between">
          <span class="text-xs sm:text-sm font-medium text-gray-700">DATE UPLOADED:</span>
          <span class="w-[50vh] text-xs p-2 text-center border-b border-black">{{
            formData.finalActionDateUploaded ? formatDisplayDate(formData.finalActionDateUploaded) : ''
          }}</span>
        </div>
        <div class="flex items-center space-x-4 justify-between">
          <span class="text-xs sm:text-sm font-medium text-gray-700">DATE RELEASED:</span>
          <span class="w-[50vh] p-2 text-center text-xs border-b border-black">{{
            formData.dateReleased ? formatDisplayDate(formData.dateReleased) : ''
          }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
