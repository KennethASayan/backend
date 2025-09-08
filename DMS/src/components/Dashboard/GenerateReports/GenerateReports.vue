<script setup lang="ts">
import { ref } from 'vue'

const selectedMonthRange = ref('all')
</script>
<template>
  <div class=" bg-gray-50">
      <!-- Router View for Reports Content -->
      <router-view v-slot="{ Component }">
        <keep-alive>
          <component :is="Component" :selected-month-range="selectedMonthRange" />
        </keep-alive>
      </router-view>
    </div>
</template>
