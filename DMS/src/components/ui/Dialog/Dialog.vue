<script setup lang="ts">
import { computed, watch, onMounted, onUnmounted } from 'vue'
import { X } from 'lucide-vue-next'
import {
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogOverlay,
  DialogPortal,
  DialogRoot,
  DialogTitle,
} from 'reka-ui'

const props = defineProps<{
  isOpen: boolean
  type?: 'modal' | 'drawer'
  // Add 'center' to the position type
  position?: 'left' | 'right' | 'top' | 'bottom' | 'center'
  title?: string
  description?: string
  closeOnOverlayClick?: boolean
  closeOnEsc?: boolean
  drawerMdWidth?: string
  icon?: any
  titleDescription?: string
}>()

const emit = defineEmits(['close'])

const descriptionId = computed(() => `dialog-description-${Math.random().toString(36).substring(2, 9)}`)
const ariaDescribedBy = computed(() => {
  return props.description && !props.icon ? descriptionId.value : undefined
})

const dialogContentClasses = computed(() => {
  const baseClasses =
    'fixed bg-white shadow-lg z-[9999] dark:bg-white-900 dark:text-black flex flex-col transition-transform duration-300 ease-in-out border border-gray-400 rounded-lg'


  // Refactored modal classes for clarity
  const modalClasses =
    'w-11/12 max-w-lg md:w-full rounded-lg h-auto max-h-[90vh] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2'

  if (props.type === 'modal') {
    return `${baseClasses} ${modalClasses}`
  } else if (props.type === 'drawer') {
    const mdWidthClass = props.drawerMdWidth ? `md:w-[${props.drawerMdWidth}]` : 'md:w-[48%]'
    switch (props.position) {
      case 'left':
        return `${baseClasses} w-64 ${mdWidthClass} h-full top-0 left-0 rounded-none inset-y-0`
      case 'right':
        return `${baseClasses} w-full ${mdWidthClass} h-full top-0 right-0 rounded-none inset-y-0`
      case 'top':
        return `${baseClasses} w-full h-auto max-h-[150vh] top-0 left-0 rounded-b-lg`
      case 'bottom':
        return `${baseClasses} w-full h-auto max-h-[130vh] bottom-0 left-0`
      // Add a case for 'center' to reuse modal classes
      case 'center':
        return `${baseClasses} ${modalClasses}`
      default:
        // Default to a right drawer if position is not specified for a drawer
        return `${baseClasses} w-64 ${mdWidthClass} h-full top-0 right-0 rounded-none inset-y-0`
    }
  }
  // Default to modal classes if type is not specified
  return `${baseClasses} ${modalClasses}`
})

const contentEnterFromLeaveToClass = computed(() => {
  if (props.type === 'modal' || props.position === 'center') return 'opacity-0 scale-95'
  if (props.type === 'drawer') {
    switch (props.position) {
      case 'left':
        return '-translate-x-full'
      case 'right':
        return 'translate-x-full'
      case 'top':
        return '-translate-y-full'
      case 'bottom':
        return 'translate-y-full'
      default:
        return 'translate-x-full'
    }
  }
  return ''
})

const handleOverlayClick = () => {
  if (props.closeOnOverlayClick) {
    emit('close')
  }
}

const handleKeydown = (event: KeyboardEvent) => {
  if (props.closeOnEsc && event.key === 'Escape' && props.isOpen) {
    emit('close')
  }
}

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      if (props.closeOnEsc) {
        document.addEventListener('keydown', handleKeydown)
      }
      document.body.style.overflow = 'hidden'
    } else {
      document.removeEventListener('keydown', handleKeydown)
      document.body.style.overflow = ''
    }
  },
  { immediate: true },
)

onMounted(() => {
  if (props.isOpen) {
    document.body.style.overflow = 'hidden'
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})
</script>

<template>
  <DialogRoot :open="isOpen">
    <DialogPortal>
      <Transition
        enter-active-class="transition-opacity duration-300 ease-in-out"
        leave-active-class="transition-opacity duration-300 ease-in-out"
        enter-from-class="opacity-0"
        leave-to-class="opacity-0"
      >
        <DialogOverlay
          v-if="isOpen"
          class="fixed inset-0 bg-black bg-opacity-50 z-[998]"
          @click="handleOverlayClick"
        />
      </Transition>

      <Transition
        enter-active-class="transform transition-transform duration-300 ease-in-out"
        leave-active-class="transform transition-transform duration-300 ease-in-out"
        :enter-from-class="contentEnterFromLeaveToClass"
        :leave-to-class="contentEnterFromLeaveToClass"
      >
        <DialogContent
          v-if="isOpen"
          :class="dialogContentClasses"
          :aria-describedby="ariaDescribedBy"
        >
          <div
            class="flex items-center p-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0"
          >
            <div class="flex-grow flex items-center">
              <slot name="title-actions" />
              <div v-if="icon" class="flex items-center gap-4 ml-4">
                <div class="p-2 bg-blue-100 rounded-md">
                  <component :is="icon" class="w-6 h-6 text-blue-500" />
                </div>
                <div>
                  <DialogTitle class="text-lg font-semibold text-gray-900 dark:text-black">
                    {{ title }}
                  </DialogTitle>
                  <p v-if="titleDescription" class="text-sm text-gray-600 dark:text-neutral-300">
                    {{ titleDescription }}
                  </p>
                </div>
              </div>
              <DialogTitle
                v-else-if="title"
                class="text-lg font-semibold text-gray-900 dark:text-black ml-4"
              >
                {{ title }}
              </DialogTitle>
            </div>
            <div class="flex-shrink-0">
              <DialogClose as-child>
                <button
                  @click="emit('close')"
                  class="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <X class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                </button>
              </DialogClose>
            </div>
          </div>

          <DialogDescription
            v-if="description && !icon"
            :id="descriptionId"
            class="text-sm text-gray-600 text-end px-4 pt-2"
          >
            {{ description }}
          </DialogDescription>

          <div class="flex-1 overflow-y-auto p-4 min-h-0">
            <slot></slot>
          </div>

          <div
            v-if="$slots.footer"
            class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end flex-shrink-0"
          >
            <slot name="footer"></slot>
          </div>
        </DialogContent>
      </Transition>
    </DialogPortal>
  </DialogRoot>
</template>
