<template>
  <Teleport to="body">
    <div class="fixed bottom-6 right-6 z-[200] flex flex-col gap-2 items-end pointer-events-none">
      <TransitionGroup name="toast">
        <div v-for="t in toasts" :key="t.id"
          class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-2xl shadow-lg max-w-sm w-full text-sm font-medium"
          :class="typeClass(t.type)">
          <!-- Icon -->
          <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path v-if="t.type === 'success'" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            <path v-else-if="t.type === 'error'" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            <path v-else-if="t.type === 'warning'" stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="flex-1 leading-relaxed">{{ t.message }}</span>
          <button @click="remove(t.id)" class="shrink-0 opacity-60 hover:opacity-100 transition-opacity -mr-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useToastStore } from '@stores/toast'

const toastStore = useToastStore()
const { toasts } = storeToRefs(toastStore)
const { remove } = toastStore

function typeClass(type) {
  return {
    success: 'bg-green-600 text-white',
    error:   'bg-red-600 text-white',
    warning: 'bg-amber-500 text-white',
    info:    'bg-blue-600 text-white',
  }[type] ?? 'bg-gray-800 text-white'
}
</script>

<style scoped>
.toast-enter-active { transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all 0.18s ease; position: absolute; right: 0; }
.toast-enter-from   { opacity: 0; transform: translateX(24px) scale(0.95); }
.toast-leave-to     { opacity: 0; transform: translateX(24px); }
.toast-move         { transition: transform 0.2s ease; }
</style>
