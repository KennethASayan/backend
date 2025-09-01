// src/components/Dashboard/InternalDocuments/InternalDocument(foradding)/index.ts

import { ref, computed, type Ref } from 'vue';
import toast from '@/components/ui/message/toast';

// Define the shape of a single activity entry
export interface ActivityEntry {
  destinationOffice: string;
  otherDestinationOffice?: string;
  dateReceived: Date | null;
  timeReceived: string;
  instructionsComments: string;
  forDestinationOffice: string;
  forOtherDestinationOffice?: string;
  forDateReceived: Date | null;
  forTimeReceived: string;
  forInstructionsComments: string;
  forDateReturnedToCreator: Date | null;
}

// Define the shape of the entire form
export interface InternalDocumentActivityForm {
  creator: string;
  documentNo: string;
  dateCreated: Date | null;
  timeCreated: string;
  documentClassification: 'confidential' | 'general' | '';
  levelOfPriority: 'high' | 'normal' | 'low' | '';
  originalFile: File | null;
  subject: string;
  sender: string;
  office: string;
  documentDate: Date | null;
  activityEntries: ActivityEntry[];
}

// Apply the interface to the initial state object
const initialFormState: InternalDocumentActivityForm = {
  creator: '',
  dateCreated: null,
  timeCreated: '',
  documentNo: '',
  documentClassification: 'general',
  levelOfPriority: 'normal',
  subject: '',
  originalFile: null,
  sender: '',
  office: '',
  documentDate: null,
  activityEntries: [
    {
      destinationOffice: '',
      otherDestinationOffice: '',
      dateReceived: null,
      timeReceived: '',
      instructionsComments: '',
      forDestinationOffice: '',
      forOtherDestinationOffice: '',
      forDateReceived: null,
      forTimeReceived: '',
      forInstructionsComments: '',
      forDateReturnedToCreator: null,
    },
  ],
};

export function useInternalDocumentActivityForm(emit: (event: 'close') => void) {
  const { addToast } = toast;

  const form: Ref<InternalDocumentActivityForm> = ref({ ...initialFormState });
  const isLoading = ref(false);
  const formSubmitted = ref(false);

  const resetForm = () => {
    form.value = { ...initialFormState };
    formSubmitted.value = false;
  };

  const submitForm = async () => {
    // Basic validation to prevent submitting empty forms
    if (!form.value.creator || !form.value.documentNo || !form.value.subject) {
      addToast({ title: 'Validation Error', description: 'Please fill in all required fields.', type: 'error' });
      return;
    }

    isLoading.value = true;
    try {
      console.log('Submitting form with data:', form.value);
      await new Promise((resolve) => setTimeout(resolve, 1500));
      addToast({ title: 'Success', description: 'Internal document (activity report) submitted successfully!', type: 'success' });
      formSubmitted.value = true;
    } catch (error) {
      console.error('Form submission failed:', error);
      addToast({ title: 'Error', description: 'Failed to submit internal document.', type: 'error' });
    } finally {
      isLoading.value = false;
    }
  };

  const handleOriginalFileChange = (file: File) => {
    form.value.originalFile = file;
    addToast({ title: 'File Selected', description: `File selected: ${file.name}`, type: 'info' });
  };

  return {
    form,
    isLoading,
    formSubmitted,
    resetForm,
    submitForm,
    handleOriginalFileChange,
  };
}
