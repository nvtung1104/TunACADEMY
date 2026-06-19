<template>
  <div class="flex items-center gap-1.5">
    <svg class="w-4 h-4 shrink-0" :class="iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span class="text-sm font-bold tabular-nums" :class="textClass">{{ formatted }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  formatted: { type: String,  required: true },
  isWarning: { type: Boolean, default: false },
  isDanger:  { type: Boolean, default: false },
})

const iconClass = computed(() =>
  props.isDanger ? 'text-red-500' : props.isWarning ? 'text-amber-500' : 'text-green-500'
)
const textClass = computed(() =>
  props.isDanger ? 'text-red-500 animate-pulse' : props.isWarning ? 'text-amber-500' : 'text-green-500'
)
</script>
