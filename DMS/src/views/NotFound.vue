<script setup lang="ts">
import { useAuthStore } from '@/components/store/authStore'
const auth = useAuthStore()
</script>

<template>
  <div class="flex flex-col items-center justify-center h-screen">
    <h1 class="text-6xl font-bold mb-4">404: Page Not Found</h1>
    <p class="text-lg mb-6">Sorry, the page you are looking for could not be found.</p>
    <router-link :to="auth.isAuthenticated ? '/Dashboard' : '/LandingPage'">
      <button class="bg-green-600 text-white px-6 py-2 rounded shadow">
        Go back {{ auth.isAuthenticated ? 'dashboard' : 'home' }}
      </button>
    </router-link>
  </div>
</template>