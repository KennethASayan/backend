<script setup lang="ts">
import { ref } from 'vue'

const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')

const handlePasswordUpdate = () => {
  if (newPassword.value !== confirmPassword.value) {
    alert('New password and confirm password do not match.')
    return
  }

  console.log('Updating password with:', {
    current: currentPassword.value,
    new: newPassword.value,
  })

  currentPassword.value = ''
  newPassword.value = ''
  confirmPassword.value = ''

  alert('Password updated successfully!')
}
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl p-6 sm:p-8 border border-gray-200 dark:border-gray-700">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Update password</h3>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      Ensure your account is using a long, random password to stay secure
    </p>
    <form @submit.prevent="handlePasswordUpdate" class="mt-6 space-y-6">
      <div>
        <label for="current-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Current password
        </label>
        <div class="mt-2">
          <input
            id="current-password"
            v-model="currentPassword"
            type="password"
            required
            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm p-2"
            placeholder="Current password"
          />
        </div>
      </div>
      <div>
        <label for="new-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          New password
        </label>
        <div class="mt-2">
          <input
            id="new-password"
            v-model="newPassword"
            type="password"
            required
            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm p-2"
            placeholder="New password"
          />
        </div>
      </div>
      <div>
        <label for="confirm-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Confirm password
        </label>
        <div class="mt-2">
          <input
            id="confirm-password"
            v-model="confirmPassword"
            type="password"
            required
            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm p-2"
            placeholder="Confirm password"
          />
        </div>
      </div>
      <div class="pt-5 text-end">
        <button
          type="submit"
          class="inline-flex justify-end rounded-lg border border-transparent bg-green-600 py-2 px-6 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
        >
          Save password
        </button>
      </div>
    </form>
  </div>
</template>