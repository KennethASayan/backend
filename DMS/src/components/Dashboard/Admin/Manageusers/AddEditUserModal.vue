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
  password: '',
  office: { id: '', name: '', code: '' },
  division: '',
  status: 'Activated' as const,
  // userRole is removed from here
}

const form = ref<Partial<User & { password?: string }>>(initialFormState)
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
        // ⚠️ FIX: Correctly map 'division' from currentUser.division
        form.value.division = props.currentUser.division
        form.value.password = '' // Clear password field for security
      } else {
        // Create mode - reset form
        form.value = { ...initialFormState }
      }
    }
  },
  { immediate: true },
)

const validateForm = () => {
  const requiredFields = {
    fullName: 'Full Name',
    name: 'Username',
    email: 'Email',
    division: 'Division',
    status: 'Status',
  }

  for (const [field, label] of Object.entries(requiredFields)) {
    const value = form.value[field as keyof typeof form.value]
    if (!value) {
      toast.addToast({
        title: 'Validation Error',
        description: `${label} is required.`,
        type: 'error',
      })
      return false
    }
  }

  // Validate password length for new users
  if (isNewUser.value && form.value.password && form.value.password.length < 6) {
    toast.addToast({
      title: 'Validation Error',
      description: 'Password must be at least 6 characters long.',
      type: 'error',
    })
    return false
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.value.email || '')) {
    toast.addToast({
      title: 'Validation Error',
      description: 'Please enter a valid email address.',
      type: 'error',
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
      password: form.value.password, // Include password in the request
      division: form.value.division,
      status: form.value.status,
    }

  if (props.currentUser) {
      // Update existing user
      try {
        await updateUserApi(String(props.currentUser.id), userData)
        toast.addToast({
          title: 'Success',
          description: 'User updated successfully!',
          type: 'success',
        })
        emit('user-saved')
        closeModal()
      } catch (error: any) {
        // Handle specific validation errors
        if (error.errors) {
          if (error.errors.email) {
            toast.addToast({
              title: 'Validation Error',
              description: error.errors.email[0],
              type: 'error',
            })
          }
          if (error.errors.name) {
            toast.addToast({
              title: 'Validation Error', 
              description: error.errors.name[0],
              type: 'error',
            })
          }
        } else {
          toast.addToast({
            title: 'Error',
            description: error.message || 'Failed to update user',
            type: 'error',
          })
        }
        throw error
      }
    } else {
      try {
        await createUserApi(userData)
        toast.addToast({
          title: 'Success',
          description: 'User created successfully!',
          type: 'success',
        })
      } catch (error: any) {
        // Handle specific validation errors
        if (error.response?.data?.errors) {
          const errors = error.response.data.errors
          if (errors.name) {
            toast.addToast({
              title: 'Error',
              description: 'This username is already taken.',
              type: 'error',
            })
          }
          if (errors.email) {
            toast.addToast({
              title: 'Error',
              description: 'This email address is already registered.',
              type: 'error',
            })
          }
        } else {
          toast.addToast({
            title: 'Error',
            description:
              error.response?.data?.message || 'An error occurred while creating the user.',
            type: 'error',
          })
        }
        throw error
      }
    }

    emit('user-saved')
    closeModal()
  } catch (error: any) {
    console.error('Error saving user:', error)
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
          :placeholder="isNewUser ? 'Enter password' : 'Leave blank to keep current'"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
          :required="isNewUser"
        />
      </div>
      <div>
        <label for="division" class="block text-sm font-medium text-gray-700"
          >Office/Division</label
        >
        <select
          id="division"
          v-model="form.division"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
        >
          <option value="" disabled>-- Select Office --</option>
          <option value="Cashier">Cashier</option>
          <option value="Oafo">Oafo</option>
          <option value="System Admin">System Admin</option>
          <option value="Lpdd">Lpdd</option>
          <option value="Pmd">Pmd</option>
          <option value="Admin">Admin</option>
          <option value="Finance">Finance</option>
          <option value="Admin">Ored</option>
          <option value="Legal">Legal</option>
          <option value="Rscis">Rscis</option>
          <option value="Ardms">Ardms</option>
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
