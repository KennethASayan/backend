<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/components/store/authStore'
import { isSidebarMinimized, isSidebarVisible, toggleSidebarVisibility, checkScreenSize } from './sidebarState'


import {
  HomeIcon,
  InboxStackIcon,
  DocumentDuplicateIcon,
  CurrencyDollarIcon,
  ClipboardDocumentListIcon,
  CheckBadgeIcon,
  DocumentChartBarIcon,
  Bars3Icon,
  XMarkIcon,
  UsersIcon,
  DocumentTextIcon,
  ArrowDownTrayIcon,
  ClockIcon,
  ArrowRightCircleIcon,
  BuildingOfficeIcon,
} from '@heroicons/vue/24/outline'

import { ChevronDownIcon, ChevronUpIcon } from '@heroicons/vue/24/solid'

const route = useRoute()
const auth = useAuthStore()

const openDropdown = ref<string | null>(
  localStorage.getItem('openDropdown') || null
)

interface NavItem {
  name: string
  href?: string
  icon: any
  hasDropdown?: boolean
  children?: NavItem[]
}

const navigation = ref<NavItem[]>([
  { name: 'Dashboard', href: '/Dashboard', icon: HomeIcon },
  { name: 'Manage Users', href: '/Manage-users', icon: UsersIcon },
  { name: 'Incoming Documents ', href: '/incoming-documents', icon: InboxStackIcon },
  {
    name: 'Internal Documents(For Approval)',
    href: '/internal-documents',
    icon: DocumentDuplicateIcon,
  },
  { name: 'Financial Documents', href: '/Financial-documents', icon: CurrencyDollarIcon },
  {
    name: 'Internal Documents (Activity Reports)',
    href: '/Activity-reports',
    icon: ClipboardDocumentListIcon,
  },
  { name: 'Acted Cases', href: '/Acted-cases', icon: CheckBadgeIcon },
  {
    name: 'Generate Reports',
    icon: DocumentChartBarIcon,
    hasDropdown: true,
    children: [
      { name: 'SUMMARY OF INCOMING DOCUMENTS ', href: '/Generate-reports/Summary-Incoming-Documents', icon: DocumentTextIcon },
      { name: 'INCOMING DOCUMENTS RECEIVED BY ROUTING OFFICE ', href: '/Generate-reports/Incoming-Documents-Received', icon: BuildingOfficeIcon },
      { name: 'TIME AND MOTION OF INCOMING DOCUMENTS REFERRAL ', href: '/Generate-reports/Time-Motion-Incoming-Documents-Referral', icon: ClockIcon },
      { name: 'TIME AND MOTION OF INCOMING DOCUMENTS REFERRED TO FINAL ACTION OFFICE', href: '/Generate-reports/Time-Motion-Incoming-Documents-Final-Action', icon: ArrowRightCircleIcon },
      { name: 'DOWNLOAD SUMMARY', href: '/Generate-reports/Download-Summary', icon: ArrowDownTrayIcon },
    ],
  },
])

const isCurrentRoute = (href: string) => {
  return route.path === href
}

const toggleDropdown = (itemName: string) => {
  const newDropdownState = openDropdown.value === itemName ? null : itemName
  openDropdown.value = newDropdownState
  if (newDropdownState) {
    localStorage.setItem('openDropdown', newDropdownState)
  } else {
    localStorage.removeItem('openDropdown')
  }
}

const closeDropdown = () => {
  openDropdown.value = null;
  localStorage.removeItem('openDropdown');
};

watch(route, () => {
  if (window.innerWidth < 768) {
    isSidebarVisible.value = false;
  }
  const parentOfCurrentRoute = navigation.value.find(navItem =>
    navItem.children?.some(child => isCurrentRoute(child.href || ''))
  );
  if (parentOfCurrentRoute) {
    openDropdown.value = parentOfCurrentRoute.name;
    localStorage.setItem('openDropdown', parentOfCurrentRoute.name);
  } else {
    closeDropdown();
  }
});

onMounted(() => {
  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);
  if (auth.isAuthenticated.value) {
    isSidebarVisible.value = true
  }
  const parentOfCurrentRoute = navigation.value.find(navItem =>
    navItem.children?.some(child => isCurrentRoute(child.href || ''))
  );
  if (parentOfCurrentRoute) {
    openDropdown.value = parentOfCurrentRoute.name;
  }
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', checkScreenSize);
});
</script>

<template>
  <div class="flex flex-col md:flex-row bg-gray-100">
    <div
      v-show="isSidebarVisible"
      class="fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity duration-300"
      @click="toggleSidebarVisibility"
    ></div>

    <aside
      :class="[
        'fixed md:relative h-full overflow-y-auto bg-gray-800 transition-all duration-300 ease-in-out z-50 md:z-0 top-0 left-0',
       isSidebarVisible ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
    isSidebarMinimized ? 'w-16 md:w-20' : 'w-4/5 max-w-xs md:w-80',
      ]"
    >
      <div class="sticky top-0 bg-gray-800 z-10">
        <div class="p-4 border-b border-gray-700 flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <img src="@/assets/DENRLOGO.png" alt="DENR Logo" class="h-8 w-8 md:h-10 md:w-10" />
            <div
              v-if="!isSidebarMinimized"
              class="flex flex-col text-white text-sm md:text-base font-normal leading-tight"
            >
              <span>Document Monitoring System</span>
            </div>
          </div>
          <button
            @click="toggleSidebarVisibility"
            class="md:hidden p-2 rounded-lg hover:bg-gray-700"
            aria-label="Close menu"
          >
            <XMarkIcon class="h-6 w-6 text-white" />
          </button>
        </div>
      </div>

      <nav class="px-3 py-10">
        <div class="space-y-4">
          <template v-for="item in navigation" :key="item.name">
            <div v-if="item.hasDropdown">
              <button
                @click="toggleDropdown(item.name)"
                :class="[
                  'w-full flex items-center px-2 py-2 text-sm font-medium rounded-lg text-gray-200 hover:bg-gray-200 hover:text-gray-800 transition-colors duration-150 ease-in-out',
                  openDropdown === item.name ? 'bg-gray-200 text-gray-800' : '',
                ]"
                :aria-expanded="openDropdown === item.name ? 'true' : 'false'"
              >
                <component
                  :is="item.icon"
                  :class="['flex-shrink-0 h-5 w-5 md:h-6 md:w-6', openDropdown === item.name ? 'text-gray-800' : 'text-gray-400 group-hover:text-gray-800']"
                  aria-hidden="true"
                />
                <span v-if="!isSidebarMinimized" class="ml-3">{{ item.name }}</span>
                <component
                  :is="openDropdown === item.name ? ChevronUpIcon : ChevronDownIcon"
                  v-if="!isSidebarMinimized"
                  class="ml-auto h-4 w-4 text-gray-400"
                  aria-hidden="true"
                />
              </button>
              <div v-show="openDropdown === item.name" :class="{'pl-8': !isSidebarMinimized}" class="mt-2 space-y-2">
                <router-link
                  v-for="child in item.children"
                  :key="child.name"
                  :to="child.href"
                  :class="[
                    'group child-link flex items-center justify-between px-2 py-2 text-sm font-medium rounded-lg text-gray-200 hover:bg-gray-200 hover:text-gray-800 transition-colors duration-150 ease-in-out',
                    isCurrentRoute(child.href || '') ? 'bg-gray-100 text-[black] rounded-2 border-r-4 border-[#4ade80]' : '',
                  ]"
                  @click="isSidebarVisible = false"
                >
                  <div class="flex items-center">
                    <component
                      :is="child.icon"
                      :class="['flex-shrink-0 h-5 w-5 md:h-6 md:w-6', isCurrentRoute(child.href || '') ? 'text-[black]' : 'text-gray-400 group-hover:text-gray-800']"
                      aria-hidden="true"
                    />
                    <span v-if="!isSidebarMinimized" class="ml-3">{{ child.name }}</span>
                  </div>
                </router-link>
              </div>
            </div>
            <router-link
              v-else
              :to="item.href"
              :class="[
                'group flex items-center justify-between px-2 py-2 text-sm font-medium rounded-lg text-gray-200 hover:bg-gray-200 hover:text-gray-800 transition-colors duration-150 ease-in-out',
                isCurrentRoute(item.href || '') ? 'bg-gray-100 text-[black] rounded-2 border-r-4 border-[#4ade80]' : '',
              ]"
              @click="isSidebarVisible = false; closeDropdown()"
            >
              <div class="flex items-center">
                <component
                  :is="item.icon"
                  :class="['flex-shrink-0 h-5 w-5 md:h-6 md:w-6', isCurrentRoute(item.href || '') ? 'text-[black]' : 'text-gray-400 group-hover:text-gray-800']"
                  aria-hidden="true"
                />
                <span v-if="!isSidebarMinimized" class="ml-3">{{ item.name }}</span>
              </div>
            </router-link>
          </template>
        </div>
      </nav>
    </aside>
  </div>
</template>
