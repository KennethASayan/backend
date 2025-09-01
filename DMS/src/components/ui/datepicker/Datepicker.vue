<script setup lang="ts">
import { Icon } from '@iconify/vue'
import {
  DatePickerArrow,
  DatePickerCalendar,
  DatePickerCell,
  DatePickerCellTrigger,
  DatePickerContent,
  DatePickerField,
  DatePickerGrid,
  DatePickerGridBody,
  DatePickerGridHead,
  DatePickerGridRow,
  DatePickerHeadCell,
  DatePickerHeader,
  DatePickerHeading,
  DatePickerInput,
  DatePickerNext,
  DatePickerPrev,
  DatePickerRoot,
  DatePickerTrigger,
  Label,
  DateValue,
} from 'reka-ui'
import { computed, PropType } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object as PropType<DateValue | null>,
    required: false,
    default: null,
  },
  id: {
    type: String,
    required: false,
    default: 'date',
  },
})

const emit = defineEmits(['update:modelValue'])

const selectedDate = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  },
})

const clearDate = () => {
  selectedDate.value = null
}
</script>

<template>
  <div class="flex flex-col gap-2">
    <DatePickerRoot v-model="selectedDate" :id="id">
      <DatePickerField
        v-slot="{ segments }"
        class="relative w-full flex select-none bg-white items-center rounded-lg shadow-sm text-center justify-between text-gray-800 border border-gray-300 p-2 focus-within:ring-2 focus-within:ring-teal-500"
      >
        <div class="flex items-center justify-center flex-1">
          <template
            v-for="item in segments"
            :key="item.part"
          >
            <DatePickerInput
              v-if="item.part === 'literal'"
              :part="item.part"
            >
              {{ item.value }}
            </DatePickerInput>
            <DatePickerInput
              v-else
              :part="item.part"
              class="rounded p-0.5 focus:outline-none focus:bg-gray-100 data-[placeholder]:text-gray-400 text-sm"
            >
              {{ item.value }}
            </DatePickerInput>
          </template>
        </div>

        <DatePickerTrigger class="pointer-events-auto focus:outline-none focus:ring-2 focus:ring-teal-500 rounded p-1">
          <Icon
            icon="radix-icons:calendar"
            class="z-[99999] text-base text-gray-500 hover:text-gray-700"
          />
        </DatePickerTrigger>
      </DatePickerField>

      <DatePickerContent
        :side-offset="1"
        class="z-[99999] rounded-xl bg-white border border-gray-200 shadow-lg will-change-[transform,opacity] data-[state=open]:data-[side=top]:animate-slideDownAndFade data-[state=open]:data-[side=right]:animate-slideLeftAndFade data-[state=open]:data-[side=bottom]:animate-slideUpAndFade data-[state=open]:data-[side=left]:animate-slideRightAndFade"
      >
        <DatePickerArrow class="fill-white stroke-gray-300" />
        <DatePickerCalendar
          v-slot="{ weekDays, grid }"
          class="p-4"
        >
          <DatePickerHeader class="flex items-center justify-between">
            <DatePickerPrev
              class="inline-flex items-center cursor-pointer text-gray-600 justify-center rounded-md bg-transparent w-7 h-7 hover:bg-gray-100 active:scale-98 active:transition-all focus:outline-none focus:ring-2 focus:ring-teal-500"
            >
              <Icon
                icon="radix-icons:chevron-left"
                class="w-4 h-4"
              />
            </DatePickerPrev>

            <DatePickerHeading class="text-gray-800 font-medium" />
            <DatePickerNext
              class="inline-flex items-center cursor-pointer text-gray-600 justify-center rounded-md bg-transparent w-7 h-7 hover:bg-gray-100 active:scale-98 active:transition-all focus:outline-none focus:ring-2 focus:ring-teal-500"
            >
              <Icon
                icon="radix-icons:chevron-right"
                class="w-4 h-4"
              />
            </DatePickerNext>
          </DatePickerHeader>
          <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:space-x-4 sm:space-y-0">
            <DatePickerGrid
              v-if="grid.length > 0"
              class="w-full border-collapse select-none space-y-1"
            >
              <DatePickerGridHead>
                <DatePickerGridRow class="mb-1 flex w-full justify-between">
                  <DatePickerHeadCell
                    v-for="day in weekDays"
                    :key="day"
                    class="w-8 rounded-md text-xs text-gray-500"
                  >
                    {{ day }}
                  </DatePickerHeadCell>
                </DatePickerGridRow>
              </DatePickerGridHead>
              <DatePickerGridBody>
                <DatePickerGridRow
                  v-for="(weekDates, index) in grid[0].rows"
                  :key="`weekDate-${index}`"
                  class="flex w-full"
                >
                  <DatePickerCell
                    v-for="weekDate in weekDates"
                    :key="weekDate.toString()"
                    :date="weekDate"
                  >
                    <DatePickerCellTrigger
                      :day="weekDate"
                      :month="grid[0].value"
                      class="relative flex items-center justify-center whitespace-nowrap rounded-md border border-transparent bg-transparent text-sm font-normal text-gray-900 w-8 h-8 outline-none focus:ring-2 focus:ring-teal-500 hover:bg-gray-100 data-[selected]:bg-teal-500 data-[selected]:font-medium data-[outside-view]:text-gray-400 data-[selected]:text-white data-[unavailable]:pointer-events-none data-[unavailable]:text-gray-300 data-[unavailable]:line-through before:absolute before:top-[5px] before:hidden before:rounded-full before:w-1 before:h-1 before:bg-white data-[today]:before:block data-[today]:before:bg-teal-500 data-[selected]:before:bg-white"
                    />
                  </DatePickerCell>
                </DatePickerGridRow>
              </DatePickerGridBody>
            </DatePickerGrid>
          </div>
        </DatePickerCalendar>

        <div class="flex justify-center p-2">
          <button
            type="button"
            class="text-sm text-red-500 hover:underline"
            @click="clearDate"
            v-if="selectedDate"
          >
            Clear
          </button>
        </div>
      </DatePickerContent>
    </DatePickerRoot>
  </div>
</template>
