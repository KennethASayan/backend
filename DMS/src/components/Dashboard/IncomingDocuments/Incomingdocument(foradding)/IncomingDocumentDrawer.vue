<script setup lang="ts">
import { ref, reactive, watch, computed } from 'vue'
import Dialog from '@/components/ui/Dialog/Dialog.vue'
import Button from '@/components/ui/button/Button.vue'
import DatePicker from '@/components/ui/DatePicker/DatePicker.vue'
import { PrinterIcon } from '@heroicons/vue/24/outline'
import IncomingDocumentPdf from '@/components/document/IncomingDocumentPDF.vue'

import type {
  FormData,
  ValidationState,
} from '@/components/Dashboard/IncomingDocuments/Incomingdocument(foradding)/index'

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits(['close'])

// Create a constant for the initial form state
const initialFormState = {
  receivingOffice: '',
  documentNo: '',
  dateReceived: null,
  timeReceived: '',
  documentDeadline: null,
  artaDeadline: '',
  documentClassification: '',
  subject: '',
  senderName: '',
  dateTimeReceivedByOredDate: null,
  dateTimeReceivedByOredTime: '',
  remarksFromOredHea: '',
  referredTo: [],
  dateTimeReleasedToBureaus: null,
  dateTimeReleasedToBureausTime: '',
  redInstructions: [],
  redInstructionsTimestamp: '',
  dateTimeReceivedByOard: null,
  dateTimeReceivedByOardTime: '',
  dateTimeReceivedByFinalAction: null,
  dateTimeReceivedByFinalActionTime: '',
  finalActionOffice: [],
  instructionsForFinalAction: '',
  instructionType: '',
  ardInstructions: '',
  routeForApproval: {
    dateTimeReceivedByOard: null,
    timeReceivedByOard: '',
    dateTimeReceivedByOred: null,
    timeReceivedByOred: '',
    dateTimeReleasedToActionOffice: null,
    timeReleasedToActionOffice: '',
  },
  dateReturnedToActionOfficeForRevision: null,
  dateReceivedByOardAfterRevision: null,
  dateReturnedToActionOfficeForRevision2: null,
  dateReceivedByOredAfterRevision: null,
  dateTimeDocumentRerouted: null,
  dateTimeDocumentReroutedTime: '',
  dateTimeReceivedByReroutedOffice: null, // This is correct
  dateTimeReceivedByReroutedOfficeTime: '', // This is correct
  reRoutedTo: [],
  noComplianceRequired: false,
  uploadingFinalAction: {
    dateReleased: null,
    mode: [],
  },
}

const form = ref<FormData>({ ...initialFormState })
const validation = reactive<ValidationState>({
  receivingOffice: '',
  documentNo: '',
  dateReceived: '',
  timeReceived: '',
  documentDeadline: '',
  documentClassification: '',
  subject: '',
  senderName: '',
  referredTo: '',
  finalActionOffice: '',
  instructionsForFinalAction: '',
  instructionType: '',
})

const isLoading = ref(false)
const formSubmitted = ref(false)
const showConfirmClose = ref(false)
const formRef = ref<HTMLFormElement | null>(null)

// Create a ref to the new PDF component
const pdfComponentRef = ref<InstanceType<typeof IncomingDocumentPdf> | null>(null)

// --- Utility Functions ---
// Move these to a separate utility file if they are used elsewhere
const addBusinessDays = (date: Date, days: number): Date => {
  const result = new Date(date)
  let addedDays = 0
  while (addedDays < days) {
    result.setDate(result.getDate() + 1)
    if (result.getDay() !== 0 && result.getDay() !== 6) {
      addedDays++
    }
  }
  return result
}
const formatMonthDayYear = (date: any): string => {
  if (!date) return ''
  if (!(date instanceof Date)) {
    date = new Date(date)
  }
  if (isNaN(date.getTime())) {
    return ''
  }
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric' }
  return date.toLocaleDateString('en-US', options)
}
// --- End Utility Functions ---

const resetForm = () => {
  form.value = { ...initialFormState }
  Object.keys(validation).forEach((key) => {
    validation[key as keyof ValidationState] = ''
  })
  formSubmitted.value = false
}

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
  emit('close')
  resetForm()
}

const cancelClose = () => {
  showConfirmClose.value = false
}

const artaDeadline = computed(() => {
  if (form.value.documentDeadline) {
    return 'Not Applicable'
  }
  if (form.value.dateReceived) {
    try {
      const receivedDate = new Date(form.value.dateReceived)
      const artaDate = addBusinessDays(receivedDate, 5)
      return formatMonthDayYear(artaDate)
    } catch (error) {
      console.error('Error calculating ARTA deadline:', error)
    }
  }
  return ''
})

watch(
  () => artaDeadline.value,
  (newValue) => {
    form.value.artaDeadline = newValue
  },
  { immediate: true },
)

const validateForm = () => {
  let isValid = true
  Object.keys(validation).forEach((key) => (validation[key as keyof ValidationState] = ''))
  if (!form.value.receivingOffice) {
    validation.receivingOffice = 'Receiving office is required.'
    isValid = false
  }
  if (!form.value.documentNo) {
    validation.documentNo = 'Document No. is required.'
    isValid = false
  }
  if (!form.value.subject) {
    validation.subject = 'Subject is required.'
    isValid = false
  }
  if (!form.value.senderName) {
    validation.senderName = 'Sender name is required.'
    isValid = false
  }
  return isValid
}

const submitForm = async () => {
  if (!validateForm()) {
    console.error('Form validation failed.')
    return
  }
  isLoading.value = true
  try {
    console.log('Form submitted successfully:', form.value)
    formSubmitted.value = true
  } catch (error) {
    console.error('Form submission failed:', error)
  } finally {
    isLoading.value = false
  }
}

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
    console.log('File selected:', uploadedFileName.value)
  }
}

const uploadedFinalActionFileName = ref('')
const uploadFinalActionRef = ref<HTMLInputElement | null>(null)
const triggerFinalActionUpload = () => {
  uploadFinalActionRef.value?.click()
}

// The `field` parameter is already of type `keyof FormData`.
const addDateTimeStamp = (event: FocusEvent) => {
  const textarea = event.target as HTMLTextAreaElement
  const fieldName = textarea.getAttribute('data-field')

  if (!fieldName || !(fieldName in form.value)) {
    console.warn(`Field ${fieldName} not found in form`)
    return
  }

  const field = fieldName as keyof FormData;

  const currentValue = form.value[field];

  if (typeof currentValue === 'string' && !currentValue.includes('Date and Time:')) {
    const now = new Date()
    const formattedDateTime = `Date and Time: ${now.getMonth() + 1}/${now.getDate()}/${now.getFullYear()} @ ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}\n`

    form.value[field] = (formattedDateTime + currentValue) as never;
  }
}

const generatePdf = async () => {
  isLoading.value = true
  try {
    await pdfComponentRef.value?.generatePdf()
  } catch (error) {
    console.error('Error generating PDF:', error)
  } finally {
    isLoading.value = false
  }
}

watch(
  form.value,
  () => {
    Object.keys(validation).forEach((key) => {
      if (validation[key as keyof ValidationState] && form.value[key as keyof FormData]) {
        validation[key as keyof ValidationState] = ''
      }
    })
  },
  { deep: true },
)

watch(() => form.value.noComplianceRequired, (newValue) => {
  if (newValue) {
    form.value.instructionType = ''
  }
})

watch(() => form.value.instructionType, (newValue) => {
  if (newValue) {
    form.value.noComplianceRequired = false
  }
})

// Optional: You can keep these lists here or move them to a separate constants file if used elsewhere.
const redInstructionsList = [
  'For Dissemination',
  'For Compliance',
  'For Discussion',
  'For Appropriate Action',
  'For Record/File/Reference',
  'For Review/Evaluation/Recommendation',
]

const finalActionOffices = [
  'Admin Division',
  'CDD',
  'Finance Division',
  'ED',
  'Legal Division',
  'LPDD',
  'PMD',
  'SMD',
  'RSCIG',
  'OARDTS-Task Force',
]

const reRoutedOffices = [
  'ADMIN DIVISION',
  'RSCIG',
  'SMD',
  'FINANCE DIVISION',
  'CDD',
  'OARDTS-Task Force',
  'LEGAL DIVISION',
  'ED',
  'PMD',
  'LPDD',
]
</script>
<template>
  <Dialog
    :is-open="isOpen"
    type="drawer"
    title="Incoming Documents"
    position="bottom"
    :close-on-overlay-click="false"
    @close="handleClose"
    class="!h-screen"
  >
    <template #title-actions>
      <div v-if="!formSubmitted" class="flex items-center space-x-2">
        <button
          type="submit"
          form="document-form"
          class="relative flex items-center justify-start p-2 rounded-full text-gray-500 hover: group transition-all duration-300 ease-in-out"
          :disabled="isLoading"
        >
          <span
            class="absolute left-1/2 -translate-x-1/3 top-8 left-full mt-2 hidden group-hover:block px-2 py-1 bg-gray-700 text-white text-xs rounded-md shadow-md whitespace-nowrap z-50"
          >Save Incoming Documents</span
          >
          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
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
          class="w-full sm:w-auto sm:order-2 hover:bg-gray-200 text-gray-800 outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
        >
          <PrinterIcon class="w-5 h-5 mr-2 " />
          <span>Click Here to Print</span>
        </Button>
      </div>
    </template>
    <div class="p-3 sm:p-4 md:p-6 overflow-y-auto max-h-[85vh] sm:max-h-[82vh] flex-1 bg-white">
      <form
        @submit.prevent="submitForm"
        id="document-form"
        ref="formRef"
        class="space-y-4 md:space-y-6"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700"
            >Receiving Office:</label
            >
            <input
              type="text"
              v-model="form.receivingOffice"
              class="w-full border-b outline-none py-1 text-xs sm:text-sm"
              :class="{
                'border-red-500': validation.receivingOffice,
                'border-gray-400': !validation.receivingOffice,
              }"
            />
            <p v-if="validation.receivingOffice" class="text-red-500 text-xs mt-1">
              {{ validation.receivingOffice }}
            </p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Document No:</label>
            <input
              type="text"
              v-model="form.documentNo"
              class="w-full border-b outline-none py-1 text-xs sm:text-sm"
              :class="{
                'border-red-500': validation.documentNo,
                'border-gray-400': !validation.documentNo,
              }"
            />
            <p v-if="validation.documentNo" class="text-red-500 text-xs mt-1">
              {{ validation.documentNo }}
            </p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Date Received:</label>
            <DatePicker v-model="form.dateReceived" id="dateReceived" />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Time Received:</label>
            <input
              type="time"
              v-model="form.timeReceived"
              class="w-full border h-[41px] rounded focus:border-gray-600 text-center outline-none px-3 py-1 text-xs sm:text-sm"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700"
            >Document Deadline:</label
            >
            <DatePicker v-model="form.documentDeadline" id="documentDeadline" />
          </div>

          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">ARTA Deadline:</label>
            <input
              type="text"
              :value="artaDeadline"
              readonly
              class="w-full border-b border-gray-400 bg-gray-50 outline-none py-1 text-xs sm:text-sm cursor-not-allowed text-center"
              :class="{
                'text-red-600 font-medium': artaDeadline === 'Not Applicable',
                'text-gray-600 font-medium': artaDeadline && artaDeadline !== 'Not Applicable',
              }"
            />
          </div>

          <div class="sm:col-span-2 lg:col-span-1 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="border border-gray-300 rounded p-2">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2"
                >Document Classification:</label
                >
                <div class="flex flex-col space-y-2">
                  <label class="flex items-center">
                    <input
                      type="radio"
                      name="docClass"
                      value="confidential"
                      v-model="form.documentClassification"
                      class="mr-2 accent-red-500"
                    />
                    <span class="text-xs sm:text-sm">Confidential</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="radio"
                      name="docClass"
                      value="general"
                      v-model="form.documentClassification"
                      class="mr-2 accent-red-500"
                    />
                    <span class="text-xs sm:text-sm">General Circulation</span>
                  </label>
                </div>
              </div>
              <div class="border border-gray-300 rounded p-2">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2"
                >ORIGINAL DOCUMENT:</label
                >
                <div class="flex flex-col space-y-2">
                  <button
                    type="button"
                    @click="triggerFileUpload"
                    class="bg-teal-500 text-white px-3 py-1.5 rounded text-xs hover:bg-teal-600 transition-colors w-full"
                  >
                    Upload File
                  </button>
                  <span class="text-xs text-gray-500 truncate text-center">
                    {{ uploadedFileName || 'No file selected' }}
                  </span>
                  <input
                    ref="uploadFileRef"
                    type="file"
                    class="hidden"
                    @change="handleFileChange"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="order-2 lg:order-1">
            <div class="relative border border-gray-400 p-3 min-h-[180px] sm:min-h-[270px]">
              <label class="absolute top-2 left-2 text-xs sm:text-sm font-medium text-gray-700"
              >SUBJECT:</label
              >
              <textarea
                v-model="form.subject"
                class="w-full h-full p-1 focus-within:ring-1 mt-6 sm:mt-7 text-xs sm:text-sm resize-none"
                :class="{
                  'border-red-500': validation.subject,
                  'border-gray-400': !validation.subject,
                }"
                rows="10"
              ></textarea>
              <button
                type="button"
                class="bg-red-500 text-white px-2 sm:px-3 py-1 rounded text-xs absolute top-2 right-2 hover:bg-red-600 transition-colors"
              >
                VERIFY
              </button>
              <p v-if="validation.subject" class="text-red-500 text-xs mt-1">
                {{ validation.subject }}
              </p>
            </div>
          </div>

          <div class="order-1 lg:order-2 flex flex-col space-y-3">
            <div
              class="relative border p-3 min-h-[80px] flex-1"
              :class="{
                'border-red-500': validation.senderName,
                'border-gray-400': !validation.senderName,
              }"
            >
              <label class="absolute top-2 left-2 text-xs sm:text-sm font-medium text-gray-700">
                NAME OF SENDER AND ADDRESS/OFFICE:
              </label>
              <textarea
                v-model="form.senderName"
                class="w-full p-1 focus-within:ring-1 resize-none text-xs sm:text-sm mt-6 sm:mt-7"
                rows="3"
              ></textarea>
              <p v-if="validation.senderName" class="text-red-500 text-xs mt-1">
                {{ validation.senderName }}
              </p>
            </div>

            <div class="border border-gray-400 p-3">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                DATE AND TIME RECEIVED BY ORED/HEA:
              </label>
              <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2">
                <DatePicker
                  v-model="form.dateTimeReceivedByOredDate"
                  id="dateTimeReceivedByOredDate"
                  class="flex-1"
                />
                <input
                  type="time"
                  v-model="form.dateTimeReceivedByOredTime"
                  class="flex-1 border border-gray-400 h-[41px] rounded focus:border-gray-600 text-center outline-none px-3 py-1 text-xs sm:text-sm"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div class="order-1">
            <div class="border border-gray-400 p-3 min-h-[270px] sm:min-h-[320px]">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                REMARKS FROM THE ORED/HEA:
              </label>
              <textarea
                v-model="form.remarksFromOredHea"
                class="w-full h-full p-1 focus-within:ring-1 focus-within:ring-teal-500 resize-none text-xs sm:text-sm"
                rows="10"
                data-field="remarksFromOredHea"
                @focus="addDateTimeStamp"
              ></textarea>
            </div>
          </div>

          <div class="order-2 space-y-3">
            <div class="border border-gray-400 p-3 min-h-[140px]">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2"
              >REFERRED TO:</label
              >
              <div class="space-y-2">
                <label class="flex items-center">
                  <input
                    type="checkbox"
                    v-model="form.referredTo"
                    value="ARD MS"
                    class="mr-2 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">ARD MS</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="checkbox"
                    v-model="form.referredTo"
                    value="ARD TS"
                    class="mr-2 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">ARD TS</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="checkbox"
                    v-model="form.referredTo"
                    value="MGB"
                    class="mr-2 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">MGB</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="checkbox"
                    v-model="form.referredTo"
                    value="EMB"
                    class="mr-2 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">EMB</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="checkbox"
                    v-model="form.referredTo"
                    value="RSCIG"
                    class="mr-2 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">RSCIG</span>
                </label>
              </div>
            </div>

            <div class="border border-gray-400 p-10">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                DATE AND TIME RELEASED TO BUREAUS:
              </label>
              <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2">
                <DatePicker
                  v-model="form.dateTimeReleasedToBureaus"
                  id="dateTimeReleasedToBureaus"
                  class="flex-1"
                />
                <input
                  type="time"
                  v-model="form.dateTimeReleasedToBureausTime"
                  class="flex-1 border border-gray-400 h-[41px] rounded focus:border-gray-600 text-center outline-none px-3 py-1 text-xs sm:text-sm"
                />
              </div>
            </div>
          </div>

          <div class="order-3">
            <div class="border border-gray-400 p-3">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2"
              >RED's INSTRUCTIONS:</label
              >
              <div class="space-y-2 mb-4">
                <label class="flex items-start">
                  <input
                    type="checkbox"
                    v-model="form.redInstructions"
                    value="For Dissemination"
                    class="mr-2 mt-0.5 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">For Dissemination</span>
                </label>
                <label class="flex items-start">
                  <input
                    type="checkbox"
                    v-model="form.redInstructions"
                    value="For Compliance"
                    class="mr-2 mt-0.5 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">For Compliance</span>
                </label>
                <label class="flex items-start">
                  <input
                    type="checkbox"
                    v-model="form.redInstructions"
                    value="For Discussion"
                    class="mr-2 mt-0.5 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">For Discussion</span>
                </label>
                <label class="flex items-start">
                  <input
                    type="checkbox"
                    v-model="form.redInstructions"
                    value="For Appropriate Action"
                    class="mr-2 mt-0.5 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">For Appropriate Action</span>
                </label>
                <label class="flex items-start">
                  <input
                    type="checkbox"
                    v-model="form.redInstructions"
                    value="For Record/File/Reference"
                    class="mr-2 mt-0.5 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">For Record/File/Reference</span>
                </label>
                <label class="flex items-start">
                  <input
                    type="checkbox"
                    v-model="form.redInstructions"
                    value="For Review/Evaluation/Recommendation"
                    class="mr-2 mt-0.5 flex-shrink-0"
                  />
                  <span class="text-xs sm:text-sm">For Review/Evaluation/Recommendation</span>
                </label>
              </div>
              <textarea
                v-model="form.redInstructionsTimestamp"
                placeholder="Input box for RED's instructions"
                class="w-full border border-gray-400 p-2 text-xs sm:text-sm focus-within:ring-1 focus-within:ring-teal-500 resize-none min-h-[80px]"
                data-field="redInstructionsTimestamp"
                @focus="addDateTimeStamp"
              ></textarea>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="border border-gray-400 p-3">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
              DATE AND TIME RECEIVED BY OARD
            </label>
            <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2">
              <DatePicker
                v-model="form.dateTimeReceivedByOard"
                id="dateTimeReceivedByOard"
                class="flex-1"
              />
              <input
                type="time"
                v-model="form.dateTimeReceivedByOardTime"
                class="flex-1 border border-gray-400 h-[41px] rounded focus:border-gray-600 text-center outline-none px-3 py-1 text-xs sm:text-sm"
              />
            </div>
          </div>
          <div class="border border-gray-400 p-3">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
              DATE AND TIME RECEIVED BY FINAL ACTION OFFICE:
            </label>

            <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2">
              <DatePicker
                v-model="form.dateTimeReceivedByFinalAction"
                id="dateTimeReceivedByFinalAction"
                class="flex-1"
              />
              <input
                type="time"
                v-model="form.dateTimeReceivedByFinalActionTime"
                class="flex-1 border border-gray-400 h-[41px] rounded focus:border-gray-600 text-center outline-none px-3 py-1 text-xs sm:text-sm"
              />
            </div>
            <input
              type="checkbox"
              v-model="form.noComplianceRequired"
              value="No Compliance Required"
              class="mt-3 flex-1 flex-shrink-0"
              :disabled="!!form.instructionType"
            />
            <label class="text-xs sm:text-sm font-medium text-gray-700">
              No Compliance Required:
            </label>
          </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="border border-gray-400 p-3">
            <div class="p-2 sm:p-4">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-3"
              >FINAL ACTION OFFICE:</label
              >
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div class="space-y-2">
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.finalActionOffice"
                      value="ADMIN DIVISION"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs">ADMIN DIVISION</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.finalActionOffice"
                      value="FINANCE DIVISION"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs">FINANCE DIVISION</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.finalActionOffice"
                      value="LEGAL DIVISION"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs">LEGAL DIVISION</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.finalActionOffice"
                      value="PMD"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs">PMD</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.finalActionOffice"
                      value="RSCIG"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs">RSCIG</span>
                  </label>
                </div>
                <div class="space-y-2">
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.finalActionOffice"
                      value="CDD"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs">CDD</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.finalActionOffice"
                      value="ED"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs">ED</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.finalActionOffice"
                      value="LPDD"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs">LPDD</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.finalActionOffice"
                      value="SMD"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs">SMD</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.finalActionOffice"
                      value="OARDTS-Task Force"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs">OARDTS-Task Force</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
          <div class="space-y-3">
            <div class="border border-gray-400 p-3 min-h-[160px]">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                INSTRUCTIONS FOR FINAL ACTION:
              </label>
              <textarea
                v-model="form.instructionsForFinalAction"
                class="w-full h-full p-1 focus-within:ring-1 resize-none text-xs sm:text-sm"
                rows="5"
                data-field="instructionsForFinalAction"
                @focus="addDateTimeStamp"
              ></textarea>
            </div>

            <div class="border border-gray-400 p-3">
              <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-2 text-xs"
              >
                <label class="flex items-center">
                  <input
                    type="radio"
                    name="instructionType"
                    value="Simple"
                    v-model="form.instructionType"
                    class="mr-1 accent-red-500 flex-shrink-0"
                    :disabled="form.noComplianceRequired"
                  />
                  <span class="truncate">Simple</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="radio"
                    name="instructionType"
                    value="Complex"
                    v-model="form.instructionType"
                    class="mr-1 accent-red-500 flex-shrink-0"
                    :disabled="form.noComplianceRequired"
                  />
                  <span class="truncate">Complex</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="radio"
                    name="instructionType"
                    value="Highly Technical"
                    v-model="form.instructionType"
                    class="mr-1 accent-red-500 flex-shrink-0"
                    :disabled="form.noComplianceRequired"
                  />
                  <span class="truncate">Highly Technical</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="radio"
                    name="instructionType"
                    value="Hotline"
                    v-model="form.instructionType"
                    class="mr-1 accent-red-500 flex-shrink-0"
                    :disabled="form.noComplianceRequired"
                  />
                  <span class="truncate">Hotline</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="radio"
                    name="instructionType"
                    value="Legal Concern"
                    v-model="form.instructionType"
                    class="mr-1 accent-red-500 flex-shrink-0"
                    :disabled="form.noComplianceRequired"
                  />
                  <span class="truncate">Legal Concern</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="space-y-4">
            <div class="border border-gray-400 p-3 min-h-[120px]">
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2"
              >ARD's INSTRUCTIONS:</label
              >
              <textarea
                v-model="form.ardInstructions"
                class="w-full h-full p-1 resize-none text-xs sm:text-sm"
                rows="5"
                data-field="ardInstructions"
              ></textarea>
            </div>

            <div class="border border-gray-400 p-[18px]">
              <div class="block text-xs sm:text-sm font-medium text-black-700 mb-2">
                ROUTE OF DOCUMENT FOR APPROVAL:
              </div>

              <div class="grid grid-cols-12 gap-2 mb-3">
                <div class="col-span-4 text-sm">DATE & TIME RECEIVED BY OARD:</div>
                <div class="col-span-4">
                  <div class="h-8">
                    <DatePicker
                      v-model="form.routeForApproval.dateTimeReceivedByOard"
                      class="w-full h-full border-0 outline-none bg-transparent"
                    />
                  </div>
                </div>
                <div class="col-span-4">
                  <div class="h-6 items-center justify-center">
                    <input
                      type="time"
                      v-model="form.routeForApproval.timeReceivedByOard"
                      class="w-full border border-gray-400 h-[41px] rounded focus:border-gray-600 text-center outline-none px-3 py-2 text-xs sm:text-sm"
                    />
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-2">
                <div class="col-span-4 text-sm italic pl-4">
                  DATE RETURNED TO ACTION OFFICE FOR REVISION
                </div>
                <div class="col-span-8">
                  <div class="h-8">
                    <DatePicker
                      v-model="form.dateReturnedToActionOfficeForRevision"
                      class="w-full h-full border-0 outline-none bg-transparent"
                    />
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-2 mb-4">
                <div class="col-span-4 text-sm italic pl-4">
                  DATE RECEIVED BY OARD AFTER REVISION
                </div>
                <div class="col-span-8">
                  <div class="h-8">
                    <DatePicker
                      v-model="form.dateReceivedByOardAfterRevision"
                      class="w-full h-full border-0 outline-none bg-transparent"
                    />
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-2 mb-3">
                <div class="col-span-4 text-sm">DATE & TIME RECEIVED BY ORED:</div>
                <div class="col-span-4">
                  <div class="h-8">
                    <DatePicker
                      v-model="form.routeForApproval.dateTimeReceivedByOred"
                      class="w-full h-full border-0 outline-none bg-transparent"
                    />
                  </div>
                </div>
                <div class="col-span-4">
                  <div class="h-6 items-center justify-center">
                    <input
                      type="time"
                      v-model="form.routeForApproval.timeReceivedByOred"
                      class="w-full border border-gray-400 h-[41px] rounded focus:border-gray-600 text-center outline-none px-3 py-2 text-xs sm:text-sm"
                    />
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-2">
                <div class="col-span-4 text-sm italic pl-4">
                  DATE RETURNED TO ACTION OFFICE FOR REVISION
                </div>
                <div class="col-span-8">
                  <div class="h-8">
                    <DatePicker
                      v-model="form.dateReturnedToActionOfficeForRevision2"
                      class="w-full h-full border-0 outline-none bg-transparent"
                    />
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-2 mb-4">
                <div class="col-span-4 text-sm italic pl-4">
                  DATE RECEIVED BY ORED AFTER REVISION
                </div>
                <div class="col-span-8">
                  <div class="h-8">
                    <DatePicker
                      v-model="form.dateReceivedByOredAfterRevision"
                      class="w-full h-full border-0 outline-none bg-transparent"
                    />
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-2">
                <div class="col-span-4 text-sm">
                  DATE & TIME RELEASED TO ACTION OFFICE (APPROVED):
                </div>
                <div class="col-span-4">
                  <div class="h-8">
                    <DatePicker
                      v-model="form.routeForApproval.dateTimeReleasedToActionOffice"
                      class="w-full h-full border-0 outline-none bg-transparent"
                    />
                  </div>
                </div>
                <div class="col-span-4">
                  <div class="h-6 items-center justify-center">
                    <input
                      type="time"
                      v-model="form.routeForApproval.timeReleasedToActionOffice"
                      class="w-full border border-gray-400 h-[41px] rounded focus:border-gray-600 text-center outline-none px-3 py-2 text-xs sm:text-sm"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="space-y-5">
            <div class="border border-gray-400 p-3">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700"
                >DATE AND TIME DOCUMENT RE-ROUTED TO:</label
                >
                <div class="flex space-x-4">
                  <DatePicker
                    v-model="form.dateTimeDocumentRerouted"
                    id="dateTimeDocumentRerouted"
                    class="flex-1"
                  />
                  <input
                    type="time"
                    v-model="form.dateTimeDocumentReroutedTime"
                    class="flex-1 border border-gray-400 h-[41px] rounded px-3 focus:border-gray-600 text-center outline-none py-1 text-sm mb-4 h-9"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="space-y-2">
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.reRoutedTo"
                      value="ADMIN DIVISION"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs sm:text-sm">ADMIN DIVISION</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.reRoutedTo"
                      value="FINANCE DIVISION"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs sm:text-sm">FINANCE DIVISION</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.reRoutedTo"
                      value="LEGAL DIVISION"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs sm:text-sm">LEGAL DIVISION</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.reRoutedTo"
                      value="PMD"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs sm:text-sm">PMD</span>
                  </label>
                </div>
                <div class="space-y-2">
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.reRoutedTo"
                      value="RSCIG"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs sm:text-sm">RSCIG</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.reRoutedTo"
                      value="CDD"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs sm:text-sm">CDD</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.reRoutedTo"
                      value="ED"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs sm:text-sm">ED</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.reRoutedTo"
                      value="LPDD"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs sm:text-sm">LPDD</span>
                  </label>
                </div>
                <div class="space-y-2">
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.reRoutedTo"
                      value="SMD"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs sm:text-sm">SMD</span>
                  </label>
                  <label class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.reRoutedTo"
                      value="OARDTS-Task Force"
                      class="mr-2 mt-0.5 flex-shrink-0"
                    />
                    <span class="text-xs sm:text-sm">OARDTS-Task Force</span>
                  </label>
                </div>
              </div>
            </div>

           <div class="border border-gray-400 p-3">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
              DATE AND TIME RECEIVED BY RE-ROUTED OFFICE:
            </label>
            <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2">
              <DatePicker
                v-model="form.dateTimeReceivedByReroutedOffice"
                id="dateTimeReceivedByReroutedOffice"
                class="flex-1"
              />
              <input
                type="time"
                v-model="form.dateTimeReceivedByReroutedOfficeTime"
                class="flex-1 border border-gray-400 h-[41px] rounded px-3 focus:border-gray-600 text-center outline-none py-1 text-xs sm:text-sm"
              />
            </div>
          </div>

            <div class="border border-gray-400 p-3">
              <div class="space-y-3">
                <label class="block text-xs sm:text-sm font-medium text-gray-700">
                  UPLOADING OF FINAL ACTION:
                </label>
                <div>
                  <button
                    type="button"
                    @click="triggerFinalActionUpload"
                    class="bg-teal-500 text-white px-3 py-2 rounded text-xs hover:bg-teal-600 transition-colors w-full sm:w-auto"
                  >
                    Upload File
                  </button>
                  <span class="text-xs text-gray-500 truncate">
                    {{ uploadedFinalActionFileName || 'No file selected' }}
                  </span>
                  <input
                    ref="uploadFinalActionRef"
                    type="file"
                    class="hidden"
                    @change="handleFileChange"
                  />
                </div>

                <div
                  class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2 sm:items-center"
                >
                  <label for="dateReleased" class="text-xs sm:text-sm font-medium"
                  >DATE RELEASED:</label
                  >
                  <DatePicker
                    v-model="form.uploadingFinalAction.dateReleased"
                    id="dateReleased"
                    class="flex-1 sm:w-auto"
                  />
                </div>

                <div>
                  <label class="block text-xs mb-2">MODE:</label>
                  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    <label class="flex items-center">
                      <input
                        type="checkbox"
                        v-model="form.uploadingFinalAction.mode"
                        value="Email"
                        class="mr-2 flex-shrink-0"
                      />
                      <span class="text-xs sm:text-sm">Email</span>
                    </label>
                    <label class="flex items-center">
                      <input
                        type="checkbox"
                        v-model="form.uploadingFinalAction.mode"
                        value="Hand-Carry"
                        class="mr-2 flex-shrink-0"
                      />
                      <span class="text-xs sm:text-sm">Hand-Carry</span>
                    </label>
                    <label class="flex items-center">
                      <input
                        type="checkbox"
                        v-model="form.uploadingFinalAction.mode"
                        value="Postal Services"
                        class="mr-2 flex-shrink-0"
                      />
                      <span class="text-xs sm:text-sm">Postal Services</span>
                    </label>
                    <label class="flex items-center">
                      <input
                        type="checkbox"
                        v-model="form.uploadingFinalAction.mode"
                        value="Courier"
                        class="mr-2 flex-shrink-0"
                      />
                      <span class="text-xs sm:text-sm">Courier</span>
                    </label>
                    <label class="flex items-center">
                      <input
                        type="checkbox"
                        v-model="form.uploadingFinalAction.mode"
                        value="Fax"
                        class="mr-2 flex-shrink-0"
                      />
                      <span class="text-xs sm:text-sm">Fax</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <IncomingDocumentPdf
      ref="pdfComponentRef"
      :form-data="form"
      :uploaded-file-name="uploadedFileName"
    />
  </Dialog>
  <Dialog
    :is-open="showConfirmClose"
    title="Confirm Close"
    description="Are you sure you want to close this form? Any unsaved changes will be lost."
    @close="cancelClose"
  >
    <div class="mt-4 flex justify-end space-x-2">
      <Button @click="cancelClose" variant="danger">Cancel</Button>
      <Button @click="confirmClose" variant="success">Close</Button>
    </div>
  </Dialog>
</template>
