<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router' // Import useRoute here
import { UserIcon, LockClosedIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/components/store/authStore'

const router = useRouter()
const route = useRoute() // Initialize useRoute to access the query parameters
const auth = useAuthStore()

const department = ref('')
const password = ref('')
const rememberMe = ref(false)

const handleLogin = async () => {
  const success = await auth.login(department.value, password.value, rememberMe.value)
  if (success) {
    auth.setSidebarState(true)

    // Check for a redirect query parameter from the URL
    const redirectPath = route.query.redirect as string || '/Dashboard';
    router.push(redirectPath)
  }
}

onMounted(() => {
  auth.initAuth()
})
</script>

<template>
  <div class="bg-gray-100 flex items-center justify-center p-6 ">
    <div class="w-full max-w-sm">
      <div class="bg-white rounded-lg p-8 shadow-md">
        <div class="text-center mb-8">
          <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
            <img
              src="@/assets/DENRLOGO.png"
              alt="DENR Logo"
              class="w-full h-full object-cover rounded-full"
            />
          </div>
          <h1 class="text-xl font-bold text-gray-800 mb-2">Document Monitoring System</h1>
          <p class="text-sm text-gray-600 leading-normal">
            Sign in to your account to access document services and documents
          </p>
        </div>

        <form @submit.prevent="handleLogin" class="mb-6">
          <div class="mb-4 relative">
            <label for="username" class="absolute left-3 top-1/2 -translate-y-1/2 z-10">
              <UserIcon class="w-5 h-5 text-gray-400" />
            </label>
            <input
              id="username"
              type="text"
              placeholder="Enter your username"
              class="w-full pl-11 pr-3 py-3 border border-gray-300 rounded-md text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500/10"
              v-model="department"
              required
            />
          </div>

          <div class="mb-4 relative">
            <label for="password" class="absolute left-3 top-1/2 -translate-y-1/2 z-10">
              <LockClosedIcon class="w-5 h-5 text-gray-400" />
            </label>
            <input
              id="password"
              type="password"
              placeholder="Enter your password"
              class="w-full pl-11 pr-3 py-3 border border-gray-300 rounded-md text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500/10"
              v-model="password"
              required
            />
          </div>

          <div class="flex items-center justify-between mb-6">
            <label class="flex items-center gap-2 text-sm text-gray-600">
              <input type="checkbox" v-model="rememberMe" class="accent-green-500 w-4 h-4" />
              Remember me
            </label>
            <a href="#" class="text-green-500 text-sm hover:underline">Forgot password?</a>
          </div>

          <button
            type="submit"
            class="w-full bg-green-500 text-white rounded-md py-3 text-base font-medium cursor-pointer hover:bg-green-600 transition-colors"
            :disabled="auth.loading.value"
          >
            <span v-if="auth.loading.value">Signing in...</span>
            <span v-else>Sign In to DMS</span>
          </button>

          <div class="text-center text-sm text-gray-600 mt-6">
            <span>Don't have an account? </span>
            <a
              href="https://srs.denr10.com.ph/"
              class="text-green-500 hover:underline"
              target="_blank"
              rel="noopener noreferrer"
            >
              Request Technical Support
            </a>
          </div>
        </form>

        <div class="border-t border-gray-200 pt-6 text-xs text-gray-600 leading-normal">
          <h3 class="text-sm font-bold text-gray-800 mb-3">ADVISORY TO ALL END USERS</h3>
          <p class="text-xs font-bold text-black mb-3">- February 1, 2021</p>
          <p class="mb-3">
            1. For system algorithm purposes, please DO NOT include the PERIOD punctuation mark
            after the last word when encoding the document SUBJECT.
          </p>
          <p>
            2. To prevent duplicate or multiple entries, click the VERIFY button to check if the
            document is already existing in the DMS database. If already existing, please use the
            same DMS No.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
