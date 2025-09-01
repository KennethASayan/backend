<script setup lang="ts">
import { ref } from 'vue'

const name = ref('Kenneth_Sayan')
const email = ref('admin@gmail.com')

const handleSubmit = () => {
  console.log('Saving profile information:', {
    name: name.value,
    email: email.value,
  })
  alert('Profile updated successfully!')
}
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl p-6 sm:p-8 border border-gray-200 dark:border-gray-700">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Profile information</h3>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      Update your name and email address.
    </p>
    <form @submit.prevent="handleSubmit" class="mt-6 space-y-6">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Name
        </label>
        <div class="mt-2">
          <input
            id="name"
            v-model="name"
            type="text"
            required
            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm p-2"
            placeholder="Enter your name"
          />
        </div>
      </div>
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Email address
        </label>
        <div class="mt-2">
          <input
            id="email"
            v-model="email"
            type="email"
            required
            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm p-2"
            placeholder="Enter your email"
          />
        </div>
      </div>
      <div class="pt-5 text-end">
        <button
          type="submit"
          class="inline-flex justify-end rounded-lg border border-transparent bg-green-600 py-2 px-6 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
        >
          Save
        </button>
      </div>
    </form>
  </div>
</template>