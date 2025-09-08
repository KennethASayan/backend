<script setup lang="ts">
import { defineProps, PropType } from 'vue';
import {
  ToastRoot,
  ToastProvider,
  ToastViewport,
  ToastTitle,
  ToastDescription,
  ToastAction,
  ToastClose,
} from 'reka-ui';

// Define the shape of a single toast object
interface Toast {
  id: string | number;
  title: string;
  description: string;
}

const props = defineProps({
  // Use PropType to correctly type the array with the new interface
  toasts: {
    type: Array as PropType<Toast[]>,
    default: () => [],
  },
});
</script>

<template>
  <ToastProvider>
    <ToastRoot
      v-for="t in toasts"
      :key="t.id"
      :open="true"
      class="bg-white rounded-lg shadow-sm border p-[15px] grid [grid-template-areas:_'title_action'_'description_action'] grid-cols-[auto_max-content] gap-x-[15px] items-center data-[state=open]:animate-slideIn data-[state=closed]:animate-hide data-[swipe=move]:translate-x-[var(--reka-toast-swipe-move-x)] data-[swipe=cancel]:translate-x-0 data-[swipe=cancel]:transition-[transform_200ms_ease-out] data-[swipe=end]:animate-swipeOut"
    >
      <ToastTitle class="[grid-area:_title] mb-[5px] font-medium text-slate12 text-sm">
        {{ t.title }}
      </ToastTitle>
      <ToastDescription class="[grid-area:_description] m-0 text-slate11 text-xs leading-[1.3]">
        {{ t.description }}
      </ToastDescription>
    </ToastRoot>
<ToastViewport class="[--viewport-padding:_25px] fixed top-0 left-1/2 -translate-x-1/2 flex flex-col p-[var(--viewport-padding)] gap-[10px] w-[390px] max-w-[100vw] m-0 list-none z-[2147483647] outline-none pointer-events-none items-center" />

  </ToastProvider>
</template>
