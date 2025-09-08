<script setup lang="ts">
import {
  DropdownMenuRoot,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuPortal,
} from 'reka-ui'
import { ref, watch } from 'vue'
import type { Component } from 'vue'

interface Props {
  buttonClass?: string
  buttonText?: string
  buttonLabel?: string
  buttonIcon?: Component | string // Use Vue's Component type for dynamic components
  align?: 'start' | 'center' | 'end'
  side?: 'top' | 'right' | 'bottom' | 'left'
  sideOffset?: number
  // Props can be extended to control other aspects of the dropdown
  contentClass?: string 
}

const props = withDefaults(defineProps<Props>(), {
  buttonClass:
    'inline-flex items-center justify-center py-2 px-4 rounded-md font-medium text-sm cursor-pointer transition-all duration-200 bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
  buttonLabel: 'Open Dropdown',
  align: 'end',
  side: 'bottom',
  sideOffset: 8,
  contentClass: '' // Default to an empty string
})

// Emit events
const emit = defineEmits<{
  'dropdown-toggle': [isOpen: boolean]
  'item-select': [item: any]
}>()

const isOpen = ref(false)

// Watch for changes in dropdown state and emit to parent
watch(isOpen, (newValue) => {
  emit('dropdown-toggle', newValue)
})

// Handle item selection and close dropdown
const handleItemClick = (event: Event, item?: any) => {
  // Close the dropdown
  isOpen.value = false
  
  // Emit item selection if item data is provided
  if (item) {
    emit('item-select', item)
  }
}
</script>

<template>
  <DropdownMenuRoot v-model:open="isOpen">
    <DropdownMenuTrigger as-child>
      <button :class="buttonClass">
        <slot name="button-content">
          <component
            :is="props.buttonIcon"
            v-if="props.buttonIcon"
            class="w-5 h-5 mr-2"
          />
          {{ buttonLabel }}
        </slot>
      </button>
    </DropdownMenuTrigger>

    <DropdownMenuPortal>
      <DropdownMenuContent
  :class="['w-full outline-none bg-white rounded-md p-[5px] shadow-lg border border-gray-200 z-[9999]', contentClass]"
  :side="side"
  :side-offset="sideOffset"
  :align="align"
  :collision-padding="8"
  :avoid-collisions="true"
  :sticky="'partial'"
  @click="handleItemClick"
>
  <slot></slot>
</DropdownMenuContent>
    </DropdownMenuPortal>
  </DropdownMenuRoot>
</template>