<script setup lang="ts">
import { ref, watch } from 'vue'
import Dialog from '@/components/ui/Dialog/Dialog.vue'
import button from '@/components/ui/button/Button.vue'

const props = defineProps<{
  isOpen: boolean;
}>();

const emit = defineEmits(['close', 'documentSelected']);

const selectedDocumentType = ref('Incoming Documents'); // Set default selection

// Watch for changes in isOpen prop to reset the selected type if needed
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    selectedDocumentType.value = 'Incoming Documents'; // Reset to default when opened
  }
});

const submitDocumentType = () => {
  console.log('Selected Document Type:', selectedDocumentType.value);
  emit('documentSelected', selectedDocumentType.value); // Emit the selected type
  emit('close'); // Close the modal
};

const handleClose = () => {
  emit('close');
};
</script>

<template>
  <Dialog
    :is-open="isOpen"
    type="modal"
    title="Select type of document"
    position="center"
    :close-on-overlay-click="true"
    @close="handleClose"
  >
    <div class="space-y-4 py-2 px-4">
      <label class="flex items-center cursor-pointer">
        <input
          type="radio"
          value="Incoming Documents"
          v-model="selectedDocumentType"
          class="form-radio h-4 w-4 text-green-600 transition duration-150 ease-in-out"
        />
        <span class="ml-2 text-gray-700">Incoming Documents</span>
      </label>
      <label class="flex items-center cursor-pointer">
        <input
          type="radio"
          value="Internal Documents (For Approval)"
          v-model="selectedDocumentType"
          class="form-radio h-4 w-4 text-green-600 transition duration-150 ease-in-out"
        />
        <span class="ml-2 text-gray-700">Internal Documents (For Approval)</span>
      </label>
      <label class="flex items-center cursor-pointer">
        <input
          type="radio"
          value="Financial Documents"
          v-model="selectedDocumentType"
          class="form-radio h-4 w-4 text-green-600 transition duration-150 ease-in-out"
        />
        <span class="ml-2 text-gray-700">Financial Documents</span>
      </label>
      <label class="flex items-center cursor-pointer">
        <input
          type="radio"
          value="Internal Documents (Activity Reports)"
          v-model="selectedDocumentType"
          class="form-radio h-4 w-4 text-green-600 transition duration-150 ease-in-out"
        />
        <span class="ml-2 text-gray-700">Internal Documents (Activity Reports)</span>
      </label>
    </div>
      <div class="flex justify-center pt-4  border-neutral-200 dark:border-neutral-700">
        <button
          @click="submitDocumentType"
          class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
        >
          Submit
        </button>
      </div>
  </Dialog>
</template>