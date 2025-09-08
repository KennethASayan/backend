<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Reset Password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter your new password
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="space-y-4">
          <div>
            <label for="email" class="sr-only">Email address</label>
            <input
              id="email"
              name="email"
              type="email"
              required
              v-model="email"
              class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="Email address"
            />
          </div>

          <div>
            <label for="password" class="sr-only">New Password</label>
            <input
              id="password"
              name="password"
              type="password"
              required
              v-model="password"
              class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="New password"
            />
          </div>

          <div>
            <label for="password_confirmation" class="sr-only">Confirm Password</label>
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              required
              v-model="passwordConfirmation"
              class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="Confirm new password"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <span v-if="loading" class="animate-spin h-5 w-5 text-white">⌛</span>
            </span>
            {{ loading ? 'Resetting...' : 'Reset Password' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/services/api'
import toast from '@/components/ui/message/toast'

const router = useRouter()
const route = useRoute()
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const token = ref('')

onMounted(() => {
  token.value = route.query.token as string
  if (!token.value) {
    toast.addToast({
      type: 'error',
      title: 'Error',
      description: 'Invalid reset token'
    })
    router.push('/login')
  }
})

const handleSubmit = async () => {
  if (password.value !== passwordConfirmation.value) {
    toast.addToast({
      type: 'error',
      title: 'Error',
      description: 'Passwords do not match'
    })
    return
  }

  loading.value = true
  try {
    await apiClient.post('/dms/reset-passwords', {
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
      token: token.value
    })

    toast.addToast({
      type: 'success',
      title: 'Success',
      description: 'Password has been reset successfully'
    })

    // Redirect to login after 2 seconds
    setTimeout(() => {
      router.push('/login')
    }, 2000)

  } catch (error: any) {
    toast.addToast({
      type: 'error',
      title: 'Error',
      description: error.response?.data?.message || 'Failed to reset password'
    })
  } finally {
    loading.value = false
  }
}
</script>