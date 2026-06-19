<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <select v-model="filters.classroom_id" @change="fetch" class="input w-44">
          <option value="">Tất cả lớp</option>
          <option v-for="c in classrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <select v-model="filters.status" @change="fetch" class="input w-40">
          <option value="">Tất cả trạng thái</option>
          <option value="draft">Bản nháp</option>
          <option value="published">Đã đăng</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Giao bài tập
      </button>
    </div>

    <!-- Cards grid -->
    <div v-if="loading" class="py-12 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>
      Đang tải...
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">
      <div v-if="assignments.length === 0" class="col-span-full py-20 text-center text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
        </svg>
        <p class="font-semibold text-gray-500">Chưa có bài tập nào</p>
        <p class="text-xs text-gray-400 mt-1">Nhấp vào "Giao bài tập" để giao bài tập đầu tiên của bạn.</p>
      </div>
      <div v-for="a in assignments" :key="a.id" class="bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-md hover:border-gray-200/80 transition-all duration-300 flex flex-col justify-between overflow-hidden min-h-[300px] group">
        <!-- Top area: Content & info -->
        <div class="p-6 pb-5 flex-1 flex flex-col justify-between">
          <div>
            <div class="flex items-start justify-between gap-3 mb-2.5">
              <div class="flex-1 min-w-0">
                <RouterLink :to="'/teacher/assignments/' + a.id" class="font-bold text-gray-900 hover:text-[#d63015] hover:underline transition-colors block text-base leading-snug line-clamp-2" :title="a.title">
                  {{ a.title }}
                </RouterLink>
                <p class="text-xs text-gray-400 mt-1 flex items-center gap-1 font-medium">
                  <span>{{ a.classroom?.name ?? '—' }}</span>
                  <span class="text-gray-300">·</span>
                  <span>{{ a.subject?.name ?? '—' }}</span>
                </p>
              </div>
            </div>

            <!-- Badges row -->
            <div class="flex flex-wrap items-center gap-1.5 mb-4">
              <span class="text-[10px] px-2 py-0.5 rounded-lg shrink-0 font-extrabold border" :class="dueBadgeClass(a.due_date)">
                {{ dueBadgeLabel(a.due_date) }}
              </span>
              <span class="text-[10px] px-2 py-0.5 rounded-lg shrink-0 font-extrabold border" :class="visibilityClass(a.visibility)">
                {{ visibilityLabel(a.visibility) }}
              </span>
              <span class="text-[10px] px-2 py-0.5 rounded-lg shrink-0 font-extrabold border" :class="a.status === 'published' ? 'bg-green-50 text-green-700 border-green-200/60' : 'bg-amber-50 text-amber-700 border-amber-200/60'">
                {{ a.status === 'published' ? 'Đã đăng' : 'Bản nháp' }}
              </span>
            </div>

            <!-- Description -->
            <p v-if="a.description" class="text-xs text-gray-500 mb-4 line-clamp-2 leading-relaxed bg-gray-50/50 p-2.5 rounded-xl border border-gray-100/50">{{ a.description }}</p>
          </div>

          <!-- Details list -->
          <div class="border-t border-dashed border-gray-100 pt-4 mt-auto space-y-2.5 text-xs text-gray-500">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span>Hạn nộp:</span>
              <strong class="text-gray-800 ml-auto">{{ a.due_date ? formatDate(a.due_date) : 'Không có' }}</strong>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
              <span>Lượt nộp bài:</span>
              <strong class="text-gray-800 ml-auto">{{ a.submissions_count ?? 0 }} lượt</strong>
            </div>
          </div>
        </div>

        <!-- Footer Actions: Permanently visible -->
        <div class="bg-gray-50/50 border-t border-gray-100 px-6 py-4 flex items-center justify-between gap-1.5">
          <RouterLink :to="'/teacher/assignments/' + a.id" class="flex-1 py-2 px-3 rounded-xl bg-gray-900 text-white hover:bg-black text-xs font-bold text-center transition-all shadow-sm">
            Chi tiết
          </RouterLink>
          <button @click="openSubmissions(a)" class="p-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-500 hover:text-emerald-600 hover:border-emerald-200 transition-all shrink-0" title="Xem nộp bài">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </button>
          <button @click="openShare(a)" class="p-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-500 hover:text-blue-600 hover:border-blue-200 transition-all shrink-0" title="Chia sẻ">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
          </button>
          <button @click="openEdit(a)" class="p-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-500 hover:text-indigo-655 hover:border-indigo-200 transition-all shrink-0" title="Chỉnh sửa">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>
          <button @click="deleteAssignment(a)" class="p-2 rounded-xl border border-gray-200 bg-white hover:bg-red-50 text-gray-400 hover:text-red-650 hover:border-red-200 transition-all shrink-0" title="Xóa bài tập">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <AssignmentFormModal
      v-model="modal"
      :edit-item="editItem"
      :subjects="subjects"
      :all-classrooms="allClassrooms"
      @saved="fetch"
    />
    <AssignmentShareModal
      v-model="shareModal"
      :assignment="shareTarget"
      :classrooms="classrooms"
      @shared="fetch"
    />
    <AssignmentSubmissionsModal
      v-model="subModal"
      :assignment="subTarget"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@api/axios'
import { useToastStore }   from '@stores/toast'
import { useConfirmStore } from '@stores/confirm'
import AssignmentFormModal        from '@components/assignment/AssignmentFormModal.vue'
import AssignmentShareModal       from '@components/assignment/AssignmentShareModal.vue'
import AssignmentSubmissionsModal from '@components/assignment/AssignmentSubmissionsModal.vue'

const toast        = useToastStore()
const confirmStore = useConfirmStore()

const assignments   = ref([])
const classrooms    = ref([])
const allClassrooms = ref([])
const subjects      = ref([])
const loading       = ref(true)
const filters       = reactive({ classroom_id: '', status: '' })

const modal      = ref(false)
const shareModal = ref(false)
const subModal   = ref(false)
const editItem   = ref(null)
const shareTarget = ref(null)
const subTarget  = ref(null)

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/assignments', { params: filters })
    assignments.value = data.data?.data ?? data.data ?? []
  } finally { loading.value = false }
}

function openCreate() {
  editItem.value = null
  modal.value    = true
}

function openEdit(a) {
  editItem.value = a
  modal.value    = true
}

function openShare(a) {
  shareTarget.value = a
  shareModal.value  = true
}

function openSubmissions(a) {
  subTarget.value = a
  subModal.value  = true
}

async function deleteAssignment(a) {
  if (!await confirmStore.ask(`Xóa bài tập "${a.title}"?`)) return
  try { await api.delete(`/teacher/assignments/${a.id}`); fetch() }
  catch (e) { toast.error(e.response?.data?.message ?? 'Không thể xóa') }
}

function visibilityLabel(v) {
  return { public: 'Công khai', private: 'Riêng tư', class: 'Lớp học', selected: 'Được chọn' }[v] ?? v
}
function visibilityClass(v) {
  return {
    public: 'bg-teal-50 text-teal-700 border-teal-200/60',
    private: 'bg-gray-55 text-gray-500 border-gray-200',
    class: 'bg-indigo-50 text-indigo-700 border-indigo-200/60',
    selected: 'bg-rose-50 text-[#d63015] border-red-200/60'
  }[v] ?? 'bg-gray-55 text-gray-550 border-gray-200'
}
function formatDate(iso) { return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '' }

function dueBadgeClass(due) {
  if (!due) return 'bg-gray-50 text-gray-500 border-gray-200'
  const diff = new Date(due) - new Date()
  if (diff < 0) return 'bg-rose-50 text-rose-600 border-rose-200/60'
  if (diff < 86400000) return 'bg-amber-50 text-amber-600 border-amber-200/60'
  return 'bg-emerald-50 text-emerald-600 border-emerald-200/60'
}
function dueBadgeLabel(due) {
  if (!due) return 'Không hạn'
  const diff = new Date(due) - new Date()
  if (diff < 0) return 'Đã hết hạn'
  if (diff < 86400000) return 'Sắp hết hạn'
  return 'Còn hạn'
}

onMounted(async () => {
  const [cr, allCr, sr] = await Promise.all([
    api.get('/teacher/classrooms').catch(() => ({ data: { data: [] } })),
    api.get('/teacher/all-classrooms').catch(() => ({ data: { data: [] } })),
    api.get('/public/subjects').catch(() => ({ data: { data: [] } })),
  ])
  classrooms.value    = cr.data.data?.data ?? cr.data.data ?? []
  allClassrooms.value = allCr.data.data ?? []
  subjects.value      = sr.data.data ?? []
  fetch()
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
