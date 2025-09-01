<script setup lang="ts">
import { ref, watch, defineProps, defineEmits, computed } from 'vue'
import Dialog from '@/components/ui/Dialog/Dialog.vue'
import Button from '@/components/ui/button/Button.vue'
import type { User, Office } from '@/types/user'
import { createUserApi, updateUserApi } from '@/components/store/ManageUser'
import { offices } from '@/data/users'
import toast from '@/components/ui/message/toast'

const props = defineProps<{
  isOpen: boolean
  currentUser: User | null
}>()

const emit = defineEmits(['close', 'user-saved'])
const isLoading = ref(false)

// Initialize form with default values
const initialFormState = {
  fullName: '',
  name: '',
  email: '',
  office: { id: '', name: '', code: '' },
  division: '',
  userRole: '',
  status: 'Activated' as const,
  role: '',
}

const form = ref<Partial<User>>(initialFormState)
const isNewUser = computed(() => !props.currentUser)

// Office helper function
const getOfficeById = (id: string): Office | undefined => {
  return offices.find((o) => o.id === id)
}

// Watch for modal open/close to reset form
watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      if (props.currentUser) {
        // Edit mode - populate form with user data
        const { password, ...userData } = props.currentUser
        form.value = { ...userData }
        const selectedOffice = getOfficeById(String(props.currentUser.office.id))
        if (selectedOffice) {
          form.value.office = selectedOffice
        }
      } else {
        // Create mode - reset form
        form.value = { ...initialFormState }
      }
    }
  },
  { immediate: true }
)

// Form validation
const validateForm = () => {
  const requiredFields = {
    fullName: 'Full Name',
    name: 'Username',
    email: 'Email',
    'office.id': 'Office',
    division: 'Division',
    userRole: 'User Role',
    status: 'Status'
  }

  for (const [field, label] of Object.entries(requiredFields)) {
    const value = field.includes('.')
      ? field.split('.').reduce((obj, key) => obj?.[key], form.value)
      : form.value[field]

    if (!value) {
      toast.addToast({
        title: 'Validation Error',
        description: `${label} is required.`,
        type: 'error'
      })
      return false
    }
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.value.email || '')) {
    toast.addToast({
      title: 'Validation Error',
      description: 'Please enter a valid email address.',
      type: 'error'
    })
    return false
  }

  return true
}

// Save user handler
const handleSaveUser = async () => {
  if (!validateForm()) return

  isLoading.value = true
  try {
    const userData = {
      fullName: form.value.fullName,
      name: form.value.name,
      email: form.value.email,
      division: form.value.division,
      status: form.value.status,
      office: form.value.office,
      userRole: form.value.userRole
    }

    if (props.currentUser) {
      await updateUserApi(String(props.currentUser.id), userData)
      toast.addToast({
        title: 'Success',
        description: 'User updated successfully!',
        type: 'success'
      })
    } else {
      await createUserApi(userData as Omit<User, 'id'>)
      toast.addToast({
        title: 'Success',
        description: 'User created successfully! Default password is: windows7',
        type: 'success'
      })
    }

    emit('user-saved')
    closeModal()
  } catch (error: any) {
    toast.addToast({
      title: 'Error',
      description: error.response?.data?.message || 'An error occurred while saving the user.',
      type: 'error'
    })
  } finally {
    isLoading.value = false
  }
}

const closeModal = () => {
  form.value = { ...initialFormState }
  emit('close')
}
</script>

<template>
  <Dialog
    :is-open="isOpen"
    @close="closeModal"
    :title="isNewUser ? 'Add New Account' : 'Update Account'"
    type="modal"
    :close-on-overlay-click="true"
  >
    <form @submit.prevent="handleSaveUser" class="space-y-4 p-4 text-black">
      <div>
        <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name</label>
        <input
          type="text"
          id="fullName"
          v-model="form.fullName"
          placeholder="e.g., Juan Dela Cruz"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
        />
      </div>
      <div>
        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
        <input
          type="text"
          id="username"
          v-model="form.name"
          placeholder="e.g., juan.cruz"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
        />
      </div>
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input
          type="email"
          id="email"
          v-model="form.email"
          placeholder="e.g., juan.cruz@example.com"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
        />
      </div>
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input
          type="password"
          id="password"
          v-model="form.password"
          :placeholder="isNewUser ? 'Enter password' : 'Optional (leave blank to keep current)'"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
        />
      </div>
      <div>
        <label for="office" class="block text-sm font-medium text-gray-700">Office</label>
        <select
          id="office"
          v-model="form.office!.id"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
        >
          <option value="" disabled>-- Select Office --</option>
          <option v-for="officeOption in offices" :key="officeOption.id" :value="officeOption.id">
            {{ officeOption.name }}
          </option>
        </select>
      </div>
      <div>
        <label for="division" class="block text-sm font-medium text-gray-700">Division</label>
        <select
          id="division"
          v-model="form.division"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
        >
          <option value="" disabled>-- Select Division --</option>
          <option value="PENR Office">PENR Office</option>
          <option value="LGU Coordination">LGU Coordination</option>
          <option value="HR Section">HR Section</option>
          <option value="Legal Division">Legal Division</option>
          <option value="Planning Division">Planning Division</option>
          <option value="Enforcement Division">Enforcement Division</option>
          <option value="Records Section">Records Section</option>
          <option value="Admin">Admin</option>
          <option value="Finance">Finance</option>
          <option value="IT">IT</option>
        </select>
      </div>
      <div>
        <label for="userRole" class="block text-sm font-medium text-gray-700">User Role</label>
        <select
          id="userRole"
          v-model="form.userRole"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
        >
          <option value="" disabled>-- Select User Role --</option>
          <option value="Administrator">Administrator</option>
          <option value="Staff">Staff</option>
        </select>
      </div>
      <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
        <select
          id="status"
          v-model="form.status"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
        >
          <option value="Activated">Activated</option>
          <option value="Deactivated">Deactivated</option>
        </select>
      </div>
    </form>
    <template #footer>
      <div class="flex justify-end space-x-2">
        <Button
          @click="closeModal"
          type="button"
          variant="danger"
          class="w-full sm:w-auto px-4 py-2 text-white rounded-md transition-colors text-sm md:text-base"
        >
          Cancel
        </Button>
        <Button
          type="submit"
          @click="handleSaveUser"
          :disabled="isLoading"
          variant="success"
          class="w-full sm:w-auto text-white"
        >
          {{ isLoading ? 'Saving...' : isNewUser ? 'Add Account' : 'Update Account' }}
        </Button>
      </div>
    </template>
  </Dialog>
</template>
