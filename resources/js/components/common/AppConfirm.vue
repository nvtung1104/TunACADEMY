<template>
  <Teleport to="body">
    <Transition name="overlay">
      <div v-if="visible"
        class="fixed inset-0 z-[300] flex items-center justify-center p-4 bg-black/40"
        @keydown.esc="reject">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full">
          <div class="flex items-start gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0 mt-0.5">
              <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-gray-900 text-base leading-tight">{{ title }}</h3>
              <p class="text-sm text-gray-500 mt-1 leading-relaxed">{{ message }}</p>
            </div>
          </div>
          <div class="flex gap-3">
            <button @click="reject"
              class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
              Hủy
            </button>
            <button @click="accept"
              class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition-colors">
              Xác nhận
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useConfirmStore } from '@stores/confirm'

const confirmStore = useConfirmStore()
const { visible, title, message } = storeToRefs(confirmStore)
const { accept, reject } = confirmStore
</script>

<style scoped>
.overlay-enter-active { transition: opacity 0.15s ease; }
.overlay-leave-active { transition: opacity 0.15s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }
</style>
