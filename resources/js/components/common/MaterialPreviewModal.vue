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
          <div v-else-if="material?.file_type === 'word'" class="p-8 max-w-3xl mx-auto">
            <div v-if="wordLoading" class="py-20 text-center">
              <div class="animate-spin w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full mx-auto mb-3"/>
              <p class="text-sm text-gray-400">Đang tải nội dung...</p>
            </div>
            <div v-else-if="wordError" class="py-16 text-center px-4">
              <div class="text-4xl mb-3">⚠️</div>
              <p class="text-gray-700 font-medium mb-1">Không thể xem trước file này</p>
              <p v-if="wordErrorMsg" class="text-xs text-red-500 mb-5 max-w-sm mx-auto">{{ wordErrorMsg }}</p>
              <a :href="material.url" :download="material.file_name"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                Tải về để xem
              </a>
            </div>
            <div v-else class="word-preview" v-html="wordHtml" />
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
const wordErrorMsg = ref('')

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
  wordErrorMsg.value = ''
  wordLoading.value = true
  try {
    const res = await fetch(mat.url, { credentials: 'same-origin' })
    if (!res.ok) throw new Error(`Server trả về lỗi ${res.status}`)

    const contentType = res.headers.get('content-type') ?? ''
    if (contentType.includes('text/html')) {
      throw new Error('Server trả về trang HTML thay vì file — kiểm tra đường dẫn storage.')
    }

    const buf = await res.arrayBuffer()

    // .docx là file ZIP — kiểm tra magic bytes PK (0x50 0x4B)
    const magic = new Uint8Array(buf, 0, 2)
    if (magic[0] !== 0x50 || magic[1] !== 0x4B) {
      throw new Error('File không đúng định dạng .docx (không phải ZIP).')
    }

    const mod = await import('mammoth')
    const mammoth = mod.default ?? mod
    const result = await mammoth.convertToHtml({ arrayBuffer: buf })
    wordHtml.value = result.value || '<p class="text-gray-400 italic">(Tài liệu trống)</p>'
  } catch (e) {
    console.error('[Word preview]', e)
    wordErrorMsg.value = e.message ?? 'Lỗi không xác định'
    wordError.value = true
  } finally {
    wordLoading.value = false
  }
}, { deep: true })
</script>

<style scoped>
.word-preview :deep(h1) { font-size: 1.5rem; font-weight: 700; margin: 1rem 0 0.5rem; }
.word-preview :deep(h2) { font-size: 1.25rem; font-weight: 600; margin: 0.875rem 0 0.4rem; }
.word-preview :deep(h3) { font-size: 1.1rem; font-weight: 600; margin: 0.75rem 0 0.35rem; }
.word-preview :deep(p)  { margin: 0.5rem 0; line-height: 1.7; color: #374151; }
.word-preview :deep(table) { width: 100%; border-collapse: collapse; margin: 1rem 0; }
.word-preview :deep(td), .word-preview :deep(th) { border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; font-size: 0.875rem; }
.word-preview :deep(th) { background: #f3f4f6; font-weight: 600; }
.word-preview :deep(ul), .word-preview :deep(ol) { padding-left: 1.5rem; margin: 0.5rem 0; }
.word-preview :deep(li) { margin: 0.2rem 0; line-height: 1.6; }
.word-preview :deep(strong) { font-weight: 600; }
.word-preview :deep(em) { font-style: italic; }
</style>
