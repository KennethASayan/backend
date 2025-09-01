<script setup lang="ts">
import { ref, onMounted, computed, getCurrentInstance } from 'vue'
import Dialog from '@/components/ui/Dialog/Dialog.vue'
import Button from '@/components/ui/button/Button.vue'
import Pagination from '@/components/ui/pagination/Pagination.vue'
import {
  PencilSquareIcon,
  TrashIcon,
  ArrowPathIcon,
  PlusIcon,
  EllipsisVerticalIcon,
  UserIcon,
  EnvelopeIcon,
  BuildingOfficeIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/solid'
import AddEditUserModal from '@/components/Dashboard/Admin/Manageusers/AddEditUserModal.vue'
import type { User, Office } from '@/types/user'
import { fetchUsersApi, deleteUserApi, resetPasswordApi } from '@/components/store/ManageUser'

// Get the global toast instance
const app = getCurrentInstance()
const toast = app?.appContext.config.globalProperties.$toast

// --- Reactive State ---
const users = ref<User[]>([])
const isAddEditModalOpen = ref(false)
const isDeleteConfirmModalOpen = ref(false)
const isResetPasswordConfirmModalOpen = ref(false)

const currentUser = ref<User | null>(null)

const searchTerm = ref('')

// Mobile menu states
const activeDropdown = ref<number | null>(null)

// --- Pagination State ---
const currentPage = ref(1)
const itemsPerPage = ref(5)

// --- Computed Properties ---
const filteredUsers = computed(() => {
  const lowerCaseSearchTerm = searchTerm.value.toLowerCase()
  return users.value.filter(
    (user) =>
      user.fullName.toLowerCase().includes(lowerCaseSearchTerm) ||
      user.name.toLowerCase().includes(lowerCaseSearchTerm) ||
      user.office.name.toLowerCase().includes(lowerCaseSearchTerm),
  )
})

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredUsers.value.slice(start, end)
})

const totalFilteredItems = computed(() => filteredUsers.value.length)

// --- Lifecycle Hook ---
onMounted(() => {
  fetchUsers()
})

// --- Functions ---

const fetchUsers = async () => {
  try {
    const data = await fetchUsersApi()
    users.value = data
  } catch (error) {
    console.error('Error fetching users:', error)
    // Corrected toast call
    toast?.addToast({ title: 'Fetch Error', description: 'Failed to load users.', type: 'error' })
  }
}

const openAddUserModal = () => {
  currentUser.value = null
  isAddEditModalOpen.value = true
}

const openEditUserModal = (user: User) => {
  currentUser.value = user
  isAddEditModalOpen.value = true
  activeDropdown.value = null
}

const handleUserSaved = () => {
  fetchUsers()
  // Corrected toast call
  toast?.addToast({ title: 'Success', description: 'User saved successfully!', type: 'success' })
}

const closeAddEditModal = () => {
  isAddEditModalOpen.value = false
  currentUser.value = null
}

const openDeleteConfirmModal = (user: User) => {
  currentUser.value = user
  isDeleteConfirmModalOpen.value = true
  activeDropdown.value = null
}

const closeDeleteConfirmModal = () => {
  isDeleteConfirmModalOpen.value = false
  currentUser.value = null
}

const handleDeleteUser = async () => {
  if (currentUser.value) {
    try {
      // ✅ Fix: Convert currentUser.value.id to string
      await deleteUserApi(String(currentUser.value.id))
      closeDeleteConfirmModal()
      fetchUsers()
    } catch (error: any) {
      console.error('Error deleting user:', error)
    }
  }
}

const openResetPasswordConfirmModal = (user: User) => {
  currentUser.value = user
  isResetPasswordConfirmModalOpen.value = true
  activeDropdown.value = null
}

const closeResetPasswordConfirmModal = () => {
  isResetPasswordConfirmModalOpen.value = false
  currentUser.value = null
}

const handleResetPassword = async () => {
  if (currentUser.value) {
    try {
      // ✅ Fix: Convert currentUser.value.id to string
      await resetPasswordApi(String(currentUser.value.id))
      closeResetPasswordConfirmModal()
    } catch (error: any) {
      console.error('Error resetting password:', error)
    }
  }
}

// Mobile dropdown functions
const toggleDropdown = (userId: number) => {
  activeDropdown.value = activeDropdown.value === userId ? null : userId
}

const closeDropdowns = () => {
  activeDropdown.value = null
}

// --- Pagination Event Handlers ---
const handlePageChange = (page: number) => {
  currentPage.value = page
}

const handleItemsPerPageChange = (items: number) => {
  itemsPerPage.value = items
  currentPage.value = 1
}
</script>

<template>
  <div @click="closeDropdowns">
    <div class="bg-white rounded-lg shadow p-4 md:p-2 font-sans">
      <div class="p-4 sm:p-6 lg:p-8 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Account Management</h1>
            <p class="text-sm text-gray-600">Manage user accounts and permissions</p>
          </div>

          <div class="flex-shrink-0">
            <Button
              @click="openAddUserModal"
              class="w-full sm:w-auto bg-[#099] hover:bg-[#009980] text-white px-4 py-2.5 rounded-lg flex items-center justify-center gap-2 font-medium shadow-sm transition-all duration-200 hover:shadow-md"
            >
              <PlusIcon class="h-4 w-4" />
              <span>Add Account</span>
            </Button>
          </div>
        </div>

        <div class="mt-6">
          <div class="max-w-md relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
            </div>
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Search by name, username, or office..."
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm placeholder-gray-500 transition-all duration-200 outline-none"
            />
          </div>
        </div>
      </div>

      <div class="p-4 sm:p-6 lg:p-8">
        <div class="hidden lg:block overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"
                  >
                    Full Name
                  </th>
                  <th
                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"
                  >
                    Username
                  </th>
                  <th
                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"
                  >
                    Email
                  </th>
                  <th
                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"
                  >
                    Office
                  </th>
                  <th
                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"
                  >
                    Status
                  </th>
                  <th
                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"
                  >
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="paginatedUsers.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                      <UserIcon class="h-12 w-12 text-gray-300 mb-4" />
                      <h3 class="text-lg font-medium text-gray-900 mb-1">No users found</h3>
                      <p class="text-sm text-gray-500">
                        Try adjusting your search criteria or add a new user.
                      </p>
                    </div>
                  </td>
                </tr>

                <tr
                  v-for="user in paginatedUsers"
                  :key="user.id"
                  class="hover:bg-gray-50 text-center transition-colors duration-150"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ user.fullName }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-600">{{ user.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-600">{{ user.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-600">{{ user.office.name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        user.status === 'Activated'
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800',
                      ]"
                    >
                      {{ user.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center justify-center gap-2">
                      <button
                        @click="openEditUserModal(user)"
                        class="inline-flex items-center p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-all duration-200"
                        title="Edit Account"
                      >
                        <PencilSquareIcon class="h-4 w-4" />
                      </button>
                      <button
                        @click="openResetPasswordConfirmModal(user)"
                        class="inline-flex items-center p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                        title="Reset Password"
                      >
                        <ArrowPathIcon class="h-4 w-4" />
                      </button>
                      <button
                        @click="openDeleteConfirmModal(user)"
                        class="inline-flex items-center p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200"
                        title="Delete Account"
                      >
                        <TrashIcon class="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="lg:hidden">
          <div v-if="paginatedUsers.length === 0" class="text-center py-12 px-4">
            <UserIcon class="h-16 w-16 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
            <p class="text-sm text-gray-500 mb-6">
              Try adjusting your search criteria or add a new user.
            </p>
            <Button
              @click="openAddUserModal"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium"
            >
              <PlusIcon class="h-4 w-4 mr-2 inline" />
              Add First User
            </Button>
          </div>

          <div class="space-y-4">
            <div
              v-for="user in paginatedUsers"
              :key="user.id"
              class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 shadow-sm hover:shadow-md transition-all duration-200"
            >
              <div class="flex items-start justify-between mb-4">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-3 mb-2">
                    <div
                      class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center"
                    >
                      <UserIcon class="h-5 w-5 text-gray-500" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">
                        {{ user.fullName }}
                      </h3>
                      <p class="text-sm text-gray-500">@{{ user.name }}</p>
                    </div>
                  </div>
                </div>

                <div class="flex items-center gap-2 ml-4 relative">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap',
                      user.status === 'Activated'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800',
                    ]"
                  >
                    {{ user.status }}
                  </span>

                  <div class="flex-shrink-0">
                    <button
                      @click.stop="toggleDropdown(user.id)"
                      class="inline-flex items-center p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                      :class="{ 'bg-gray-100 text-gray-600': activeDropdown === user.id }"
                    >
                      <EllipsisVerticalIcon class="h-5 w-5" />
                    </button>

                    <div
                      v-if="activeDropdown === user.id"
                      class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                      @click.stop
                    >
                      <button
                        @click="openEditUserModal(user)"
                        class="w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                      >
                        <PencilSquareIcon class="h-4 w-4 text-green-600" />
                        Edit Account
                      </button>
                      <button
                        @click="openResetPasswordConfirmModal(user)"
                        class="w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                      >
                        <ArrowPathIcon class="h-4 w-4 text-blue-600" />
                        Reset Password
                      </button>
                      <div class="border-t border-gray-100 my-1"></div>
                      <button
                        @click="openDeleteConfirmModal(user)"
                        class="w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-3 transition-colors duration-150"
                      >
                        <TrashIcon class="h-4 w-4" />
                        Delete Account
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <div class="flex items-center gap-3 text-sm">
                  <EnvelopeIcon class="h-4 w-4 text-gray-400 flex-shrink-0" />
                  <span class="text-gray-900 truncate">{{ user.email }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                  <BuildingOfficeIcon class="h-4 w-4 text-gray-400 flex-shrink-0" />
                  <span class="text-gray-900 truncate">{{ user.office.name }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8 border-gray-200 pt-6 overflow-x-auto lg:overflow-visible">
              <Pagination
        :total-items="Number(totalFilteredItems)"
        :initial-page="currentPage"
        :initial-items-per-page="itemsPerPage"
        @page-change="handlePageChange"
        @items-per-page-change="handleItemsPerPageChange"
      />
        </div>
      </div>
    </div>

    <AddEditUserModal
      :is-open="isAddEditModalOpen"
      :current-user="currentUser"
      @close="closeAddEditModal"
      @user-saved="handleUserSaved"
    />
    <Dialog
      v-for="(modal, index) in [
        {
          isOpen: isDeleteConfirmModalOpen,
          title: 'Delete User Account',
          message: `Are you sure you want to delete ${currentUser?.fullName}'s account?`,
          onClose: closeDeleteConfirmModal,
          onConfirm: handleDeleteUser,
          confirmText: 'Delete Account',
          icon: 'trash',
        },
        {
          isOpen: isResetPasswordConfirmModalOpen,
          title: 'Reset Password',
          message: `Are you sure you want to reset the password for ${currentUser?.name}?`,
          onClose: closeResetPasswordConfirmModal,
          onConfirm: handleResetPassword,
          confirmText: 'Reset Password',
          icon: 'refresh',
        },
      ]"
      :key="index"
      :is-open="modal.isOpen"
      @close="modal.onClose"
      :title="modal.title"
      type="modal"
      :close-on-overlay-click="false"
      class="w-11/12 max-w-md mx-auto"
    >
      <div class="p-2">
        <p class="text-gray-600 text-center leading-relaxed mb-10 mt-3">{{ modal.message }}</p>
        <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
          <Button
            @click="modal.onClose"
            type="button"
            variant="danger"
            class="w-full sm:w-auto px-4 py-2.5 rounded-lg text-white font-medium transition-colors duration-200 focus:ring-red-500"
          >
            Cancel
          </Button>

          <Button
            @click="modal.onConfirm"
            type="button"
            variant="success"
            class="w-full sm:w-auto px-4 py-2.5 rounded-lg text-white font-medium transition-colors duration-200 focus:ring-green-500"
          >
            {{ modal.confirmText }}
          </Button>
        </div>
      </div>
    </Dialog>
  </div>
</template>
