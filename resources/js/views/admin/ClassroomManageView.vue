<template>
  <div class="space-y-4">
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
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tạo lớp học
      </button>
    </div>

    <!-- Cards -->
    <div v-if="loading" class="py-12 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>Đang tải...
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-if="classrooms.length === 0" class="col-span-full py-16 text-center text-gray-400">Chưa có lớp học nào</div>
      <div v-for="c in classrooms" :key="c.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-900">{{ c.name }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ c.school_year?.name }}</p>
          </div>
          <span class="text-xs px-2 py-1 rounded-full" :class="c.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
            {{ c.status === 'active' ? 'Hoạt động' : 'Vô hiệu' }}
          </span>
        </div>
        <div class="flex items-center gap-4 text-sm text-gray-600 mb-4">
          <span>🎒 {{ c.students_count ?? 0 }}/{{ c.max_students }} HS</span>
          <span>📚 Khối {{ c.grade?.level }}</span>
        </div>
        <div class="flex gap-2">
          <button @click="openEdit(c)" class="flex-1 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:border-indigo-300 hover:text-indigo-600 transition-colors">Chỉnh sửa</button>
          <button @click="deleteClassroom(c)" class="py-1.5 px-3 rounded-lg border border-red-200 text-xs text-red-500 hover:bg-red-50 transition-colors">Xóa</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
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
            <input v-model.number="form.max_students" type="number" min="1" max="50" class="input" />
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
        <button @click="save" :disabled="saving" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm hover:bg-indigo-700 disabled:opacity-60">
          {{ saving ? 'Đang lưu...' : 'Lưu' }}
        </button>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const classrooms = ref([])
const grades = ref([])
const schoolYears = ref([])
const loading = ref(true)
const modal = ref(false)
const editItem = ref(null)
const saving = ref(false)
const formError = ref('')
const filters = reactive({ school_year_id: '', status: '' })
const form = reactive({ name: '', grade_id: '', school_year_id: '', max_students: 40, status: 'active' })

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/classrooms', { params: filters })
    classrooms.value = data.data?.data ?? []
  } finally { loading.value = false }
}

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
    else await api.post('/admin/classrooms', form)
    modal.value = false; fetch()
  } catch (e) { formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

async function deleteClassroom(c) {
  if (!confirm(`Xóa lớp "${c.name}"?`)) return
  await api.delete(`/admin/classrooms/${c.id}`)
  fetch()
}

onMounted(async () => {
  const [g, sy] = await Promise.all([api.get('/admin/grades'), api.get('/admin/school-years')])
  grades.value = g.data.data ?? []
  schoolYears.value = sy.data.data ?? []
  fetch()
})
</script>

<style scoped>
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm; }
</style>
