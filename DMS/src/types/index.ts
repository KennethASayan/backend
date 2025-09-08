import type { FunctionalComponent, SVGAttributes } from 'vue'
import type { UserIcon } from 'lucide-vue-next' // Assuming you're also using Lucide icons

// General type for Heroicons
export type HeroIcon = FunctionalComponent<SVGAttributes>

// Dashboard Stats Data Interface
export interface DashboardStat {
  id: number
  name: string
  value: string
  description: string
  icon: HeroIcon
  bgColorClass: string
  iconColorClass: string
  trend: string // e.g., "+12%"
}

// Recent Activity Data Interface
export interface RecentActivity {
  id: number
  document: string // e.g., "Document #2025-07-0485"
  action: string // e.g., "was updated"
  time: string // e.g., "2 minutes ago"
}

// Quick Action Data Interface
export interface QuickAction {
  id: number
  name: string
  icon: HeroIcon
  action: () => void // A function that takes no arguments and returns nothing
  iconColorClass: string // Add this new property
}

// Due Document Data Interface
// Renamed from DueDocument for consistency with your existing Document interface if it's related
// If these are different types of documents, keep DueDocument.
// For now, I'll use DueDocument as per your original request to map directly.
export interface DueDocument {
  id: number
  date: string // Consider using Date objects if you plan to do date comparisons/formatting
  priority?: string // Optional property, not always present in your dummy data
  title: string
  finalActionOffices: string[] // Array of strings (e.g., ['CDO', 'DENR'])
  status: 'Due Today' | 'Due Tomorrow' | 'Due Soon' // Union type for specific string values
}

// Your existing Document interface (if it's distinct from DueDocument)
export interface Document {
  id: number
  documentNo: string
  subject: string
  finalActionOffice: string
  dueDate: Date // Note: Date object, not string
}

export type FilterType = 'all' | 'today' | 'tomorrow'
export type SearchableFields = 'documentNo' | 'subject' | 'finalActionOffice'

export type Severity = 'primary' | 'success' | 'warning' | 'danger' | 'info' | 'secondary'
export type LucideIcon = typeof UserIcon

// Add Pagination interfaces here
export interface PaginationProps {
  totalItems: number
  initialPage?: number
  initialItemsPerPage?: number
}

export interface PaginationEmits {
  (e: 'page-change', page: number): void
  (e: 'items-per-page-change', itemsPerPage: number): void
}
