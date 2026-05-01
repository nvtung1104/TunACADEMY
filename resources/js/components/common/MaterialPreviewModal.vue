<template>
  <Teleport to="body">
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="$emit('update:modelValue', false)">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" />

      <!-- Modal -->
      <div class="relative bg-white rounded-2xl shadow-2xl flex flex-col w-full max-w-4xl max-h-[90vh]">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-white text-xs font-bold"
              :class="bgClass">
              {{ label }}
            </div>
            <div class="min-w-0">
              <p class="font-semibold text-gray-800 truncate">{{ material?.file_name }}</p>
              <p class="text-xs text-gray-400">{{ typeName }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0 ml-4">
            <a :href="material?.url" :download="material?.file_name"
              class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Tải về
            </a>
            <button @click="$emit('update:modelValue', false)"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-auto min-h-0">
          <!-- PDF preview -->
          <iframe v-if="material?.file_type === 'pdf'"
            :src="material.url"
            class="w-full h-full min-h-[70vh] border-0" />

          <!-- DOCX preview -->
          <div v-else-if="material?.file_type === 'word'" class="p-6">
            <div v-if="wordLoading" class="py-20 text-center">
              <div class="animate-spin w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full mx-auto mb-3"/>
              <p class="text-sm text-gray-400">Đang tải nội dung...</p>
            </div>
            <div v-else-if="wordError" class="py-20 text-center">
              <div class="text-4xl mb-3">⚠️</div>
              <p class="text-gray-500 mb-4">Không thể xem trước file này.</p>
              <a :href="material.url" :download="material.file_name"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                Tải về để xem
              </a>
            </div>
            <div v-else class="prose prose-sm max-w-none" v-html="wordHtml" />
          </div>

          <!-- PPT — no preview -->
          <div v-else-if="material?.file_type === 'ppt'" class="py-24 text-center px-6">
            <div class="w-20 h-20 rounded-2xl bg-orange-100 flex items-center justify-center mx-auto mb-5">
              <span class="text-3xl font-bold text-orange-500">PPT</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Không hỗ trợ xem trước</h3>
            <p class="text-gray-400 text-sm mb-6">File PowerPoint cần tải về để xem đầy đủ nội dung.</p>
            <a :href="material.url" :download="material.file_name"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Tải về
            </a>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  material: Object,
})
defineEmits(['update:modelValue'])

const wordHtml = ref('')
const wordLoading = ref(false)
const wordError = ref(false)

const bgClass = computed(() => ({
  pdf: 'bg-red-500', word: 'bg-blue-500', ppt: 'bg-orange-500'
}[props.material?.file_type] ?? 'bg-gray-400'))

const label = computed(() => ({
  pdf: 'PDF', word: 'DOC', ppt: 'PPT'
}[props.material?.file_type] ?? 'FILE'))

const typeName = computed(() => ({
  pdf: 'PDF Document', word: 'Word Document', ppt: 'PowerPoint'
}[props.material?.file_type] ?? 'Tài liệu'))

watch(() => [props.modelValue, props.material], async ([open, mat]) => {
  if (!open || !mat || mat.file_type !== 'word') return
  wordHtml.value = ''
  wordError.value = false
  wordLoading.value = true
  try {
    const mammoth = (await import('mammoth')).default
    const res = await fetch(mat.url)
    const buf = await res.arrayBuffer()
    const result = await mammoth.convertToHtml({ arrayBuffer: buf })
    wordHtml.value = result.value
  } catch {
    wordError.value = true
  } finally {
    wordLoading.value = false
  }
}, { deep: true })
</script>
