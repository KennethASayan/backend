// src/types/document-types.ts

export interface RouteForApproval {
  dateTimeReceivedByOard: Date | null
  timeReceivedByOard: string
  dateTimeReceivedByOred: Date | null
  timeReceivedByOred: string
  dateTimeReleasedToActionOffice: Date | null
  timeReleasedToActionOffice: string
}

export interface UploadingFinalAction {
  dateReleased: Date | null
  mode: string[]
}

export interface FormData {
  receivingOffice: string
  documentNo: string
  dateReceived: Date | null
  timeReceived: string
  documentDeadline: Date | null
  artaDeadline: string
  documentClassification: string
  subject: string
  senderName: string
  dateTimeReceivedByOredDate: Date | null
  dateTimeReceivedByOredTime: string
  remarksFromOredHea: string
  referredTo: string[]
  dateTimeReleasedToBureaus: Date | null
  dateTimeReleasedToBureausTime: string
  redInstructions: string[]
  redInstructionsTimestamp: string
  dateTimeReceivedByOard: Date | null
  dateTimeReceivedByOardTime: string
  dateTimeReceivedByFinalAction: Date | null
  dateTimeReceivedByFinalActionTime: string
  finalActionOffice: string[]
  instructionsForFinalAction: string
  instructionType: string
  ardInstructions: string
  routeForApproval: RouteForApproval
  dateReturnedToActionOfficeForRevision: Date | null
  dateReceivedByOardAfterRevision: Date | null
  dateReturnedToActionOfficeForRevision2: Date | null
  dateReceivedByOredAfterRevision: Date | null
  dateTimeDocumentRerouted: Date | null
  dateTimeDocumentReroutedTime: string
  reRoutedTo: string[]
  noComplianceRequired: boolean
  uploadingFinalAction: UploadingFinalAction
}

export interface ValidationState {
  receivingOffice: string
  documentNo: string
  dateReceived: string
  timeReceived: string
  documentDeadline: string
  documentClassification: string
  subject: string
  senderName: string
  referredTo: string
  finalActionOffice: string
  instructionsForFinalAction: string
  instructionType: string
}

export const RED_INSTRUCTIONS_LIST = [
  'For Dissemination',
  'For Compliance',
  'For Discussion',
  'For Appropriate Action',
  'For Record/File/Reference',
  'For Review/Evaluation/Recommendation',
]

export const FINAL_ACTION_OFFICES = [
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

export const RE_ROUTED_OFFICES = [
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