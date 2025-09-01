import { Document } from '@/types'; 

export const documentNumbers: string[] = [
  '2025-07-0506',
  '2025-07-0507',
  '2025-07-0508',
  '2025-07-0509',
  '2025-07-0510',
  '2025-07-0511',
  '2025-07-0512',
  '2025-07-0513',
  '2025-07-0514',
  '2025-07-0515',
  '2025-07-0516',
  '2025-07-0517',
  '2025-07-0518',
  '2025-07-0519',
  '2025-07-0520',
]

export const offices: string[] = [
  'RSCIS',
  'ADMIN',
  'CDD',
  'ED',
  'LPDD',
  'OARDTSTACKSFORCE',
  'OARDTSTACKSFORCE,LPDD',
]

export const subjects: string[] = [
  'MEMO DATED JULY 15, 2025 RE: QUARTERLY REPORT ON NGP IMPLEMENTATION',
  'EMAILED LETTER DTD 7/14/2025 RE: REQUEST FOR SITE INSPECTION',
  'MEMO DATED JULY 13, 2025 RE: MONTHLY ACCOMPLISHMENT REPORT',
  'EMAILED MEMO DTD 7/12/2025 RE: BUDGET UTILIZATION REPORT',
  'MEMO DATED JULY 11, 2025 RE: STATUS OF ONGOING PROJECTS',
  'EMAILED LETTER DTD 7/15/2025 RE: ENVIRONMENTAL COMPLIANCE CERTIFICATE',
  'MEMO DATED JULY 14, 2025 RE: WILDLIFE PERMIT APPLICATION',
  'EMAILED MEMO DTD 7/13/2025 RE: FOREST PROTECTION REPORT',
  'MEMO DATED JULY 12, 2025 RE: LAND TITLING UPDATE',
  'EMAILED LETTER DTD 7/11/2025 RE: MINING PERMIT REQUEST',
]

export const sampleDocuments: Document[] = (() => {
  const documents: Document[] = []
  const today = new Date()

  const todayInManila = new Date(today.toLocaleString("en-US", { timeZone: "Asia/Manila" }));
  todayInManila.setHours(0, 0, 0, 0); 

  const numDueToday = 8; 
  const numDueTomorrow = 12;
  const numDueFuture = 10; 
  const numDuePast = 5; 

  let idCounter = 1;

  for (let i = 0; i < numDueToday; i++) {
    documents.push({
      id: idCounter++,
      documentNo: `2025-07-${(1000 + idCounter).toString().slice(1)}`,
      subject: subjects[Math.floor(Math.random() * subjects.length)],
      finalActionOffice: offices[Math.floor(Math.random() * offices.length)],
      dueDate: new Date(todayInManila), // Set to today
    });
  }

  // Generate documents due tomorrow
  for (let i = 0; i < numDueTomorrow; i++) {
    const tomorrow = new Date(todayInManila);
    tomorrow.setDate(todayInManila.getDate() + 1);
    documents.push({
      id: idCounter++,
      documentNo: `2025-07-${(1000 + idCounter).toString().slice(1)}`,
      subject: subjects[Math.floor(Math.random() * subjects.length)],
      finalActionOffice: offices[Math.floor(Math.random() * offices.length)],
      dueDate: tomorrow, // Set to tomorrow
    });
  }

  // Generate documents due in the future (2 to 31 days from today)
  for (let i = 0; i < numDueFuture; i++) {
    const futureDate = new Date(todayInManila);
    const dayOffset = Math.floor(Math.random() * 30) + 2; // 2 to 31 days from today
    futureDate.setDate(todayInManila.getDate() + dayOffset);
    documents.push({
      id: idCounter++,
      documentNo: `2025-07-${(1000 + idCounter).toString().slice(1)}`,
      subject: subjects[Math.floor(Math.random() * subjects.length)],
      finalActionOffice: offices[Math.floor(Math.random() * offices.length)],
      dueDate: futureDate,
    });
  }

  // Generate documents due in the past (1 to 30 days ago)
  for (let i = 0; i < numDuePast; i++) {
    const pastDate = new Date(todayInManila);
    const dayOffset = Math.floor(Math.random() * 30) + 1; // 1 to 30 days ago
    pastDate.setDate(todayInManila.getDate() - dayOffset);
    documents.push({
      id: idCounter++,
      documentNo: `2025-07-${(1000 + idCounter).toString().slice(1)}`,
      subject: subjects[Math.floor(Math.random() * subjects.length)],
      finalActionOffice: offices[Math.floor(Math.random() * offices.length)],
      dueDate: pastDate,
    });
  }

  // Shuffle the documents to mix up due dates in the array
  for (let i = documents.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [documents[i], documents[j]] = [documents[j], documents[i]];
  }

  return documents
})()