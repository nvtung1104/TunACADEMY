<template>
  <AppModal v-model="show" title="Kết quả kiểm tra" size="lg">
    <div v-if="loading" class="py-8 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>
      Đang tải...
    </div>
    <table v-else class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Học sinh</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Điểm</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500">Thời gian nộp</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <tr v-if="results.length === 0">
          <td colspan="3" class="py-8 text-center text-gray-400">Chưa có kết quả</td>
        </tr>
        <tr v-for="r in results" :key="r.id" class="hover:bg-gray-50">
          <td class="px-4 py-2.5">
            <p class="font-medium text-gray-800">{{ r.student?.name }}</p>
            <p class="text-xs text-gray-400">{{ r.student?.email }}</p>
          </td>
          <td class="px-4 py-2.5">
            <span class="font-bold text-lg" :class="(r.score ?? 0) >= 5 ? 'text-green-600' : 'text-red-500'">
              {{ r.score != null ? r.score : '—' }}
            </span>
            <span class="text-gray-400 text-xs">/10</span>
          </td>
          <td class="px-4 py-2.5 text-gray-400 text-xs">
            {{ r.submitted_at ? formatDate(r.submitted_at) : '—' }}
          </td>
        </tr>
      </tbody>
    </table>
  </AppModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  exam:       { type: Object,  default: null },
})

const emit = defineEmits(['update:modelValue'])

const show = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const loading = ref(false)
const results = ref([])

watch(() => props.modelValue, async (open) => {
  if (!open || !props.exam) return
  loading.value = true
  results.value = []
  try {
    const { data } = await api.get(`/teacher/exams/${props.exam.id}/attempts`)
    results.value = data.data?.data ?? data.data ?? []
  } finally {
    loading.value = false
  }
})

function formatDate(iso) {
  return new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' })
}
</script>
