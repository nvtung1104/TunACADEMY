<template>
  <div class="space-y-4">
    <!-- Filter tabs -->
    <div class="flex gap-2">
      <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
        class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
        :class="activeTab === tab.value ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-300'">
        {{ tab.label }}
      </button>
    </div>

    <!-- List -->
    <div v-if="loading" class="py-12 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>
      Đang tải...
    </div>
    <div v-else class="space-y-3">
      <div v-if="filteredAssignments.length === 0" class="py-16 text-center bg-white rounded-2xl border border-gray-100 shadow-sm text-gray-400">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
        </svg>
        Không có bài tập nào
      </div>

      <div v-for="a in filteredAssignments" :key="a.id"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-all">
        <div class="flex items-start gap-4">
          <!-- Priority indicator -->
          <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
            :class="dueBg(a.due_date, a.submitted)">
            <svg class="w-6 h-6" :class="dueColor(a.due_date, a.submitted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div>
                <h3 class="font-semibold text-gray-900">{{ a.title }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ a.classroom?.name }}</p>
              </div>
              <span class="px-2 py-1 rounded-full text-xs font-medium shrink-0"
                :class="a.submitted ? 'bg-green-100 text-green-700' : isOverdue(a.due_date) ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700'">
                {{ a.submitted ? 'Đã nộp' : isOverdue(a.due_date) ? 'Quá hạn' : 'Chờ nộp' }}
              </span>
            </div>

            <p v-if="a.description" class="text-xs text-gray-500 mt-1.5 line-clamp-2">{{ a.description }}</p>

            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
              <span v-if="a.due_date" class="flex items-center gap-1" :class="isOverdue(a.due_date) && !a.submitted ? 'text-red-500 font-medium' : ''">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Hạn: {{ formatDate(a.due_date) }}
              </span>
              <span v-if="a.my_submission?.score != null" class="font-medium text-green-600">
                Điểm: {{ a.my_submission.score }}/10
              </span>
            </div>
          </div>

          <!-- Action -->
          <div class="shrink-0">
            <button v-if="!a.submitted && !isOverdue(a.due_date)" @click="submitModal = true; selectedAssignment = a"
              class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-colors">
              Nộp bài
            </button>
            <span v-else-if="a.submitted" class="text-xs text-gray-400">
              Nộp {{ formatDate(a.my_submission?.submitted_at) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Submit Modal -->
    <AppModal v-model="submitModal" :title="`Nộp bài: ${selectedAssignment?.title ?? ''}`" size="md">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">Nhập câu trả lời hoặc mô tả bài làm của bạn:</p>
        <textarea v-model="submitContent" rows="5" placeholder="Nhập nội dung bài làm..."
          class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm resize-none"></textarea>
        <div v-if="submitError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ submitError }}</div>
      </div>
      <template #footer>
        <button @click="submitModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
        <button @click="submitAssignment" :disabled="submitting" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm hover:bg-indigo-700 disabled:opacity-60 font-medium">
          {{ submitting ? 'Đang nộp...' : 'Nộp bài' }}
        </button>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const assignments = ref([])
const loading = ref(true)
const activeTab = ref('all')
const submitModal = ref(false)
const selectedAssignment = ref(null)
const submitContent = ref('')
const submitting = ref(false)
const submitError = ref('')

const tabs = [
  { value: 'all', label: 'Tất cả' },
  { value: 'pending', label: 'Chờ nộp' },
  { value: 'submitted', label: 'Đã nộp' },
]

const filteredAssignments = computed(() => {
  if (activeTab.value === 'pending') return assignments.value.filter(a => !a.submitted)
  if (activeTab.value === 'submitted') return assignments.value.filter(a => a.submitted)
  return assignments.value
})

function isOverdue(due) { return due && new Date(due) < new Date() }
function formatDate(iso) { return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '' }
function dueBg(due, submitted) {
  if (submitted) return 'bg-green-50'
  if (isOverdue(due)) return 'bg-red-50'
  return 'bg-amber-50'
}
function dueColor(due, submitted) {
  if (submitted) return 'text-green-500'
  if (isOverdue(due)) return 'text-red-500'
  return 'text-amber-500'
}

async function submitAssignment() {
  if (!selectedAssignment.value) return
  submitting.value = true
  submitError.value = ''
  try {
    await api.post(`/student/assignments/${selectedAssignment.value.id}/submit`, { content: submitContent.value })
    submitModal.value = false
    submitContent.value = ''
    fetch()
  } catch (e) {
    submitError.value = e.response?.data?.message ?? 'Không thể nộp bài'
  } finally { submitting.value = false }
}

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/student/assignments')
    assignments.value = data.data?.data ?? data.data ?? []
  } finally { loading.value = false }
}

onMounted(fetch)
</script>

