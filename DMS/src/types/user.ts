// src/types/user.ts

export interface Office {
  id: string;
  name: string;
  code: string;
}

export interface User {
  id: string | number;
  fullName: string; // New: Full name for display and input
  firstName?: string; // Optional: Can be used for backend storage if needed
  middleName?: string; // Optional
  lastName?: string; // Optional
  name: string;
  email: string;
  avatar?: string;
  role: string;
  office: Office;
  password?: string;
  status: 'Activated' | 'Deactivated';
  division: string;
  userRole: string;
}
