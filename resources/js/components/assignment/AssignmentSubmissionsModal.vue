<template>
  <AppModal v-model="show" :title="`Danh sách nộp bài${assignment ? ' — ' + assignment.title : ''}`" size="lg">
    <div v-if="loading" class="py-8 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>
      Đang tải...
    </div>
    <table v-else class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Học sinh</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Thời gian nộp</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Điểm</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Nhận xét AI</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <tr v-if="submissions.length === 0">
          <td colspan="4" class="py-8 text-center text-gray-400">Chưa có bài nộp</td>
        </tr>
        <tr v-for="s in submissions" :key="s.id" class="hover:bg-gray-50">
          <td class="px-4 py-2.5">
            <p class="font-medium text-gray-800">{{ s.student?.name }}</p>
          </td>
          <td class="px-4 py-2.5 text-gray-400 text-xs">{{ formatDate(s.submitted_at) }}</td>
          <td class="px-4 py-2.5">
            <span v-if="s.score != null" class="font-bold" :class="s.score >= 5 ? 'text-green-600' : 'text-red-500'">{{ s.score }}</span>
            <span v-else class="text-gray-400 text-xs">Chưa chấm</span>
          </td>
          <td class="px-4 py-2.5 text-xs text-gray-500 max-w-xs truncate">
            {{ s.ai_evaluation?.competency_comment || s.feedback || '—' }}
          </td>
        </tr>
      </tbody>
    </table>
    <template #footer>
      <button @click="show = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Đóng</button>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  assignment: { type: Object,  default: null },
})
const emit = defineEmits(['update:modelValue'])

const show = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const loading     = ref(false)
const submissions = ref([])

watch(() => props.modelValue, async (open) => {
  if (!open || !props.assignment) return
  loading.value = true
  submissions.value = []
  try {
    const { data } = await api.get(`/teacher/assignments/${props.assignment.id}/submissions`)
    submissions.value = data.data ?? []
  } finally { loading.value = false }
})

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : ''
}
</script>
