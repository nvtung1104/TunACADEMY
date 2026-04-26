<template>
  <div v-if="meta.last_page > 1" class="flex items-center justify-between">
    <p class="text-sm text-gray-400">
      Tổng <span class="font-medium text-gray-700">{{ meta.total }}</span> kết quả
    </p>
    <div class="flex items-center gap-1.5">
      <button @click="$emit('page', meta.current_page - 1)" :disabled="meta.current_page === 1"
        class="p-2 rounded-lg border border-gray-200 text-gray-500 disabled:opacity-40 hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <template v-for="p in pages" :key="p">
        <span v-if="p === '...'" class="px-1 text-gray-400 text-sm">…</span>
        <button v-else @click="$emit('page', p)"
          class="w-9 h-9 rounded-lg border text-sm font-medium transition-colors"
          :class="p === meta.current_page ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-200 text-gray-700 hover:bg-gray-50'">
          {{ p }}
        </button>
      </template>
      <button @click="$emit('page', meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
        class="p-2 rounded-lg border border-gray-200 text-gray-500 disabled:opacity-40 hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ meta: { type: Object, required: true } })
defineEmits(['page'])

const pages = computed(() => {
  const { current_page: c, last_page: t } = props.meta
  if (t <= 7) return Array.from({ length: t }, (_, i) => i + 1)
  const p = [1]
  if (c > 3) p.push('...')
  for (let i = Math.max(2, c - 1); i <= Math.min(t - 1, c + 1); i++) p.push(i)
  if (c < t - 2) p.push('...')
  p.push(t)
  return p
})
</script>
