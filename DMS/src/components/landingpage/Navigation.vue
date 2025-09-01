<script setup lang="ts">
import { useRouter } from 'vue-router'
import Button from '@/components/ui/button/Button.vue'

interface Props {
  buttonText?: string
  buttonRoute?: string
}

const props = withDefaults(defineProps<Props>(), {
  buttonText: 'Login',
  buttonRoute: '/login',
})

const router = useRouter()

const handleNavigation = () => {
  router.push(props.buttonRoute)
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
          {{ buttonText }}
        </button>
      </div>
    </div>
  </nav>
</template>
