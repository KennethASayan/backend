<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';
import type { FormData } from '@/types/incomingDocuments';

// Define props to receive all necessary data
const props = defineProps<{
  formData: FormData;
  uploadedFileName: string;
}>();

const pdfContainerRef = ref<HTMLDivElement | null>(null);

// Utility function to add business days
const addBusinessDays = (date: Date, days: number): Date => {
  const result = new Date(date);
  let addedDays = 0;
  while (addedDays < days) {
    result.setDate(result.getDate() + 1);
    if (result.getDay() !== 0 && result.getDay() !== 6) {
      addedDays++;
    }
  }
  return result;
};

// Utility function to format date for month day, year format
const formatMonthDayYear = (date: any): string => {
  if (!date) return '';
  const d = date instanceof Date ? date : new Date(date);
  if (isNaN(d.getTime())) return '';
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric' };
  return d.toLocaleDateString('en-US', options);
};

// Utility function to format date for standard display
const formatDisplayDate = (date: any): string => {
  if (!date) return '';
  const d = date instanceof Date ? date : new Date(date);
  if (isNaN(d.getTime())) return '';
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  const year = d.getFullYear();
  return `${month}/${day}/${year}`;
};

// Calculate ARTA deadline based on formData
const artaDeadline = computed(() => {
  if (props.formData.documentDeadline) {
    return 'Not Applicable';
  }
  if (props.formData.dateReceived) {
    try {
      const receivedDate = new Date(props.formData.dateReceived);
      const artaDate = addBusinessDays(receivedDate, 5);
      return formatMonthDayYear(artaDate);
    } catch (error) {
      console.error('Error calculating ARTA deadline:', error);
    }
  }
  return '';
});

// The core function to generate the PDF
const generatePdf = async () => {
  if (!pdfContainerRef.value) return;

  try {
    const canvas = await html2canvas(pdfContainerRef.value, { scale: 2 });
    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('p', 'mm', 'legal');
    const imgWidth = 216;
    const pageHeight = 356;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    let heightLeft = imgHeight;
    let position = 0;

    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
    heightLeft -= pageHeight;

    while (heightLeft > 0) {
      position = heightLeft - imgHeight;
      pdf.addPage();
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;
    }

    const pdfBlob = pdf.output('blob');
    const pdfUrl = URL.createObjectURL(pdfBlob);
    window.open(pdfUrl, '_blank');
  } catch (error) {
    console.error('Error generating PDF:', error);
  }
};

// Expose the generatePdf function so the parent component can call it
defineExpose({
  generatePdf,
});
</script>

<template>
  <div class="pdf-container-hidden absolute -top-[9999px] left-0" ref="pdfContainerRef">
    <div class="p-8 font-serif text-black">
      <div class="header-section mb-6 mt-20 grid grid-cols-[1fr_2fr] gap-4">
        <div class="flex items-center">
          <div class="logo-placeholder w-28 h-28 mr-4 ml-4 flex items-center justify-center">
            <img src="@/assets/DENRLOGO.png" alt="DENR Logo" class="max-w-full max-h-full" />
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
              <span class="font-bold mt-2 whitespace-nowrap">Receiving Office:</span>
              <div class="border-b p-1 border-black flex-1 text-center uppercase">
                {{ formData.receivingOffice }}
              </div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Date Received:</span>
              <div class="border-b p-1 border-black flex-1 text-center">
                {{ formatMonthDayYear(formData.dateReceived) }}
              </div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Document Deadline:</span>
              <div class="border-b p-1 border-black flex-1 text-center">
                {{ formatMonthDayYear(formData.documentDeadline) || 'Not Applicable' }}
              </div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Original Document:</span>
              <div class="border-b border-black p-1 flex-1 text-center">
                {{ uploadedFileName || 'N/A' }}
              </div>
            </div>
          </div>

          <div class="p-2">
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Received By:</span>
              <div class="border-b border-black p-1 flex-1 text-center">Kenneth Sayan</div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Time Received:</span>
              <div class="border-b p-1 border-black flex-1 text-center">
                {{ formData.timeReceived }}
              </div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">ARTA Deadline:</span>
              <div class="border-b p-1 border-black flex-1 text-center">
                {{ artaDeadline }}
              </div>
            </div>
            <div class="flex items-end space-x-4">
              <span class="font-bold mt-2 whitespace-nowrap">Document No:</span>
              <div class="border-b p-1 border-black flex-1 text-center">
                {{ formData.documentNo }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="ml-1 grid grid-cols-2 gap-4 mb-6">
        <div class="space-y-4">
          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">SUBJECT:</span>
            </div>
            <div class="p-2 min-h-[150px] text-sm uppercase">
              {{ formData.subject }}
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="border border-black">
              <div class="p-2 border-b border-black">
                <span class="font-bold text-sm">REMARKS FROM THE ORED/HEA:</span>
              </div>
              <div class="p-2 min-h-[100px] text-sm">
                {{ formData.remarksFromOredHea }}
              </div>
            </div>
            <div class="border border-black">
              <div class="p-2 border-b border-black">
                <span class="font-bold text-sm">REFERRED TO:</span>
              </div>
              <div class="p-2 text-xs">
                <div class="grid grid-cols-2 gap-2">
                  <div v-for="office in ['ARD MS', 'ARD TS', 'MGB']" :key="office">
                    <div class="flex items-center">
                      <span
                        class="inline-block w-3 h-3 border border-black mr-2"
                        :class="{ 'bg-black': formData.referredTo.includes(office) }"
                      ></span>
                      <div class="mb-3"><span>{{ office }}</span></div>
                    </div>
                  </div>
                  <div v-for="office in ['EMB', 'RSCIG']" :key="office">
                    <div class="flex items-center">
                      <span
                        class="inline-block w-3 h-3 border border-black mr-2"
                        :class="{ 'bg-black': formData.referredTo.includes(office) }"
                      ></span>
                      <div class="mb-3"><span>{{ office }}</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">DATE AND TIME RELEASED TO BUREAUS:</span>
            </div>
            <div class="p-10 text-xs text-center">
              {{ formatDisplayDate(formData.dateTimeReleasedToBureaus) }}
              {{ formData.dateTimeReleasedToBureausTime }}
            </div>
          </div>
          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">DATE AND TIME RECEIVED BY OARD:</span>
            </div>
            <div class="p-10 text-xs text-center">
              {{ formatDisplayDate(formData.dateTimeReceivedByOard) }}
              {{ formData.dateTimeReceivedByOardTime }}
            </div>
          </div>
          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">FINAL ACTION OFFICE:</span>
            </div>
            <div class="p-2 text-xs">
              <div class="grid grid-cols-2">
                <div v-for="office in ['ADMIN DIVISION', 'FINANCE DIVISION', 'LEGAL DIVISION', 'PMD', 'RSCIG']" :key="office">
                  <div class="flex items-center">
                    <span
                      class="inline-block w-3 h-3 border border-black mr-2"
                      :class="{ 'bg-black': formData.finalActionOffice.includes(office) }"
                    ></span>
                    <div class="mb-3"><span>{{ office }}</span></div>
                  </div>
                </div>
                <div v-for="office in ['CDD', 'ED', 'LPDD', 'SMD', 'OARDTS-Task Force']" :key="office">
                  <div class="flex items-center">
                    <span
                      class="inline-block w-3 h-3 border border-black mr-2"
                      :class="{ 'bg-black': formData.finalActionOffice.includes(office) }"
                    ></span>
                    <div class="mb-3"><span>{{ office }}</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">ARD'S INSTRUCTIONS:</span>
            </div>
            <div class="p-2 min-h-[100px] text-xs">
              {{ formData.ardInstructions }}
            </div>
          </div>

          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">ROUTE OF DOCUMENT FOR APPROVAL:</span>
            </div>
            <div class="p-2 text-xs">
              <div class="mb-2">
                <span class="font-bold">Date received by OARD:</span>
                <div>
                  {{ formatDisplayDate(formData.routeForApproval.dateTimeReceivedByOard) }}
                  {{ formData.routeForApproval.timeReceivedByOard }}
                </div>
              </div>
              <div class="ml-4 mb-2 italic text-sm text-gray-600">
                <span>Date returned to action office for revision:</span>
                <div>{{ formatDisplayDate(formData.dateReturnedToActionOfficeForRevision) }}</div>
              </div>
              <div class="ml-4 mb-3 italic text-sm text-gray-600">
                <span>Date received by OARD after revision:</span>
                <div>{{ formatDisplayDate(formData.dateReceivedByOardAfterRevision) }}</div>
              </div>
              <div class="mb-2">
                <span class="font-bold">Date received by ORED:</span>
                <div>
                  {{ formatDisplayDate(formData.routeForApproval.dateTimeReceivedByOred) }}
                  {{ formData.routeForApproval.timeReceivedByOred }}
                </div>
              </div>
              <div class="ml-4 mb-2 italic text-gray-600">
                <span>Date returned to action office for revision:</span>
                <div>{{ formatDisplayDate(formData.dateReturnedToActionOfficeForRevision2) }}</div>
              </div>
              <div class="ml-4 mb-3 italic text-gray-600">
                <span>Date received by ORED after revision:</span>
                <div>{{ formatDisplayDate(formData.dateReceivedByOredAfterRevision) }}</div>
              </div>
              <div>
                <span class="font-bold">Date released to action office (APPROVED):</span>
                <div>
                  {{ formatDisplayDate(formData.routeForApproval.dateTimeReleasedToActionOffice) }}
                  {{ formData.routeForApproval.timeReleasedToActionOffice }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-4 flex flex-col mr-3">
          <div class="border border-black flex flex-col">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">NAME OF SENDER AND ADDRESS/OFFICE:</span>
            </div>
            <div class="p-2 min-h-[100px] text-sm flex-1 uppercase">
              {{ formData.senderName }}
            </div>
          </div>
          <div class="border border-black flex flex-col">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">DATE AND TIME RECEIVED BY ORED:</span>
            </div>
            <div class="p-8 text-xs text-center flex-1">
              {{ formatDisplayDate(formData.dateTimeReceivedByOredDate) }}
              {{ formData.dateTimeReceivedByOredTime }}
            </div>
          </div>

          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">RED'S INSTRUCTIONS:</span>
            </div>
            <div class="p-2 text-xs">
              <div class="grid grid-cols-2 gap-x-4">
                <div class="space-y-1">
                  <div v-for="instruction in ['For Dissemination', 'For Compliance', 'For Discussion']" :key="instruction" class="flex items-center">
                    <span
                      class="inline-block w-3 h-3 border border-black mr-2"
                      :class="{ 'bg-black': formData.redInstructions.includes(instruction) }"
                    ></span>
                    <div class="mb-3"><span>{{ instruction }}</span></div>
                  </div>
                </div>
                <div class="space-y-1">
                  <div v-for="instruction in ['For Appropriate Action', 'For Record/File/Reference', 'For Review/Evaluation/Recommendation']" :key="instruction" class="flex items-center">
                    <span
                      class="inline-block w-3 h-3 border border-black mr-2"
                      :class="{ 'bg-black': formData.redInstructions.includes(instruction) }"
                    ></span>
                    <div class="mb-3"><span>{{ instruction }}</span></div>
                  </div>
                </div>
              </div>
              <div class="mt-2 p-2 border border-gray-300 min-h-16 text-xs">
                {{ formData.redInstructionsTimestamp }}
              </div>
            </div>
          </div>

          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">DATE AND TIME RECEIVED BY FINAL ACTION OFFICE:</span>
            </div>
            <div class="p-2 min-h-[70px] text-xs text-center">
              {{ formatDisplayDate(formData.dateTimeReceivedByFinalAction) }}
              {{ formData.dateTimeReceivedByFinalActionTime }}
            </div>
          </div>

          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">INSTRUCTIONS FOR FINAL ACTION:</span>
            </div>
            <div class="p-2 min-h-[100px] text-xs">
              {{ formData.instructionsForFinalAction }}
            </div>
          </div>

          <div class="border border-black p-2 text-xs">
            <div class="grid grid-cols-3 gap-2 mb-2">
              <div class="flex items-center">
                <span
                  class="inline-block w-3 h-3 border border-black mr-1"
                  :class="{ 'bg-black': formData.noComplianceRequired }"
                ></span>
                <div class="mb-3"><span>No Compliance Required</span></div>
              </div>
              <div class="flex items-center">
                <span
                  class="inline-block w-3 h-3 border border-black mr-1"
                  :class="{ 'bg-black': formData.instructionType === 'Simple' }"
                ></span>
                <div class="mb-3"><span>Simple</span></div>
              </div>
              <div class="flex items-center">
                <span
                  class="inline-block w-3 h-3 border border-black mr-1"
                  :class="{ 'bg-black': formData.instructionType === 'Complex' }"
                ></span>
                <div class="mb-3"><span>Complex</span></div>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
              <div class="flex items-center">
                <span
                  class="inline-block w-3 h-3 border border-black mr-1"
                  :class="{ 'bg-black': formData.instructionType === 'Highly Technical' }"
                ></span>
                <div class="mb-3"><span>Highly Technical</span></div>
              </div>
              <div class="flex items-center">
                <span
                  class="inline-block w-3 h-3 border border-black mr-1"
                  :class="{ 'bg-black': formData.instructionType === 'Routine' }"
                ></span>
                <div class="mb-3"><span>Routine</span></div>
              </div>
              <div class="flex items-center">
                <span
                  class="inline-block w-3 h-3 border border-black mr-1"
                  :class="{ 'bg-black': formData.instructionType === 'Legal Concern' }"
                ></span>
                <div class="mb-3"><span>Legal Concern</span></div>
              </div>
            </div>
          </div>

          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">DATE AND TIME DOCUMENT RE-ROUTED TO:</span>
            </div>
            <div class="p-2 text-xs">
              <div class="mb-3">
                <div class="border-b p-1 text-center text-sm border-black">
                  {{ formatDisplayDate(formData.dateTimeDocumentRerouted) }}
                  {{ formData.dateTimeDocumentReroutedTime }}
                </div>
              </div>
              <div class="grid grid-cols-2 gap-1">
                <div v-for="office in ['ADMIN DIVISION', 'SMD', 'CDD', 'LEGAL DIVISION', 'PMD']" :key="office">
                  <div class="flex items-center">
                    <span
                      class="inline-block w-3 h-3 border border-black mr-1"
                      :class="{ 'bg-black': formData.reRoutedTo.includes(office) }"
                    ></span>
                    <div class="mb-3"><span>{{ office }}</span></div>
                  </div>
                </div>
                <div v-for="office in ['RSCIG', 'FINANCE DIVISION', 'OARDTS-Task Force', 'ED', 'LPDD']" :key="office">
                  <div class="flex items-center">
                    <span
                      class="inline-block w-3 h-3 border border-black mr-1"
                      :class="{ 'bg-black': formData.reRoutedTo.includes(office) }"
                    ></span>
                    <div class="mb-3"><span>{{ office }}</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="border border-black">
            <div class="p-2 border-b border-black">
              <span class="font-bold text-sm">UPLOADING OF FINAL ACTION:</span>
            </div>
            <div class="p-2 text-xs">
              <div class="grid grid-cols-2 gap-1">
                <div v-for="mode in ['Email', 'Postal Services', 'Fax']" :key="mode">
                  <div class="flex items-center">
                    <span
                      class="inline-block w-3 h-3 border border-black mr-1"
                      :class="{ 'bg-black': formData.uploadingFinalAction.mode.includes(mode) }"
                    ></span>
                    <div class="mb-3"><span>{{ mode }}</span></div>
                  </div>
                </div>
                <div v-for="mode in ['Hand-Carry', 'Courier']" :key="mode">
                  <div class="flex items-center">
                    <span
                      class="inline-block w-3 h-3 border border-black mr-1"
                      :class="{ 'bg-black': formData.uploadingFinalAction.mode.includes(mode) }"
                    ></span>
                    <div class="mb-3"><span>{{ mode }}</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
