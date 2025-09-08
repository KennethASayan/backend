export interface Office {
  id: string;
  name: string;
  code: string;
}

export interface User {
  id: string | number;
  department?: string; // Make this required, not optional
  name: string;
  user_dept: string;
  email: string;
  status: boolean | string;
  fullName: string;
  division: string;
  avatar?: string;
  role?: string;
  office?: Office;
  password?: string;
  userRole?: string;
}
