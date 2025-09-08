<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Forgot Password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter your email address to receive a password reset link
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div>
          <label for="email" class="sr-only">Email address</label>
          <input
            id="email"
            name="email"
            type="email"
            required
            v-model="email"
            class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            placeholder="Enter your email address"
          />
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
            {{ loading ? 'Sending...' : 'Send Reset Link' }}
          </button>
        </div>

        <div class="text-center">
          <router-link
            to="/login"
            class="text-sm text-blue-600 hover:text-blue-800"
          >
            Back to Login
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/services/api'
import toast from '@/components/ui/message/toast'

const router = useRouter()
const email = ref('')
const loading = ref(false)

const handleSubmit = async () => {
  if (!email.value) return

  loading.value = true
  try {
    const response = await apiClient.post('/dms/forgot-password', {
      email: email.value
    })

    toast.addToast({
      title: 'Success',
      description: 'Password reset link has been sent to your email.',
      type: 'success'
    })

    setTimeout(() => {
      router.push('/login')
    }, 2000)

  } catch (error: any) {
    toast.addToast({
      title: 'Error',
      description: error.response?.data?.message || 'Failed to send reset link',
      type: 'error'
    })
  } finally {
    loading.value = false
  }
}
</script>