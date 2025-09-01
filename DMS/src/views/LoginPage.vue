<script setup lang="ts">
import Login from '@/components/Auth/Login.vue'
import Header from '../components/landingpage/HeaderPage.vue'
import Navigation from '../components/landingpage/Navigation.vue'
import { DocumentTextIcon } from '@heroicons/vue/24/outline'
import LoadingScreen from '@/loading/LoadingScreen.vue'
</script>

<template>
  <div>
    <LoadingScreen />
    <Header />
    <Navigation buttonText="Show Due Documents" buttonRoute="/LandingPage">
      <template #button-icon>
        <DocumentTextIcon class="h-5 w-5" aria-hidden="true" />
      </template>
    </Navigation>
    <Login />
  </div>
</template>