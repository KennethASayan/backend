<script setup lang="ts">
import { useRouter } from 'vue-router'
import { computed } from 'vue';
import { useAuthStore } from '@/components/store/authStore';

const authStore = useAuthStore();
const router = useRouter()

const buttonText = computed(() => {
    // Dynamically set the button text based on authentication status
    return authStore.isAuthenticated.value ? 'Go to Dashboard' : 'Login';
});

const buttonRoute = computed(() => {
    // Dynamically set the button route based on authentication status
    return authStore.isAuthenticated.value ? '/Dashboard' : '/login';
});

const displayButton = computed(() => {
  // Logic to determine what to display on the button
  // This can be customized based on your application's needs
  if (router.currentRoute.value.path === '/login' && !authStore.isAuthenticated.value) {
    return {
      text: 'Show Due Documents',
      route: '/landingpage' // Replace with the actual route for due documents
    };
  } else if (authStore.isAuthenticated.value) {
    return {
      text: 'Go to Dashboard',
      route: '/Dashboard'
    };
  } else {
    return {
      text: 'Login',
      route: '/login'
    };
  }
});

const handleNavigation = () => {
    router.push(displayButton.value.route)
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
        <button
          class="w-full sm:w-auto inline-flex items-center justify-center py-2 px-4 rounded-md font-medium text-sm cursor-pointer transition-all duration-200 bg-green-500 text-white hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          @click="handleNavigation"
        >
          {{ displayButton.text }}
        </button>
      </div>
    </div>
  </nav>
</template>
