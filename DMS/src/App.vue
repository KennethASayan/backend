<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/components/store/authStore';
import SideBar from '@/components/ui/Sidebar/SideBar.vue';
import Header from '@/components/ui/Header/Header.vue';
import ToastNotification from '@/components/ui/message/ToastNotification.vue';
import toast from '@/components/ui/message/toast';

const isSidebarOpen = ref(false);
const route = useRoute();
const authStore = useAuthStore();

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

// Update this computed property to include forgot-password route
const isAuthPage = computed(() => {
  const publicRoutes = ['LandingPage', 'LoginPage', 'NotFound', 'ForgotPassword', 'ResetPassword'];
  return publicRoutes.includes(route.name as string);
});
</script>

<template>
  <ToastNotification :toasts="toast.state.toasts" />

  <router-view v-if="isAuthPage" />

  <div v-else class="flex h-screen overflow-hidden">
    <SideBar :isSidebarOpen="isSidebarOpen" @toggle-sidebar="toggleSidebar" />
    <div class="flex-1 flex flex-col overflow-hidden">
      <Header @toggle-sidebar="toggleSidebar" />
      <main class="p-4 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>