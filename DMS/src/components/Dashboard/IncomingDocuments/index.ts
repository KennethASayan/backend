export interface Document {
  id: number;
  number: string;
  subject: string;
  documentDeadline: string;
  artaDeadline: string;
  status: 'pending' | 'complete';
}