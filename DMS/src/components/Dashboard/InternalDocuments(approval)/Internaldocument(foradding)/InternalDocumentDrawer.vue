<script setup lang="ts">
import { ref } from 'vue';
import Dialog from '@/components/ui/Dialog/Dialog.vue';
import Button from '@/components/ui/button/Button.vue';
import DatePicker from '@/components/ui/DatePicker/DatePicker.vue';
import { useInternalDocumentForm } from './index';
import { PrinterIcon } from '@heroicons/vue/24/outline';
import InternalApprovalPdf from '@/components/document/InternalApprovalPDF.vue';

const props = defineProps<{
  isOpen: boolean;
}>();

const emit = defineEmits(['close']);

// Create a ref for the new component.
const pdfComponentRef = ref<InstanceType<typeof InternalApprovalPdf> | null>(null);

const {
  form,
  isLoading,
  submitForm,
  handleOriginalFileChange,
  handleFinalActionFileChange,
  formSubmitted,
  resetForm,
} = useInternalDocumentForm(emit);

const uploadFileRef = ref<HTMLInputElement | null>();
const triggerFileUpload = () => {
  uploadFileRef.value?.click();
};

const uploadedFileName = ref('');
const handleFileChange = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files?.length) {
    const file = input.files[0];
    uploadedFileName.value = file.name;
    handleOriginalFileChange(file);
  }
};

const uploadFinalActionRef = ref<HTMLInputElement | null>();
const triggerFinalActionUpload = () => {
  uploadFinalActionRef.value?.click();
};

const uploadedFinalActionFileName = ref('');
const handleFinalActionFileChangeInternal = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files?.length) {
    uploadedFinalActionFileName.value = input.files[0].name;
    handleFinalActionFileChange(input.files[0]);
  }
};

const addDate = (field: keyof typeof form.value) => {
  if (Array.isArray(form.value[field])) {
    (form.value[field] as (Date | null)[]).push(null);
  }
};

const removeDate = (field: keyof typeof form.value, index: number) => {
  if (Array.isArray(form.value[field])) {
    (form.value[field] as (Date | null)[]).splice(index, 1);
  }
};

const generatePdf = () => {
  pdfComponentRef.value?.generatePdf();
};

// State for the confirmation modal, initially set to false
const showConfirmClose = ref(false);

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
    title="Internal Documents -(For Approval)"
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
          >Save Internal Document</span>
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 border-gray-300 pb-4 mb-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Creator:</label>
            <input type="text" v-model="form.creator" class="w-full border-b border-gray-400 focus:border-gray-600 outline-none py-1 text-xs sm:text-sm" />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Document No:</label>
            <input type="text" v-model="form.documentNo" class="w-full border-b border-gray-400 focus:border-gray-600 outline-none py-1 text-xs sm:text-sm" />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Date Created:</label>
            <DatePicker v-model="form.dateCreated" id="dateCreated" />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Time Created:</label>
            <input type="time" v-model="form.timeCreated" class="w-full h-[41px] rounded border border-gray-400 focus:border-gray-600 text-center outline-none py-1 text-xs sm:text-sm" />
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
              <span class="text-xs text-gray-500 truncate text-center">{{ uploadedFileName || 'No file selected' }}</span>
            </div>
          </div>
        </div>

        <div class="border border-gray-400 p-3 min-h-[250px]">
          <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">SUBJECT:</label>
          <textarea
            v-model="form.subject"
            class="w-full h-full p-1 text-xs sm:text-sm resize-none"
            rows="10"
          ></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
          <div class="border border-gray-400 p-3 space-y-4">
            <h3 class="text-sm font-bold text-gray-900 text-center">ROUTE OF DOCUMENT FOR APPROVAL</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4">
              <div class="flex items-center space-x-2">
                <label class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">1. DATE RECEIVED BY OARD:</label>
                <DatePicker v-model="form.dateReceivedByOard" class="w-full border-gray-400 py-1 text-xs" />
              </div>
            </div>

            <div v-for="(date, index) in form.dateReturnedToCreatorOard" :key="`OARD-return-${index}`" class="flex items-center space-x-2 pl-4">
              <label class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">DATE RETURNED TO CREATOR FOR REVISION:</label>
              <DatePicker v-model="form.dateReturnedToCreatorOard[index]" class="w-[50vh] border-gray-400 py-1 text-xs" />

              <div class="flex space-x-2">
                <button
                  v-if="form.dateReturnedToCreatorOard.length > 1"
                  type="button"
                  @click="removeDate('dateReturnedToCreatorOard', index)"
                  class="bg-red-500 text-white text-xs px-2 py-1 rounded hover:bg-red-600"
                >
                  Delete
                </button>
                <button
                  v-if="date && index === form.dateReturnedToCreatorOard.length - 1"
                  type="button"
                  @click="addDate('dateReturnedToCreatorOard')"
                  class="bg-blue-500 text-white text-xs px-2 py-1 rounded hover:bg-blue-600"
                >
                  Add Date
                </button>
              </div>
            </div>

            <div v-for="(date, index) in form.dateReceivedByOardAfterRevision" :key="`OARD-after-${index}`" class="flex items-center space-x-2 pl-4">
              <label class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">DATE RECEIVED BY OARD AFTER REVISION:</label>
              <DatePicker v-model="form.dateReceivedByOardAfterRevision[index]" class="w-[50vh] border-gray-400 py-1 text-xs" />

              <div class="flex space-x-2">
                <button
                  v-if="form.dateReceivedByOardAfterRevision.length > 1"
                  type="button"
                  @click="removeDate('dateReceivedByOardAfterRevision', index)"
                  class="bg-red-500 text-white text-xs px-2 py-1 rounded hover:bg-red-600"
                >
                  Delete
                </button>
                <button
                  v-if="date && index === form.dateReceivedByOardAfterRevision.length - 1"
                  type="button"
                  @click="addDate('dateReceivedByOardAfterRevision')"
                  class="bg-blue-500 text-white text-xs px-2 py-1 rounded hover:bg-blue-600"
                >
                  Add Date
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4 pt-4">
              <div class="flex items-center space-x-2">
                <label class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">2. DATE RECEIVED BY ORED:</label>
                <DatePicker v-model="form.dateReceivedByOred" class="w-full border-gray-400 py-1 text-xs" />
              </div>
            </div>

            <div v-for="(date, index) in form.dateReturnedToCreatorOred" :key="`ORED-return-${index}`" class="flex items-center space-x-2 pl-4">
              <label class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">DATE RETURNED TO CREATOR FOR REVISION:</label>
              <DatePicker v-model="form.dateReturnedToCreatorOred[index]" class="w-[50vh] border-gray-400 py-1 text-xs" />

              <div class="flex space-x-2">
                <button
                  v-if="form.dateReturnedToCreatorOred.length > 1"
                  type="button"
                  @click="removeDate('dateReturnedToCreatorOred', index)"
                  class="bg-red-500 text-white text-xs px-2 py-1 rounded hover:bg-red-600"
                >
                  Delete
                </button>
                <button
                  v-if="date && index === form.dateReturnedToCreatorOred.length - 1"
                  type="button"
                  @click="addDate('dateReturnedToCreatorOred')"
                  class="bg-blue-500 text-white text-xs px-2 py-1 rounded hover:bg-blue-600"
                >
                  Add Date
                </button>
              </div>
            </div>

            <div v-for="(date, index) in form.dateReceivedByOredAfterRevision" :key="`ORED-after-${index}`" class="flex items-center space-x-2 pl-4">
              <label class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">DATE RECEIVED BY ORED AFTER REVISION:</label>
              <DatePicker v-model="form.dateReceivedByOredAfterRevision[index]" class="w-[50vh] border-gray-400 py-1 text-xs" />

              <div class="flex space-x-2">
                <button
                  v-if="form.dateReceivedByOredAfterRevision.length > 1"
                  type="button"
                  @click="removeDate('dateReceivedByOredAfterRevision', index)"
                  class="bg-red-500 text-white text-xs px-2 py-1 rounded hover:bg-red-600"
                >
                  Delete
                </button>
                <button
                  v-if="date && index === form.dateReceivedByOredAfterRevision.length - 1"
                  type="button"
                  @click="addDate('dateReceivedByOredAfterRevision')"
                  class="bg-blue-500 text-white text-xs px-2 py-1 rounded hover:bg-blue-600"
                >
                  Add Date
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4 pt-4">
              <div class="flex items-center space-x-2">
                <label class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">3. DATE RECEIVED BY CREATOR:</label>
                <DatePicker v-model="form.dateReceivedByCreator" class="w-full border-gray-400 py-1 text-xs" />
              </div>
            </div>
          </div>
          <div class="border border-gray-400 mb-25 p-20 space-y-4">
            <h3 class="text-xs sm:text-sm font-medium text-gray-700 w-1/2">4. FINAL ACTION BY THE CREATOR:</h3>
            <div class="flex items-center space-x-4 justify-between">
              <label class="text-xs sm:text-sm font-medium text-gray-700">DATE UPLOADED:</label>
              <DatePicker v-model="form.dateUploaded" class="w-[50vh] border-gray-400 py-1 text-xs" />
            </div>
            <div class="flex items-center space-x-4 justify-between">
              <label class="text-xs sm:text-sm font-medium text-gray-700 mr-2">DATE RELEASED:</label>
              <DatePicker v-model="form.dateReleased" class="w-[50vh] border-gray-400 py-1 text-xs" />
            </div>
            <div class="space-y-2">
              <label class="text-xs font-medium text-gray-700">MODE:</label>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-1">
                <label class="flex items-center text-xs sm:text-sm">
                  <input
                    type="checkbox"
                    v-model="form.mode"
                    value="Email"
                    class="mr-1 accent-blue"
                  />
                  Email
                </label>
                <label class="flex items-center text-xs sm:text-sm">
                  <input
                    type="checkbox"
                    v-model="form.mode"
                    value="Courier"
                    class="mr-1 accent-blue"
                  />
                  Courier
                </label>
                <label class="flex items-center text-xs sm:text-sm">
                  <input
                    type="checkbox"
                    v-model="form.mode"
                    value="Hand-Carry"
                    class="mr-1 accent-blue"
                  />
                  Hand-Carry
                </label>
                <label class="flex items-center text-xs sm:text-sm">
                  <input
                    type="checkbox"
                    v-model="form.mode"
                    value="Fax"
                    class="mr-1 accent-blue"
                  />
                  Fax
                </label>
                <label class="flex items-center text-xs sm:text-sm">
                  <input
                    type="checkbox"
                    v-model="form.mode"
                    value="Postal Services"
                    class="mr-1 accent-blue"
                  />
                  Postal Services
                </label>
              </div>
            </div>
            <div class="flex items-center justify-end space-x-2 pt-4">
              <button
                type="button"
                @click="triggerFinalActionUpload"
                class="bg-teal-500 text-white px-3 py-1.5 rounded text-xs hover:bg-teal-600 transition-colors w-auto"
              >
                Upload File
              </button>
              <input
                ref="uploadFinalActionRef"
                type="file"
                class="hidden"
                @change="handleFinalActionFileChangeInternal"
              />
              <span class="text-xs text-gray-500 truncate flex-1">{{ uploadedFinalActionFileName || 'No file selected' }}</span>
            </div>
          </div>
        </div>
      </form>
    </div>

    <InternalApprovalPdf
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
    <div class="mt-4 flex justify-end space-x-2">
      <Button @click="cancelClose" variant="danger">Cancel</Button>
      <Button @click="confirmClose" variant="success">Close</Button>
    </div>
  </Dialog>
</template>
