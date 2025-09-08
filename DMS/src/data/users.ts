// src/data/users.ts
import type { User, Office } from '@/types/user';

export const offices: Office[] = [
  { id: 'penro-misocc', name: 'PENRO MisOcc', code: 'PMO' },
];

const getOfficeByName = (name: string): Office => {
  const foundOffice = offices.find(o => o.name === name);
  if (!foundOffice) {
    console.warn(`Office '${name}' not found in dummy offices. Returning a default/placeholder.`);
    return { id: 'unknown', name: name, code: 'UNKNOWN' };
  }
  return foundOffice;
};

export const Users: User[] = [
  {
    id: 'user-001',
    fullName: 'Kenneth Angel M. Sayan', // Added fullName
    username: 'KennethSayan',
    email: 'sayankenneth@gmail.com',
    role: 'Administrator',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'PENR Office',
    userRole: 'Administrator',
    status: 'Activated',
  },
  {
    id: 'user-002',
    fullName: 'Ricky Boy Diez', // Added fullName
    username: 'tt.borja',
    email: 'tt.borja@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'LGU Coordination',
    userRole: 'Staff',
    status: 'Activated',
  },
  {
    id: 'user-003',
    fullName: 'Alister A. Jaramilla', // Added fullName
    username: 'alister.j',
    email: 'alister.j@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'HR Section',
    userRole: 'Staff',
    status: 'Activated',
  },
  {
    id: 'user-004',
    fullName: 'Norhata T. Imam', // Added fullName
    username: 'norhata.i',
    email: 'norhata.i@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'Legal Division',
    userRole: 'Staff',
    status: 'Activated',
  },
  {
    id: 'user-005',
    fullName: 'Vilma C. Navaja', // Added fullName
    username: 'vilma.n',
    email: 'vilma.n@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'Planning Division',
    userRole: 'Staff',
    status: 'Activated',
  },
  {
    id: 'user-006',
    fullName: 'Orolin V. Abatoa', // Added fullName
    username: 'orolin.a',
    email: 'orolin.a@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'Enforcement Division',
    userRole: 'Staff',
    status: 'Activated',
  },
  {
    id: 'user-007',
    fullName: 'Re A. Delacruz', // Added fullName
    username: 're.dela',
    email: 're.dela@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'Records Section',
    userRole: 'Staff',
    status: 'Deactivated',
  },
  {
    id: 'user-008',
    fullName: 'Alice M. Smith', // Added fullName
    username: 'alice.s',
    email: 'alice.s@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'Admin',
    userRole: 'Staff',
    status: 'Activated',
  },
  {
    id: 'user-009',
    fullName: 'Bob K. Johnson', // Added fullName
    username: 'bob.j',
    email: 'bob.j@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'Finance',
    userRole: 'Staff',
    status: 'Deactivated',
  },
  {
    id: 'user-010',
    fullName: 'Charlie L. Brown', // Added fullName
    username: 'charlie.b',
    email: 'charlie.b@example.com',
    role: 'Administrator',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'IT',
    userRole: 'Administrator',
    status: 'Activated',
  },
  {
    id: 'user-011',
    fullName: 'Diana P. Miller', // Added fullName
    username: 'diana.m',
    email: 'diana.m@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'HR',
    userRole: 'Staff',
    status: 'Activated',
  },
  {
    id: 'user-012',
    fullName: 'Frank D. White', // Added fullName
    username: 'frank.w',
    email: 'frank.w@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'Logistics',
    userRole: 'Staff',
    status: 'Activated',
  },
  {
    id: 'user-013',
    fullName: 'Grace E. Black', // Added fullName
    username: 'grace.b',
    email: 'grace.b@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'Procurement',
    userRole: 'Staff',
    status: 'Deactivated',
  },
  {
    id: 'user-014',
    fullName: 'Henry F. Green', // Added fullName
    username: 'henry.g',
    email: 'henry.g@example.com',
    role: 'Administrator',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'QA',
    userRole: 'Administrator',
    status: 'Activated',
  },
  {
    id: 'user-015',
    fullName: 'Ivy G. Purple', // Added fullName
    username: 'ivy.p',
    email: 'ivy.p@example.com',
    role: 'Staff',
    office: getOfficeByName('PENRO MisOcc'),
    division: 'Support',
    userRole: 'Staff',
    status: 'Activated',
  },
];
