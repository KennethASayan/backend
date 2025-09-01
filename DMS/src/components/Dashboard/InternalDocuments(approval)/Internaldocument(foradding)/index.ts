// src/components/Dashboard/InternalDocuments/InternalDocument(forapproval)/index.ts

import { ref, computed } from 'vue';
import toast from '@/components/ui/message/toast';

// Define the initial state of the form. This is our source of truth for comparison.
const initialFormState = {
  creator: '',
  documentNo: '',
  dateCreated: null,
  timeCreated: '',
  documentClassification: 'general',
  levelOfPriority: 'normal',
  subject: '',
  dateReceivedByOard: null,
  dateReturnedToCreatorOard: [null],
  dateReceivedByOardAfterRevision: [null],
  dateReceivedByOred: null,
  dateReturnedToCreatorOred: [null],
  dateReceivedByOredAfterRevision: [null],
  dateReceivedByCreator: null,
  dateUploaded: null,
  dateReleased: null,
  mode: [],
  originalFile: null,
  uploadedFinalActionFile: null
};

// We create a deep copy of the initial state to ensure the reactive form is a new object
const initialFormStateCopy = JSON.parse(JSON.stringify(initialFormState));

export function useInternalDocumentForm(emit: (event: 'close') => void) {
  const { addToast } = toast;

  const form = ref({ ...initialFormStateCopy });
  const isLoading = ref(false);
  const formSubmitted = ref(false);

  const resetForm = () => {
    form.value = { ...initialFormStateCopy };
    formSubmitted.value = false;
  };

  const submitForm = async () => {
    isLoading.value = true;
    try {
      await new Promise(resolve => setTimeout(resolve, 1500));
      addToast({
        title: 'Success',
        description: 'Internal document submitted successfully!',
        type: 'success',
      });
      formSubmitted.value = true;
    } catch (error) {
      console.error('Submission failed:', error);
      addToast({
        title: 'Error',
        description: 'Failed to submit internal document.',
        type: 'error',
      });
    } finally {
      isLoading.value = false;
    }
  };

  const handleOriginalFileChange = (file: File) => {
    form.value.originalFile = file;
    addToast({
      title: 'File Selected',
      description: `Original file for internal form: ${file.name}`,
      type: 'info',
    });
  };

  const handleFinalActionFileChange = (file: File) => {
    form.value.uploadedFinalActionFile = file;
    addToast({
      title: 'File Selected',
      description: `Final action file for internal form: ${file.name}`,
      type: 'info',
    });
  };

  return {
    form,
    isLoading,
    formSubmitted,
    resetForm,
    submitForm,
    handleOriginalFileChange,
    handleFinalActionFileChange,
  };
}
