<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/components/store/authStore'

const router = useRouter()
const authStore = useAuthStore()

const goToLogin = () => {
  router.push('/login')
}

const goToDashboard = () => {
  router.push('/Dashboard')
}

const handleLogout = async () => {
  await authStore.logout(router)
}
</script>

<template>
  <nav class="bg-white py-3 sm:py-4 shadow-md">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4 sm:gap-0">
        <div class="flex items-center gap-3">
          <div class="w-10 sm:w-12 h-10 sm:h-12 rounded-full flex items-center justify-center">
            <img
              src="@/assets/DENRLOGO.png"
              alt="DENR Logo"
              class="w-9 sm:w-[42px] h-9 sm:h-[42px] object-cover rounded-full"
            />
          </div>
          <div>
            <div class="text-xl sm:text-2xl font-bold text-gray-800">DENR-X</div>
            <div class="text-xs sm:text-sm text-gray-600">
              Department of Environment and Natural Resources
            </div>
          </div>
        </div>

        <!-- Navigation Actions -->
        <div class="flex items-center space-x-4">
          <!-- Show when NOT authenticated -->
          <template v-if="!authStore.isAuthenticated.value">
            <button
              @click="goToLogin"
              class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors duration-200 font-medium"
            >
              Login
            </button>
          </template>

          <!-- Show when authenticated -->
          <template v-else>
            <div class="flex items-center space-x-4">
              <span class="text-gray-700">
                Welcome, {{ authStore.user.value?.name || authStore.user.value?.fullName }}
              </span>
              <button
                @click="goToDashboard"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200 font-medium"
              >
                Go to Dashboard
              </button>
              <button
                @click="handleLogout"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 font-medium"
              >
                Logout
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>