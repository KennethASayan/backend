<script setup lang="ts">
import { ref } from 'vue'
import Dialog from '@/components/ui/Dialog/Dialog.vue'
import Button from '@/components/ui/button/Button.vue'
import DatePicker from '@/components/ui/DatePicker/DatePicker.vue'
import { useFinancialDocumentForm } from './index'
import { PrinterIcon } from '@heroicons/vue/24/outline'
import FinancialDocumentPdf from '@/components/document/FinancialDocumentPDF.vue'

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits(['close'])

// Create a ref for the new component.
const pdfComponentRef = ref<InstanceType<typeof FinancialDocumentPdf> | null>(null)

const {
  form,
  isLoading,
  submitForm,
  handleOriginalFileChange,
  handleFinalActionFileChange,
  formSubmitted,
  resetForm,
} = useFinancialDocumentForm(emit)

const uploadFileRef = ref<HTMLInputElement | null>(null)
const triggerFileUpload = () => {
  uploadFileRef.value?.click()
}

const uploadedFileName = ref('')
const handleFileChange = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (input.files?.length) {
    const file = input.files[0]
    uploadedFileName.value = file.name
    handleOriginalFileChange(file)
  }
}

const uploadFinalActionRef = ref<HTMLInputElement | null>(null)
const triggerFinalActionUpload = () => {
  uploadFinalActionRef.value?.click()
}

const uploadedFinalActionFileName = ref('')
const handleFinalActionFileChangeInternal = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (input.files?.length) {
    uploadedFinalActionFileName.value = input.files[0].name
    handleFinalActionFileChange(input.files[0])
  }
}

// Call the generatePdf function from the new component's ref.
const generatePdf = () => {
  pdfComponentRef.value?.generatePdf()
}

const showConfirmClose = ref(false)

const handleClose = () => {
  if (formSubmitted.value) {
    resetForm()
    emit('close')
  } else {
    showConfirmClose.value = true
  }
}

const confirmClose = () => {
  showConfirmClose.value = false
  resetForm()
  emit('close')
}

const cancelClose = () => {
  // User canceled, so just hide the modal and let them continue editing.
  showConfirmClose.value = false
}
</script>

<template>
  <Dialog
    :is-open="isOpen"
    type="drawer"
    title="Financial Documents"
    position="bottom"
    :close-on-overlay-click="false"
    @close="handleClose"
    class="!h-screen"
  >
    <template #title-actions>
      <div class="flex items-center space-x-2">
        <div v-if="!formSubmitted" class="flex items-center space-x-2">
          <button
            type="submit"
            form="financial-document-form"
            class="relative flex items-center justify-start p-2 rounded-full text-gray-500 hover:text-gray-700 group transition-all duration-300 ease-in-out"
            :disabled="isLoading"
          >
            <span
              class="absolute left-1/2 -translate-x-1/2 top-full mt-2 hidden group-hover:block px-2 py-1 bg-gray-700 text-white text-xs rounded-md shadow-md whitespace-nowrap z-50 pointer-events-none"
              >Save Financial Document</span
            >
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8">
              <path
                fill="currentColor"
                d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2m10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"
              />
            </svg>
          </button>
        </div>
        <div v-else class="flex items-center space-x-2">
          <Button
            type="button"
            variant="ghost"
            @click="generatePdf"
            class="w-auto flex items-center px-3 py-2 hover:bg-gray-200 text-gray-800 outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 text-sm sm:text-base"
          >
            <PrinterIcon class="w-4 h-4 sm:w-5 sm:h-5 mr-2" />
            <span class="hidden sm:inline">Click Here to Print</span>
            <span class="sm:hidden">Print</span>
          </Button>
        </div>
      </div>
    </template>

      <div class="p-3 sm:p-4 md:p-6 overflow-y-auto max-h-[85vh] sm:max-h-[82vh] flex-1 bg-white">
      <form
        @submit.prevent="submitForm"
        id="financial-document-form"
        class="space-y-3 sm:space-y-4 md:space-y-6"
      >
        <!-- Header Information Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2 sm:gap-3 md:gap-4 border-gray-300 pb-3 sm:pb-4 mb-3 sm:mb-4">
          <div class="min-w-0">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Creator:</label>
            <input
              type="text"
              v-model="form.creator"
              class="w-full border-b border-gray-400 focus:border-gray-600 outline-none py-1 text-xs sm:text-sm min-w-0"
            />
          </div>
          <div class="min-w-0">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Document No:</label>
            <input
              type="text"
              v-model="form.documentNo"
              class="w-full border-b border-gray-400 focus:border-gray-600 outline-none py-1 text-xs sm:text-sm min-w-0"
            />
          </div>
          <div class="min-w-0">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Date Created:</label>
            <DatePicker v-model="form.dateCreated" id="dateCreated" class="w-full" />
          </div>
          <div class="min-w-0">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Time Created:</label>
            <input
              type="time"
              v-model="form.timeCreated"
              class="w-full border-b border-gray-400 focus:border-gray-600 text-center outline-none py-1 text-xs sm:text-sm min-w-0"
            />
          </div>
        </div>

           <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-gray-300 pb-4">
          <div class="border border-gray-300 rounded p-4 space-y-3">
            <label class="block text-sm font-medium text-gray-700">Document Classification:</label>
            <div class="flex flex-col space-y-2">
              <label class="flex items-center">
                <input type="radio" name="docClass" value="confidential" v-model="form.documentClassification" class="mr-2 accent-red-500" />
                <span class="text-sm">Confidential</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="docClass" value="general" v-model="form.documentClassification" class="mr-2 accent-red-500" />
                <span class="text-sm">General Circulation</span>
              </label>
            </div>
          </div>

          <div class="border border-gray-300 rounded p-4 space-y-3">
            <label class="block text-sm font-medium text-gray-700">Level of Priority:</label>
            <div class="flex flex-col space-y-2">
              <label class="flex items-center">
                <input type="radio" name="priority" value="high" v-model="form.levelOfPriority" class="mr-2 accent-red-500" />
                <span class="text-sm">High</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="priority" value="normal" v-model="form.levelOfPriority" class="mr-2 accent-red-500" />
                <span class="text-sm">Normal</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="priority" value="low" v-model="form.levelOfPriority" class="mr-2 accent-red-500" />
                <span class="text-sm">Low</span>
              </label>
            </div>
          </div>

         <div class="border border-gray-300 rounded p-4 space-y-3 flex flex-col justify-between">
            <label class="block text-sm font-medium text-gray-700">ORIGINAL DOCUMENT:</label>
            <div class="flex-1 flex flex-col items-center justify-center space-y-2">
              <button
                type="button"
                @click="triggerFileUpload"
                class="bg-teal-500 text-white px-4 py-2 rounded text-sm hover:bg-teal-600 transition-colors w-full"
              >
                Upload File
              </button>
              <input ref="uploadFileRef" type="file" class="hidden" @change="handleFileChange" />
              <span class="text-xs text-gray-500 truncate text-center">{{
                uploadedFileName || 'No file selected'
              }}</span>
            </div>
          </div>
        </div>

        <!-- Subject Section -->
        <div class="border border-gray-400 p-3 sm:p-4 min-h-[120px] sm:min-h-[150px]">
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">SUBJECT:</label>
          <textarea
            v-model="form.subject"
            class="w-full h-full p-1 sm:p-2 text-xs sm:text-sm resize-none border-none outline-none"
            rows="8"
            placeholder="Enter subject details..."
          ></textarea>
        </div>

<div class="border border-gray-400 p-3 sm:p-4 space-y-4">
  <div class="grid grid-cols-1 lg:grid-cols-4 gap-2 sm:gap-4">
    <h3 class="text-xs sm:text-sm font-bold text-gray-900 text-center lg:col-span-2 order-1">
      ROUTE OF DOCUMENT FOR APPROVAL
    </h3>
    <h3 class="text-xs sm:text-sm font-bold text-gray-900 text-center lg:col-span-2 order-2">
      TIME RECEIVED / RELEASED
    </h3>
  </div>

  <div class="space-y-4">
    <label class="text-xs sm:text-sm font-medium font-bold text-gray-700 block">PROCUREMENT SERVICES:</label>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 items-end">
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1">DATE RECEIVED:</label>
      </div>
      <div class="min-w-0">
        <DatePicker v-model="form.procurementDateReceived" class="w-full text-xs sm:text-sm" />
      </div>
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1 text-center">TIME RECEIVED:</label>
      </div>
      <div class="min-w-0">
        <input
          type="time"
          v-model="form.procurementTimeReceived"
          class="w-full border-b border-gray-400 text-center focus:border-gray-600 outline-none py-1 text-xs sm:text-sm"
        />
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 items-end">
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1">DATE RELEASED:</label>
      </div>
      <div class="min-w-0">
        <DatePicker v-model="form.procurementDateReleased" class="w-full text-xs sm:text-sm" />
      </div>
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1 text-center">TIME RELEASED:</label>
      </div>
      <div class="min-w-0">
        <input
          type="time"
          v-model="form.procurementTimeReleased"
          class="w-full border-b border-gray-400 text-center focus:border-gray-600 outline-none py-1 text-xs sm:text-sm"
        />
      </div>
    </div>
  </div>

  <div class="space-y-4">
    <label class="text-xs sm:text-sm font-medium font-bold text-gray-700 block">FINANCE DIVISION:</label>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 items-end">
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1">DATE RECEIVED:</label>
      </div>
      <div class="min-w-0">
        <DatePicker v-model="form.financeDateReceived" class="w-full text-xs sm:text-sm" />
      </div>
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1 text-center">TIME RECEIVED:</label>
      </div>
      <div class="min-w-0">
        <input
          type="time"
          v-model="form.financeTimeReceived"
          class="w-full border-b border-gray-400 focus:border-gray-600 text-center outline-none py-1 text-xs sm:text-sm"
        />
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 items-end">
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1">DATE RELEASED:</label>
      </div>
      <div class="min-w-0">
        <DatePicker v-model="form.financeDateReleased" class="w-full text-xs sm:text-sm" />
      </div>
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1 text-center">TIME RELEASED:</label>
      </div>
      <div class="min-w-0">
        <input
          type="time"
          v-model="form.financeTimeReleased"
          class="w-full border-b border-gray-400 focus:border-gray-600 text-center outline-none py-1 text-xs sm:text-sm"
        />
      </div>
    </div>
  </div>

  <div class="space-y-4">
    <label class="text-xs sm:text-sm font-medium font-bold text-gray-700 block">CASHIERING UNIT:</label>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 items-end">
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1">DATE RECEIVED:</label>
      </div>
      <div class="min-w-0">
        <DatePicker v-model="form.cashieringDateReceived" class="w-full text-xs sm:text-sm" />
      </div>
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1 text-center">TIME RECEIVED:</label>
      </div>
      <div class="min-w-0">
        <input
          type="time"
          v-model="form.cashieringTimeReceived"
          class="w-full border-b border-gray-400 focus:border-gray-600 text-center outline-none py-1 text-xs sm:text-sm"
        />
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 items-end">
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1">DATE RELEASED:</label>
      </div>
      <div class="min-w-0">
        <DatePicker v-model="form.cashieringDateReleased" class="w-full text-xs sm:text-sm" />
      </div>
      <div class="min-w-0">
        <label class="text-xs sm:text-sm font-medium text-gray-700 block mb-1 text-center">TIME RELEASED:</label>
      </div>
      <div class="min-w-0">
        <input
          type="time"
          v-model="form.cashieringTimeReleased"
          class="w-full border-b border-gray-400 focus:border-gray-600 text-center outline-none py-1 text-xs sm:text-sm"
        />
      </div>
    </div>
  </div>

          <!-- Final Action Section -->
          <div class=" border-gray-300 pt-4 mt-4">
            <div class="flex flex-col lg:flex-row gap-4 lg:items-center">
              <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center flex-1 min-w-0">
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 whitespace-nowrap">FINAL ACTION</h3>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 min-w-0 flex-1">
                  <label class="text-xs sm:text-sm font-medium text-gray-700 whitespace-nowrap">DATE UPLOADED:</label>
                  <DatePicker
                    v-model="form.finalActionDateUploaded"
                    class="w-[36vh]  min-w-0 text-xs sm:text-sm"
                  />
                </div>
              </div>

              <div class="flex flex-col items-center space-y-2 min-w-0">
                <button
                  type="button"
                  @click="triggerFinalActionUpload"
                  class="bg-teal-500 text-white px-3 py-1.5 rounded text-xs hover:bg-teal-600 transition-colors whitespace-nowrap"
                >
                  Upload File
                </button>
                <input
                  ref="uploadFinalActionRef"
                  type="file"
                  class="hidden"
                  @change="handleFinalActionFileChangeInternal"
                />
                <span class="text-xs text-gray-500 text-center break-all max-w-full px-1">
                  {{ uploadedFinalActionFileName || 'No file selected' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <FinancialDocumentPdf
      :formData="form"
      :uploadedFileName="uploadedFileName"
      :uploadedFinalActionFileName="uploadedFinalActionFileName"
      ref="pdfComponentRef"
    />
  </Dialog>

  <Dialog
    :is-open="showConfirmClose"
    title="Confirm Close"
    description="Are you sure you want to close this form? Any unsaved changes will be lost."
    @close="cancelClose"
  >
    <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2 sm:gap-2 sm:space-x-0">
      <Button @click="cancelClose" variant="danger" class="w-full sm:w-auto">Cancel</Button>
      <Button @click="confirmClose" variant="success" class="w-full sm:w-auto">Close</Button>
    </div>
  </Dialog>
</template>
