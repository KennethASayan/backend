<script setup lang="ts">
import { ref } from 'vue'
import Dialog from '@/components/ui/Dialog/Dialog.vue'
import Button from '@/components/ui/button/Button.vue'
import DatePicker from '@/components/ui/DatePicker/DatePicker.vue'
import { useInternalDocumentActivityForm } from './index'
import { PrinterIcon } from '@heroicons/vue/24/outline'
import InternalActivityPdf from '@/components/document/InternalActivityPDF.vue'

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits(['close'])

const pdfComponentRef = ref<InstanceType<typeof InternalActivityPdf> | null>(null)

// Destructure the new `isFormDirty` and `resetForm` from the composable
const { form, isLoading, submitForm, handleOriginalFileChange, formSubmitted, isFormDirty, resetForm } =
  useInternalDocumentActivityForm(emit)

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

const generatePdf = () => {
  pdfComponentRef.value?.generatePdf()
}

// State for the confirmation modal, initially set to false
const showConfirmClose = ref(false)

// Intercept the close action to check for unsaved changes
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
    title="Internal Documents -(Activity Reports)"
    position="bottom"
    :close-on-overlay-click="false"
    @close="handleClose"
    class="!h-screen"
  >
    <template #title-actions>
      <div v-if="!formSubmitted" class="flex items-center space-x-2">
        <button
          type="submit"
          form="internal-document-form"
          class="relative flex items-center justify-start p-2 rounded-full text-gray-500 hover: group transition-all duration-300 ease-in-out"
          :disabled="isLoading"
        >
          <span
            class="absolute left-1/2 -translate-x-1/3 top-8 left-full mt-2 hidden group-hover:block px-2 py-1 bg-gray-700 text-white text-xs rounded-md shadow-md whitespace-nowrap z-50"
            >Save Internal Document</span
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
      <form @submit.prevent="submitForm" id="internal-document-form" class="space-y-4 md:space-y-6">
        <div
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 border-gray-300 pb-4 mb-4"
        >
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Creator:</label>
            <input
              type="text"
              v-model="form.creator"
              class="w-full border-b border-gray-400 focus:border-gray-600 outline-none py-1 text-xs sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1"
              >Document No:</label
            >
            <input
              type="text"
              v-model="form.documentNo"
              class="w-full border-b border-gray-400 focus:border-gray-600 outline-none py-1 text-xs sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1"
              >Date Created:</label
            >
            <DatePicker v-model="form.dateCreated" id="dateCreated" />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1"
              >Time Created:</label
            >
            <input
              type="time"
              v-model="form.timeCreated"
              class="w-full h-[41px] rounded border border-gray-400 focus:border-gray-600 text-center outline-none py-1 text-xs sm:text-sm"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-gray-300 pb-4">
          <div class="border border-gray-300 rounded p-4 space-y-3">
            <label class="block text-sm font-medium text-gray-700">Document Classification:</label>
            <div class="flex flex-col space-y-2">
              <label class="flex items-center">
                <input
                  type="radio"
                  name="docClass"
                  value="confidential"
                  v-model="form.documentClassification"
                  class="mr-2 accent-red-500"
                />
                <span class="text-sm">Confidential</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  name="docClass"
                  value="general"
                  v-model="form.documentClassification"
                  class="mr-2 accent-red-500"
                />
                <span class="text-sm">General Circulation</span>
              </label>
            </div>
          </div>

          <div class="border border-gray-300 rounded p-4 space-y-3">
            <label class="block text-sm font-medium text-gray-700">Level of Priority:</label>
            <div class="flex flex-col space-y-2">
              <label class="flex items-center">
                <input
                  type="radio"
                  name="priority"
                  value="high"
                  v-model="form.levelOfPriority"
                  class="mr-2 accent-red-500"
                />
                <span class="text-sm">High</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  name="priority"
                  value="normal"
                  v-model="form.levelOfPriority"
                  class="mr-2 accent-red-500"
                />
                <span class="text-sm">Normal</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  name="priority"
                  value="low"
                  v-model="form.levelOfPriority"
                  class="mr-2 accent-red-500"
                />
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

        <div class="border border-gray-400 p-3 min-h-[200px]">
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">SUBJECT:</label>
          <textarea
            v-model="form.subject"
            class="w-full h-full p-1 text-xs sm:text-sm resize-none border-none"
            rows="8"
            placeholder="Enter document subject..."
          ></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-gray-300 pb-4">
          <div class="border border-gray-400 p-3 min-h-[100px]">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">SENDER:</label>
            <textarea
              v-model="form.sender"
              class="w-full p-1 text-xs sm:text-sm resize-none border-none"
              rows="4"
              placeholder="Enter sender information..."
            ></textarea>
          </div>
          <div class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 border border-gray-400 p-3 min-h-[150px]">
              <div class="mt-8">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                  Office:
                </label>
                <input
                  type="text"
                  v-model="form.office"
                  class="w-full border-b border-gray-400 focus:border-gray-600 outline-none py-1 text-xs sm:text-sm"
                />
              </div>
              <div class="mt-8">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                  Document Date:
                </label>
                <DatePicker v-model="form.documentDate" id="documentDate" />
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 space-y-4 md:space-y-0">
          <div class="border border-gray-300 rounded p-4 space-y-4 relative">
            <div class="pl-4 space-y-3">
              <h5 class="text-sm font-medium text-gray-700">THRU:</h5>
              <div class="space-y-3">
                <div class="flex items-center space-x-2">
                  <label class="block text-xs font-medium text-gray-700 w-1/2"
                    >Destination Office:</label
                  >
                  <select
                    v-model="form.activityEntries[0].destinationOffice"
                    class="w-full border border-gray-400 rounded px-2 py-1 text-xs focus:border-gray-600 outline-none"
                  >
                    <option value="">Select Office</option>
                    <option value="OARD">OARD</option>
                    <option value="ORED">ORED</option>
                    <option value="PMD">PMD</option>
                    <option value="Other">Other</option>
                  </select>
                </div>

                <div v-if="form.activityEntries[0].destinationOffice === 'Other'">
                  <label class="block text-xs font-medium text-gray-700 mb-1 mt-2"
                    >Specify Other Office:</label
                  >
                  <input
                    type="text"
                    v-model="form.activityEntries[0].otherDestinationOffice"
                    class="w-full border-b border-gray-400 focus:border-gray-600 outline-none py-1 text-xs"
                  />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1"
                      >Date Received:</label
                    >
                    <DatePicker v-model="form.activityEntries[0].dateReceived" class="w-full" />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1"
                      >Time Received:</label
                    >
                    <input
                      type="time"
                      v-model="form.activityEntries[0].timeReceived"
                      class="w-full border border-gray-400 h-[41px] rounded px-2 py-1 text-xs focus:border-gray-600 outline-none"
                    />
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1"
                    >Instructions/Comments:</label
                  >
                  <textarea
                    v-model="form.activityEntries[0].instructionsComments"
                    class="w-full border border-gray-400 rounded p-2 text-xs focus:border-gray-600 outline-none resize-none"
                    rows="3"
                    placeholder="Enter instructions or comments..."
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="border border-gray-300 rounded p-4 space-y-4 relative">
            <div class="pl-4 space-y-3">
              <h5 class="text-sm font-medium text-gray-700">FOR:</h5>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1"
                  >Destination Office:</label
                >
                <select
                  v-model="form.activityEntries[0].forDestinationOffice"
                  class="w-full border border-gray-400 rounded px-2 py-1 text-xs focus:border-gray-600 outline-none"
                >
                  <option value="">Select Office</option>
                  <option value="OARD">OARD</option>
                  <option value="ORED">ORED</option>
                  <option value="PMD">PMD</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div v-if="form.activityEntries[0].forDestinationOffice === 'Other'">
                <label class="block text-xs font-medium text-gray-700 mb-1 mt-2"
                  >Specify Other Office:</label
                >
                <input
                  type="text"
                  v-model="form.activityEntries[0].forOtherDestinationOffice"
                  class="w-full border-b border-gray-400 focus:border-gray-600 outline-none py-1 text-xs"
                />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1"
                    >Date Received:</label
                  >
                  <DatePicker v-model="form.activityEntries[0].forDateReceived" class="w-full" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1"
                    >Time Received:</label
                  >
                  <input
                    type="time"
                    v-model="form.activityEntries[0].forTimeReceived"
                    class="w-full border border-gray-400 h-[41px] rounded px-2 py-1 text-xs focus:border-gray-600 outline-none"
                  />
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1"
                    >Instructions/Comments:</label
                  >
                  <textarea
                    v-model="form.activityEntries[0].forInstructionsComments"
                    class="w-full border border-gray-400 rounded p-2 text-xs focus:border-gray-600 outline-none resize-none"
                    rows="3"
                    placeholder="Enter instructions or comments..."
                  ></textarea>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1 ml-3"
                    >Date Returned to Creator:</label
                  >
                  <DatePicker
                    v-model="form.activityEntries[0].forDateReturnedToCreator"
                    class="w-full ml-3 "
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <InternalActivityPdf
      :formData="form"
      :uploadedFileName="uploadedFileName"
      ref="pdfComponentRef"
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
