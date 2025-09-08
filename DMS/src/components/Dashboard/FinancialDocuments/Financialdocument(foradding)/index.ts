// src/components/Dashboard/FinancialDocuments/Financialdocument(foradding)/index.ts

import { ref, computed } from 'vue';
import toast from '@/components/ui/message/toast';

// Define the initial state of the form. This is the source of truth for comparison.
const initialFormState = {
  creator: '',
  dateCreated: null,
  timeCreated: '',
  documentNo: '',
  documentClassification: 'general',
  levelOfPriority: 'normal',
  subject: '',
  originalDocumentFile: null,
  procurementDateReceived: null,
  procurementTimeReceived: '',
  procurementDateReleased: null,
  procurementTimeReleased: '',
  financeDateReceived: null,
  financeTimeReceived: '',
  financeDateReleased: null,
  financeTimeReleased: '',
  cashieringDateReceived: null,
  cashieringTimeReceived: '',
  cashieringDateReleased: null,
  cashieringTimeReleased: '',
  finalActionDateUploaded: null,
  finalActionFile: null,
  dateReleased: null,
};

export function useFinancialDocumentForm(emit) {
  const { addToast } = toast;

  // Use a deep copy of the initial state to create the reactive form
  const form = ref({ ...initialFormState });
  const isLoading = ref(false);
  const formSubmitted = ref(false);

  const resetForm = () => {
    form.value = { ...initialFormState };
    formSubmitted.value = false;
  };

  const handleClose = () => {
    resetForm();
    emit('close');
  };

  const validateForm = () => {
    if (!form.value.creator || !form.value.documentNo || !form.value.subject) {
      return false;
    }
    return true;
  };

  const submitForm = async () => {
    if (!validateForm()) {
      addToast({
        title: 'Validation Error',
        description: 'Please fill in all required fields.',
        type: 'error',
      });
      return;
    }

    isLoading.value = true;
    try {
      console.log('Submitting financial document form:', form.value);
      await new Promise(resolve => setTimeout(resolve, 1500));
      addToast({
        title: 'Success',
        description: 'Financial document submitted successfully!',
        type: 'success',
      });
      formSubmitted.value = true;
    } catch (error) {
      console.error('Submission failed:', error);
      addToast({
        title: 'Submission Failed',
        description: 'Failed to submit financial document.',
        type: 'error',
      });
    } finally {
      isLoading.value = false;
    }
  };

  const handleOriginalFileChange = (file) => {
    form.value.originalDocumentFile = file;
    addToast({
      title: 'File Selected',
      description: `Original document selected: ${file.name}`,
      type: 'info',
    });
  };

  const handleFinalActionFileChange = (file) => {
    form.value.finalActionFile = file;
    addToast({
      title: 'File Selected',
      description: `Final action file selected: ${file.name}`,
      type: 'info',
    });
  };

  return {
    form,
    isLoading,
    formSubmitted,
    resetForm,
    handleClose,
    submitForm,
    handleOriginalFileChange,
    handleFinalActionFileChange,
  };
}
