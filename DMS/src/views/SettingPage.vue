<script setup lang="ts">
import { UserCircleIcon, LockClosedIcon } from '@heroicons/vue/24/outline'
</script>

<template>
  <div class="dark:bg-white sm:p-6 lg:p-8 font-sans">
    <div
      class="max-w-6xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden transform transition-all duration-300"
    >
      <!-- Header Section -->
      <div
        class="p-6 sm:p-8 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"
      >
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Settings</h2>
        <p class="mt-2 text-base text-gray-600 dark:text-gray-400">
          Manage your profile and account settings.
        </p>
      </div>

      <!-- Mobile Navigation (Top) -->
      <div
        class="block lg:hidden border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4"
      >
        <nav class="flex space-x-2 overflow-x-auto">
          <router-link
            to="/settings/profile"
            class="flex items-center gap-2 px-4 py-2 rounded-lg whitespace-nowrap transition-all duration-200 ease-in-out"
            :class="{
              'bg-green-50 text-green-500 dark:bg-green-800 dark:text-green-200 shadow-md hover:bg-green-100 dark:hover:bg-green-700':
                $route.path === '/settings/profile',
              'text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700':
                $route.path !== '/settings/profile',
            }"
          >
            <UserCircleIcon class="h-5 w-5" />
            <span class="font-medium">Profile</span>
          </router-link>

          <router-link
            to="/settings/password"
            class="flex items-center gap-2 px-4 py-2 rounded-lg whitespace-nowrap transition-all duration-200 ease-in-out"
            :class="{
              'bg-green-50 text-green-500 dark:bg-green-800 dark:text-green-200 shadow-md hover:bg-green-100 dark:hover:bg-green-700':
                $route.path === '/settings/password',
              'text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700':
                $route.path !== '/settings/password',
            }"
          >
            <LockClosedIcon class="h-5 w-5" />
            <span class="font-medium">Password</span>
          </router-link>
        </nav>
      </div>

      <div class="flex flex-col lg:flex-row min-h-[600px]">
        <!-- Desktop Sidebar Navigation -->
        <aside
          class="hidden lg:block lg:w-80 border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-6 transition-all duration-300"
        >
          <nav class="space-y-3">
            <router-link
              to="/settings/profile"
              class="group flex items-center gap-4 px-6 py-4 rounded-xl transition-all duration-200 ease-in-out hover:shadow-md border border-transparent hover:border-gray-200 dark:hover:border-gray-600"
              :class="{
                'bg-green-50 text-green-500 dark:bg-green-800 dark:text-green-200':
                  $route.path === '/settings/profile',
                'text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700':
                  $route.path !== '/settings/profile',
              }"
            >
              <UserCircleIcon
                class="h-6 w-6 transition-colors duration-200"
                :class="{
                  'text-green-500': $route.path === '/settings/profile',
                  'text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300':
                    $route.path !== '/settings/profile',
                }"
              />
              <div class="flex-1">
                <span class="font-semibold text-base">Profile</span>
                <p class="text-sm opacity-75 mt-1">
                  Update your name and email address
                </p>
              </div>
            </router-link>

            <router-link
              to="/settings/password"
              class="group flex items-center gap-4 px-6 py-4 rounded-xl transition-all duration-200 ease-in-out hover:shadow-md border border-transparent hover:border-gray-200 dark:hover:border-gray-600"
              :class="{
                'bg-green-50 text-green-500 dark:bg-green-800 dark:text-green-200':
                  $route.path === '/settings/password',
                'text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700':
                  $route.path !== '/settings/password',
              }"
            >
              <LockClosedIcon
                class="h-6 w-6 transition-colors duration-200"
                :class="{
                  'text-green-500': $route.path === '/settings/password',
                  'text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300':
                    $route.path !== '/settings/password',
                }"
              />
              <div class="flex-1">
                <span class="font-semibold text-base">Password</span>
                <p class="text-sm opacity-75 mt-1">Change your password</p>
              </div>
            </router-link>
          </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-6 sm:p-8 bg-white dark:bg-gray-900">
          <div class="max-w-4xl">
            <router-view />
          </div>
        </main>
      </div>
    </div>
  </div>
</template>
