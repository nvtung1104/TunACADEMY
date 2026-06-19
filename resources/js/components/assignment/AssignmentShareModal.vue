<template>
  <AppModal v-model="show" title="Chia sẻ bài tập" size="sm">
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Chế độ chia sẻ</label>
        <div class="space-y-2">
          <label v-for="opt in visibilityOptions" :key="opt.value"
            class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
            :class="shareForm.visibility === opt.value ? 'border-[#d63015] bg-red-50' : 'border-gray-200 hover:border-gray-300'">
            <input type="radio" :value="opt.value" v-model="shareForm.visibility" class="mt-0.5 text-[#d63015]" />
            <div>
              <p class="text-sm font-medium text-gray-800">{{ opt.label }}</p>
              <p class="text-xs text-gray-400">{{ opt.desc }}</p>
            </div>
          </label>
        </div>
      </div>
      <div v-if="shareForm.visibility === 'class'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Chọn lớp</label>
        <select v-model="shareForm.classroom_id" class="input">
          <option value="">Chọn lớp</option>
          <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <div v-if="shareForm.visibility === 'selected'">
        <label class="block text-sm font-medium text-gray-700 mb-1">ID học sinh (cách nhau bởi dấu phẩy)</label>
        <input v-model="shareForm.student_codes" class="input" placeholder="VD: 12, 45, 78" />
      </div>
      <div v-if="shareError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ shareError }}</div>
    </div>
    <template #footer>
      <button @click="show = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
      <button @click="doShare" :disabled="sharing" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] disabled:opacity-60 font-medium">
        {{ sharing ? 'Đang lưu...' : 'Lưu chia sẻ' }}
      </button>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const props = defineProps({
  modelValue:  { type: Boolean, default: false },
  assignment:  { type: Object,  default: null },
  classrooms:  { type: Array,   default: () => [] },
})
const emit = defineEmits(['update:modelValue', 'shared'])

const show = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const sharing    = ref(false)
const shareError = ref('')
const shareForm  = reactive({ visibility: 'class', classroom_id: '', student_codes: '' })

const visibilityOptions = [
  { value: 'private',  label: 'Chưa chia sẻ',   desc: 'Để ở trạng thái riêng tư, chưa chia sẻ' },
  { value: 'class',    label: 'Cho lớp (chọn lớp)', desc: 'Chọn lớp học cụ thể để chia sẻ' },
  { value: 'public',   label: 'Công khai',      desc: 'Chia sẻ công khai cho tất cả mọi người' },
]

watch(() => props.modelValue, (open) => {
  if (!open) return
  shareError.value = ''
  Object.assign(shareForm, {
    visibility:    props.assignment?.visibility ?? 'class',
    classroom_id:  props.assignment?.classroom?.id ?? '',
    student_codes: '',
  })
})

async function doShare() {
  sharing.value = true; shareError.value = ''
  try {
    const payload = { visibility: shareForm.visibility, target_classroom_ids: [], target_student_ids: [] }
    if (shareForm.visibility === 'class' && shareForm.classroom_id) {
      payload.target_classroom_ids = [Number(shareForm.classroom_id)]
    }
    if (shareForm.visibility === 'selected') {
      payload.target_student_ids = shareForm.student_codes
        .split(',')
        .map(s => Number(s.trim()))
        .filter(v => Number.isInteger(v) && v > 0)
    }
    await api.post(`/teacher/assignments/${props.assignment.id}/share`, payload)
    show.value = false
    emit('shared')
  } catch (e) { shareError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { sharing.value = false }
}
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
