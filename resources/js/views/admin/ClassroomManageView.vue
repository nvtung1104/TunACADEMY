<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <select v-model="filters.school_year_id" @change="fetch" class="input w-44">
          <option value="">Tất cả năm học</option>
          <option v-for="sy in schoolYears" :key="sy.id" :value="sy.id">{{ sy.name }}</option>
        </select>
        <select v-model="filters.status" @change="fetch" class="input w-40">
          <option value="">Tất cả trạng thái</option>
          <option value="active">Hoạt động</option>
          <option value="inactive">Vô hiệu</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tạo lớp học
      </button>
    </div>

    <!-- Cards -->
    <div v-if="loading" class="py-12 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"/>Đang tải...
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-if="classrooms.length === 0" class="col-span-full py-16 text-center text-gray-400">Chưa có lớp học nào</div>
      <div v-for="c in classrooms" :key="c.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-900">{{ c.name }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ c.school_year?.name }} · Khối {{ c.grade?.level }}</p>
          </div>
          <span class="text-xs px-2 py-1 rounded-full" :class="c.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
            {{ c.status === 'active' ? 'Hoạt động' : 'Vô hiệu' }}
          </span>
        </div>

        <!-- Student count bar -->
        <div class="mb-4">
          <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
            <span>Học sinh</span>
            <span class="font-semibold" :class="(c.students_count ?? 0) >= c.max_students ? 'text-red-600' : 'text-gray-700'">
              {{ c.students_count ?? 0 }}/{{ c.max_students }}
            </span>
          </div>
          <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all"
              :style="{ width: Math.min(((c.students_count ?? 0) / c.max_students) * 100, 100) + '%' }"
              :class="(c.students_count ?? 0) >= c.max_students ? 'bg-red-400' : 'bg-[#d63015]'"/>
          </div>
        </div>

        <div class="flex gap-2">
          <button @click="openStudents(c)"
            class="flex-1 py-1.5 rounded-lg bg-indigo-50 text-xs text-indigo-700 hover:bg-indigo-100 font-medium transition-colors flex items-center justify-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Học sinh
          </button>
          <button @click="openEdit(c)" class="py-1.5 px-3 rounded-lg border border-gray-200 text-xs text-gray-600 hover:border-[#d63015] hover:text-[#d63015] transition-colors">Sửa</button>
          <button @click="deleteClassroom(c)" class="py-1.5 px-3 rounded-lg border border-red-200 text-xs text-red-500 hover:bg-red-50 transition-colors">Xóa</button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa lớp học' : 'Tạo lớp học mới'">
      <form class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tên lớp</label>
          <input v-model="form.name" class="input" placeholder="VD: 10A1" required maxlength="20" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Khối lớp</label>
            <select v-model="form.grade_id" class="input" required>
              <option value="">Chọn khối</option>
              <option v-for="g in grades" :key="g.id" :value="g.id">Khối {{ g.level }} - {{ g.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Năm học</label>
            <select v-model="form.school_year_id" class="input" required>
              <option value="">Chọn năm học</option>
              <option v-for="sy in schoolYears" :key="sy.id" :value="sy.id">{{ sy.name }}</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sĩ số tối đa</label>
            <input v-model.number="form.max_students" type="number" min="1" max="60" class="input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
            <select v-model="form.status" class="input">
              <option value="active">Hoạt động</option>
              <option value="inactive">Vô hiệu</option>
            </select>
          </div>
        </div>
        <div v-if="formError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ formError }}</div>
      </form>
      <template #footer>
        <button @click="modal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
        <button @click="save" :disabled="saving" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] disabled:opacity-60">
          {{ saving ? 'Đang lưu...' : 'Lưu' }}
        </button>
      </template>
    </AppModal>

    <!-- Student Management Modal -->
    <AppModal v-model="studentModal" :title="`Học sinh — ${selectedClass?.name ?? ''}`">
      <div class="space-y-4">
        <!-- Search to add -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Thêm học sinh</label>
          <div class="flex gap-2">
            <div class="relative flex-1">
              <input v-model="studentSearch" @input="debounceSearch" type="text"
                placeholder="Tìm theo tên hoặc email..."
                class="input pr-8" />
              <div v-if="searchLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                <div class="w-3.5 h-3.5 border border-gray-400 border-t-transparent rounded-full animate-spin"/>
              </div>
            </div>
          </div>
          <!-- Search results dropdown -->
          <div v-if="searchResults.length" class="mt-1 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <div v-for="u in searchResults" :key="u.id"
              class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 border-b border-gray-50 last:border-0">
              <div>
                <p class="text-sm font-medium text-gray-800">{{ u.name }}</p>
                <p class="text-xs text-gray-400">{{ u.email }}</p>
              </div>
              <button @click="addStudent(u)"
                :disabled="addingId === u.id || enrolledIds.has(u.id)"
                class="text-xs px-2.5 py-1 rounded-lg font-medium transition-colors"
                :class="enrolledIds.has(u.id)
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  : 'bg-indigo-600 hover:bg-indigo-700 text-white'">
                {{ enrolledIds.has(u.id) ? 'Đã có' : (addingId === u.id ? '...' : 'Thêm') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Enrolled students list -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-gray-700">
              Danh sách hiện tại
              <span class="ml-1 text-xs text-gray-400">({{ enrolledStudents.length }} học sinh)</span>
            </p>
          </div>
          <div v-if="studentsLoading" class="py-6 text-center text-gray-400 text-sm">Đang tải...</div>
          <div v-else-if="!enrolledStudents.length" class="py-6 text-center text-gray-400 text-sm">
            Chưa có học sinh nào trong lớp
          </div>
          <div v-else class="space-y-1.5 max-h-64 overflow-y-auto">
            <div v-for="s in enrolledStudents" :key="s.id"
              class="flex items-center justify-between px-3 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-700 shrink-0">
                  {{ s.name?.charAt(0)?.toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-800 truncate">{{ s.name }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ s.email }}</p>
                </div>
              </div>
              <button @click="removeStudent(s.id)"
                :disabled="removingId === s.id"
                class="shrink-0 ml-2 text-xs px-2 py-1 rounded-lg text-red-500 hover:bg-red-50 transition-colors disabled:opacity-40">
                {{ removingId === s.id ? '...' : 'Xóa' }}
              </button>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <button @click="studentModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Đóng</button>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

// ── List ──────────────────────────────────────────────────
const classrooms    = ref([])
const grades        = ref([])
const schoolYears   = ref([])
const loading       = ref(true)
const filters       = reactive({ school_year_id: '', status: '' })

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/classrooms', { params: filters })
    classrooms.value = data.data ?? []
  } finally { loading.value = false }
}

// ── Create / Edit ─────────────────────────────────────────
const modal     = ref(false)
const editItem  = ref(null)
const saving    = ref(false)
const formError = ref('')
const form      = reactive({ name: '', grade_id: '', school_year_id: '', max_students: 40, status: 'active' })

function openCreate() {
  editItem.value = null
  Object.assign(form, { name: '', grade_id: '', school_year_id: '', max_students: 40, status: 'active' })
  formError.value = ''
  modal.value = true
}

function openEdit(c) {
  editItem.value = c
  Object.assign(form, { name: c.name, grade_id: c.grade?.id ?? '', school_year_id: c.school_year?.id ?? '', max_students: c.max_students, status: c.status })
  formError.value = ''
  modal.value = true
}

async function save() {
  saving.value = true; formError.value = ''
  try {
    if (editItem.value) await api.put(`/admin/classrooms/${editItem.value.id}`, form)
    else                await api.post('/admin/classrooms', form)
    modal.value = false; fetch()
  } catch (e) { formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

async function deleteClassroom(c) {
  if (!confirm(`Xóa lớp "${c.name}"?`)) return
  try { await api.delete(`/admin/classrooms/${c.id}`); fetch() }
  catch (e) { alert(e.response?.data?.message ?? 'Không thể xóa') }
}

// ── Student management ────────────────────────────────────
const studentModal      = ref(false)
const selectedClass     = ref(null)
const enrolledStudents  = ref([])
const studentsLoading   = ref(false)
const studentSearch     = ref('')
const searchResults     = ref([])
const searchLoading     = ref(false)
const addingId          = ref(null)
const removingId        = ref(null)

const enrolledIds = computed(() => new Set(enrolledStudents.value.map(s => s.id)))

async function openStudents(c) {
  selectedClass.value = c
  studentSearch.value = ''
  searchResults.value = []
  studentModal.value  = true
  await loadEnrolled()
}

async function loadEnrolled() {
  studentsLoading.value = true
  try {
    const { data } = await api.get(`/admin/classrooms/${selectedClass.value.id}`)
    const classroom = data.data ?? data
    enrolledStudents.value = classroom.students ?? []
    // sync count on card
    const card = classrooms.value.find(c => c.id === selectedClass.value.id)
    if (card) card.students_count = enrolledStudents.value.length
  } finally { studentsLoading.value = false }
}

let searchTimer = null
function debounceSearch() {
  clearTimeout(searchTimer)
  if (!studentSearch.value.trim()) { searchResults.value = []; return }
  searchTimer = setTimeout(searchStudents, 400)
}

async function searchStudents() {
  searchLoading.value = true
  try {
    const { data } = await api.get('/admin/users', { params: { role: 'student', search: studentSearch.value, per_page: 8 } })
    searchResults.value = data.data?.data ?? data.data ?? []
  } finally { searchLoading.value = false }
}

async function addStudent(u) {
  addingId.value = u.id
  try {
    await api.post(`/admin/classrooms/${selectedClass.value.id}/students`, { student_id: u.id })
    await loadEnrolled()
    searchResults.value = searchResults.value.filter(s => s.id !== u.id)
  } catch (e) { alert(e.response?.data?.message ?? 'Không thể thêm học sinh') }
  finally { addingId.value = null }
}

async function removeStudent(studentId) {
  if (!confirm('Xóa học sinh khỏi lớp?')) return
  removingId.value = studentId
  try {
    await api.delete(`/admin/classrooms/${selectedClass.value.id}/students/${studentId}`)
    await loadEnrolled()
  } catch (e) { alert(e.response?.data?.message ?? 'Không thể xóa học sinh') }
  finally { removingId.value = null }
}

// ── Init ──────────────────────────────────────────────────
onMounted(async () => {
  const [g, sy] = await Promise.all([api.get('/admin/grades'), api.get('/admin/school-years')])
  grades.value      = g.data.data  ?? []
  schoolYears.value = sy.data.data ?? []
  fetch()
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
