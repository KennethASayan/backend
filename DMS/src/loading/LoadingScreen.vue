<template>
  <div v-if="loading" class="fixed inset-0 flex items-center justify-center bg-white z-50">
    <img
      src="@/assets/DENR_LOGO.gif"
      alt="Loading Logo"
      class="w-40 h-40 rounded-full"
    />
  </div>

  <main v-if="!loading">
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
const loading = ref(true)

onMounted(() => {
  setTimeout(() => {
    loading.value = false
  }, 2750) // Duration before loader disappears
})
</script>

