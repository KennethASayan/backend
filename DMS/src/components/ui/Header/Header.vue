<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import { toggleSidebarVisibility } from '@/components/ui/Sidebar/sidebarState'
import { useAuthStore } from '@/components/store/authStore'
import {
  UserCircleIcon,
  ChevronDownIcon,
  ChevronUpIcon,
  MagnifyingGlassIcon,
  ChevronRightIcon,
  Bars3Icon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import DropdownMenu from '@/components/ui/dropdownmenu/DropDownMenu.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const isMobileNavOpen = ref(false)
const isDropdownOpen = ref(false)
const isMobileMenuOpen = ref(false)

// Initialize auth state when component mounts
onMounted(async () => {
  // Initialize auth store if not already done
  if (!authStore.isAuthenticated.value) {
    await authStore.initAuth()
  }
  
  // Set up datetime updater
  updateDateTime()
  intervalId = setInterval(updateDateTime, 1000)
  
  // Optional: Periodically refresh user data to ensure it's current
  // This helps if user data changes in another tab/window
  const refreshInterval = setInterval(async () => {
    if (authStore.isAuthenticated.value) {
      await authStore.refreshUserData()
    }
  }, 5 * 60 * 1000) // Refresh every 5 minutes
  
  // Clean up interval on unmount
  onBeforeUnmount(() => {
    clearInterval(refreshInterval)
  })
})

// Get user department from auth store with better fallback
const userDepartment = computed(() => {
  const user = authStore.user.value
  if (!user) return 'User'
  
  // Try different possible field names for department
  return user.department || user.user_dept || user.division || 'User'
})

// Get user full name for display with better fallback
const userFullName = computed(() => {
  const user = authStore.user.value
  if (!user) return 'User'
  
  // Try different possible field names for full name
  return user.fullName || user.name || user.displayName || 'User'
})

// Watch for authentication changes to handle login/logout
watch(
  () => authStore.isAuthenticated.value,
  async (isAuth) => {
    if (!isAuth) {
      // User is no longer authenticated, redirect to login
      await router.push('/LandingPage')
    }
  }
)

// Watch for user data changes to ensure reactivity
watch(
  () => authStore.user.value,
  (newUser, oldUser) => {
    if (newUser && newUser !== oldUser) {
      console.log('User data updated in header:', newUser)
    }
  },
  { deep: true }
)

const toggleMobileNav = () => {
  isMobileNavOpen.value = !isMobileNavOpen.value
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

interface BreadcrumbItem {
  name: string
  path?: string
  isCurrentPage: boolean
}

const breadcrumbs = computed((): BreadcrumbItem[] => {
  const documentName = route.params.documentName || route.query.name
  const items: BreadcrumbItem[] = []

  switch (route.name) {
    case 'Dashboard':
      items.push({
        name: 'Dashboard',
        isCurrentPage: true,
      })
      break

    case 'ActivityReports':
      items.push({
        name: 'Internal Documents',
        isCurrentPage: true,
      })
      break

    case 'ManageUsers':
      items.push({
        name: 'Manage Users',
        isCurrentPage: true,
      })
      break

    case 'IncomingDocuments':
      items.push({
        name: 'Incoming Documents',
        path: documentName ? '/incoming-documents' : undefined,
        isCurrentPage: !documentName,
      })
      if (documentName) {
        items.push({
          name: documentName as string,
          isCurrentPage: true,
        })
      }
      break

    case 'InternalDocuments':
      items.push({
        name: 'Internal Documents',
        path: documentName ? '/internal-documents' : undefined,
        isCurrentPage: !documentName,
      })
      if (documentName) {
        items.push({
          name: documentName as string,
          isCurrentPage: true,
        })
      }
      break

    case 'FinancialDocuments':
      items.push({
        name: 'Financial Documents',
        path: documentName ? '/financial-documents' : undefined,
        isCurrentPage: !documentName,
      })
      if (documentName) {
        items.push({
          name: documentName as string,
          isCurrentPage: true,
        })
      }
      break

    case 'ActedCases':
      items.push({
        name: 'Acted Cases',
        isCurrentPage: true,
      })
      break

    case 'GenerateReports':
    case 'SummaryIncomingDocuments':
    case 'IncomingDocumentsReceived':
    case 'TimeMotionIncomingDocumentsReferral':
    case 'TimeMotionIncomingDocumentsFinalAction':
    case 'DownloadSummary':
      items.push({
        name: 'Generate Reports',
        path: route.name === 'GenerateReports' ? undefined : '/Generate-reports',
        isCurrentPage: route.name === 'GenerateReports',
      })
      if (route.name === 'SummaryIncomingDocuments') {
        items.push({
          name: 'Summary Incoming Documents',
          isCurrentPage: true,
        })
      } else if (route.name === 'IncomingDocumentsReceived') {
        items.push({
          name: 'Incoming Documents Received',
          isCurrentPage: true,
        })
      } else if (route.name === 'TimeMotionIncomingDocumentsReferral') {
        items.push({
          name: 'Time Motion Incoming Documents Referral',
          isCurrentPage: true,
        })
      } else if (route.name === 'TimeMotionIncomingDocumentsFinalAction') {
        items.push({
          name: 'Time Motion Incoming Documents Final Action',
          isCurrentPage: true,
        })
      } else if (route.name === 'DownloadSummary') {
        items.push({
          name: 'Download Summary',
          isCurrentPage: true,
        })
      }
      break

    case 'SearchResults':
      return [
        {
          name: 'Search Results',
          isCurrentPage: true,
        },
      ]

    case 'ProfileSettings':
      items.push({
        name: 'Settings',
        path: '/settings',
        isCurrentPage: false,
      })
      items.push({
        name: 'Profile',
        isCurrentPage: true,
      })
      break

    case 'Password':
      items.push({
        name: 'Settings',
        path: '/settings',
        isCurrentPage: false,
      })
      items.push({
        name: 'Change Password',
        isCurrentPage: true,
      })
      break

    case 'AppearanceSettings':
      items.push({
        name: 'Settings',
        path: '/settings',
        isCurrentPage: false,
      })
      items.push({
        name: 'Appearance',
        isCurrentPage: true,
      })
      break

    default:
      items.push({
        name: 'Dashboard',
        isCurrentPage: true,
      })
      break
  }

  return items
})

const currentDateTime = ref('')
let intervalId: NodeJS.Timeout | undefined

const updateDateTime = () => {
  const now = new Date()
  const optionsDate: Intl.DateTimeFormatOptions = {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }
  const optionsTime: Intl.DateTimeFormatOptions = {
    hour: 'numeric',
    minute: 'numeric',
    hour12: true,
  }
  const dateString = now.toLocaleDateString('en-US', optionsDate)
  const timeString = now.toLocaleTimeString('en-US', optionsTime)
  currentDateTime.value = `Today is ${dateString}, Time is ${timeString}`
}

onBeforeUnmount(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
})

const userMenuItems = [
  { label: 'Settings', action: 'navigate', path: '/settings', class: 'text-gray-700' },
  { label: 'Logout', action: 'logout', class: 'text-red-600' },
]

const handleMenuSelect = async (item: (typeof userMenuItems)[0]) => {
  console.log(`Selected: ${item.label}`)
  
  if (item.action === 'logout') {
    try {
      // Close dropdowns first
      isDropdownOpen.value = false
      isMobileMenuOpen.value = false
      
      // Use the auth store logout method
      await authStore.logout(router)
    } catch (error) {
      console.error('Logout failed:', error)
      // Even if logout API fails, clear local state and redirect
      authStore.clearAuth()
      await router.push('/LandingPage')
    }
  } else if (item.action === 'refresh') {
    // Refresh user data from database
    isDropdownOpen.value = false
    isMobileMenuOpen.value = false
    
    try {
      const success = await authStore.refreshUserData()
      if (success) {
        console.log('User data refreshed successfully')
      } else {
        console.warn('Failed to refresh user data')
      }
    } catch (error) {
      console.error('Error refreshing user data:', error)
    }
  } else if (item.action === 'navigate' && item.path) {
    isDropdownOpen.value = false
    isMobileMenuOpen.value = false
    await router.push(item.path)
  }
}

const searchQueryInput = ref('')

const handleSearch = () => {
  if (searchQueryInput.value.trim()) {
    router.push({
      name: 'SearchResults',
      query: { q: searchQueryInput.value.trim() },
    })
    searchQueryInput.value = ''
    isMobileMenuOpen.value = false
  }
}

const navigateToBreadcrumb = (item: BreadcrumbItem) => {
  if (item.path && !item.isCurrentPage) {
    router.push(item.path)
  }
}

const handleDropdownToggle = (isOpen: boolean) => {
  isDropdownOpen.value = isOpen
}

// Check authentication status periodically
const checkAuthStatus = async () => {
  if (authStore.isAuthenticated.value && authStore.token.value) {
    try {
      await authStore.validateSession()
    } catch (error) {
      console.error('Session validation failed:', error)
      // Session is invalid, logout user
      await authStore.logout(router)
    }
  }
}

// Check auth status every 10 minutes
onMounted(() => {
  const authCheckInterval = setInterval(checkAuthStatus, 10 * 60 * 1000)
  
  onBeforeUnmount(() => {
    clearInterval(authCheckInterval)
  })
})
</script>

<template>
  <header class="bg-white shadow-md sticky top-0 z-40">
    <div class="flex items-center justify-between px-4 py-3">
      <div class="flex items-center space-x-3 flex-1 min-w-0">
        <button
          @click="toggleSidebarVisibility"
          class="p-1 rounded-md hover:bg-gray-100 transition-colors duration-200 flex items-center justify-center flex-shrink-0"
        >
          <Icon icon="ph:sidebar-simple-light" class="w-6 h-6 text-gray-600" />
        </button>

        <div class="flex items-center min-w-0 flex-1">
          <nav class="flex lg:hidden items-center space-x-1 text-sm font-medium text-gray-600 min-w-0">
            <template v-if="breadcrumbs.length > 1">
              <button
                v-if="breadcrumbs[breadcrumbs.length - 2]?.path"
                @click="navigateToBreadcrumb(breadcrumbs[breadcrumbs.length - 2])"
                class="text-gray-500 hover:text-gray-700 transition-colors duration-150 truncate max-w-30"
                :title="breadcrumbs[breadcrumbs.length - 2]?.name"
              >
                {{ breadcrumbs[breadcrumbs.length - 2]?.name }}
              </button>
              <ChevronRightIcon class="w-3 h-3 text-gray-400 flex-shrink-0" />
            </template>
            <span class="font-semibold text-gray-900 truncate">
              {{ breadcrumbs[breadcrumbs.length - 1]?.name || 'Dashboard' }}
            </span>
          </nav>

          <nav class="hidden lg:flex items-center space-x-1 text-sm font-medium text-gray-600 min-w-0">
            <template v-for="(item, index) in breadcrumbs" :key="index">
              <ChevronRightIcon
                v-if="index > 0"
                class="w-4 h-4 text-gray-400 flex-shrink-0"
              />

              <button
                v-if="item.path && !item.isCurrentPage"
                @click="navigateToBreadcrumb(item)"
                class="text-gray-600 hover:text-gray-900 transition-colors duration-150 truncate max-w-40"
                :title="item.name"
              >
                {{ item.name }}
              </button>

              <span
                v-else
                class="font-semibold truncate max-w-50"
                :class="item.isCurrentPage ? 'text-gray-900' : 'text-gray-600'"
                :title="item.name"
              >
                {{ item.name }}
              </span>
            </template>
          </nav>
        </div>
      </div>

      <div class="hidden lg:block flex-1 max-w-md mx-8">
        <form @submit.prevent="handleSearch" class="relative">
          <MagnifyingGlassIcon
            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
          />
          <input
            v-model="searchQueryInput"
            type="text"
            placeholder="Search documents..."
            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
          />
        </form>
      </div>

      <div class="flex items-center space-x-3">
        <div class="hidden lg:flex items-center space-x-4">
          <span class="text-sm text-gray-600 whitespace-nowrap">
            {{ currentDateTime }}
          </span>
          <div class="flex items-center space-x-2">
            <UserCircleIcon class="w-6 h-6 text-gray-600" />
            <DropdownMenu
              content-class="w-48 z-50"
              buttonClass="flex z-[9999] items-center space-x-2 text-sm text-gray-700 hover:text-gray-900 transition-colors duration-200"
              @dropdown-toggle="handleDropdownToggle"
            >
              <template #button-content>
                <span class="text-sm font-bold text-gray-600" :title="userFullName">
                  {{ userDepartment }}
                </span>
                <ChevronUpIcon
                  v-if="isDropdownOpen"
                  class="w-4 h-4 transition-transform duration-200"
                />
                <ChevronDownIcon
                  v-else
                  class="w-4 h-4 transition-transform duration-200"
                />
              </template>

              <div class="py-1">
                <!-- Display user info in dropdown -->
                <div class="px-4 py-2 text-xs text-gray-500 border-b border-gray-100">
                  <div class="font-medium text-gray-700">{{ userFullName }}</div>
                  <div>{{ userDepartment }}</div>
                </div>
                <button
                  v-for="item in userMenuItems"
                  :key="item.label"
                  :class="[item.class, 'block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition-colors duration-200']"
                  @click="handleMenuSelect(item)"
                >
                  {{ item.label }}
                </button>
              </div>
            </DropdownMenu>
          </div>
        </div>

        <button
          @click="toggleMobileMenu"
          class="lg:hidden p-2 rounded-md hover:bg-gray-100 transition-colors duration-200"
        >
          <Bars3Icon v-if="!isMobileMenuOpen" class="w-6 h-6 text-gray-600" />
          <XMarkIcon v-else class="w-6 h-6 text-gray-600" />
        </button>
      </div>
    </div>

    <div
      v-if="isMobileMenuOpen"
      class="lg:hidden border-t border-gray-200 bg-white shadow-lg"
    >
      <div class="px-4 py-3 space-y-4">
        <form @submit.prevent="handleSearch" class="relative">
          <MagnifyingGlassIcon
            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
          />
          <input
            v-model="searchQueryInput"
            type="text"
            placeholder="Search documents..."
            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
          />
        </form>

        <div class="text-sm text-gray-600 text-center py-2 border-b border-gray-100">
          {{ currentDateTime }}
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <UserCircleIcon class="w-8 h-8 text-gray-600" />
              <div class="flex flex-col">
                <span class="text-sm font-bold text-gray-600">
                  {{ userDepartment }}
                </span>
                <span class="text-xs text-gray-500">
                  {{ userFullName }}
                </span>
              </div>
            </div>
          </div>

          <div class="space-y-1 pt-2 border-t border-gray-100">
            <button
              v-for="item in userMenuItems"
              :key="item.label"
              :class="[item.class, 'block w-full text-left px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100 transition-colors duration-200']"
              @click="handleMenuSelect(item)"
            >
              {{ item.label }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>