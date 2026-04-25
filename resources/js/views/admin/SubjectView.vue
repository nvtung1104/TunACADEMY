<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <input v-model="filters.search" @input="debouncedFetch" placeholder="Tìm môn học..."
          class="px-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-52" />
        <select v-model="filters.status" @change="fetch" class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">Tất cả trạng thái</option>
          <option value="active">Hoạt động</option>
          <option value="inactive">Vô hiệu</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Thêm môn học
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-16 text-center text-gray-400">
      <div class="animate-spin w-7 h-7 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-3"></div>
      Đang tải...
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div v-if="subjects.length === 0" class="col-span-full py-16 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <p class="text-gray-400">Chưa có môn học nào</p>
      </div>

      <div v-for="s in subjects" :key="s.id"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-all group">
        <!-- Color strip -->
        <div class="w-full h-1.5 rounded-full mb-4" :style="{ backgroundColor: s.color || '#6366f1' }"></div>

        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-900">{{ s.name }}</h3>
            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ s.code }}</p>
          </div>
          <span class="text-xs px-2 py-0.5 rounded-full font-medium"
            :class="s.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
            {{ s.status === 'active' ? 'Hoạt động' : 'Vô hiệu' }}
          </span>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-2 mb-4">
          <div class="text-center py-2 bg-gray-50 rounded-xl">
            <p class="text-lg font-bold text-gray-800">{{ s.lessons_count ?? 0 }}</p>
            <p class="text-[10px] text-gray-500">Bài học</p>
          </div>
          <div class="text-center py-2 bg-gray-50 rounded-xl">
            <p class="text-lg font-bold text-gray-800">{{ s.exams_count ?? 0 }}</p>
            <p class="text-[10px] text-gray-500">Đề thi</p>
          </div>
          <div class="text-center py-2 bg-gray-50 rounded-xl">
            <p class="text-lg font-bold text-gray-800">{{ s.assignments_count ?? 0 }}</p>
            <p class="text-[10px] text-gray-500">Bài tập</p>
          </div>
        </div>

        <!-- Applicable grades -->
        <div v-if="s.applicable_grades?.length" class="flex flex-wrap gap-1 mb-4">
          <span v-for="g in s.applicable_grades" :key="g"
            class="text-[10px] px-1.5 py-0.5 rounded-md bg-indigo-50 text-indigo-600 font-medium">
            Khối {{ g }}
          </span>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
          <button @click="openEdit(s)" class="flex-1 py-1.5 rounded-xl border border-gray-200 text-xs text-gray-600 hover:border-indigo-300 hover:text-indigo-600 transition-colors font-medium">
            Chỉnh sửa
          </button>
          <button @click="deleteSubject(s)" class="py-1.5 px-3 rounded-xl border border-red-200 text-xs text-red-500 hover:bg-red-50 transition-colors">
            Xóa
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa môn học' : 'Thêm môn học mới'" size="md">
      <form class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tên môn học <span class="text-red-500">*</span></label>
            <input v-model="form.name" class="input" placeholder="VD: Toán học" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mã môn <span class="text-red-500">*</span></label>
            <input v-model="form.code" class="input font-mono" placeholder="VD: MATH" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Màu sắc</label>
            <div class="flex gap-2 items-center">
              <input v-model="form.color" type="color" class="h-10 w-14 rounded-xl border border-gray-200 cursor-pointer p-1" />
              <input v-model="form.color" class="input flex-1 font-mono text-xs" placeholder="#6366f1" />
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Áp dụng cho khối</label>
          <div class="flex flex-wrap gap-2">
            <label v-for="g in 12" :key="g" class="flex items-center gap-1.5 cursor-pointer">
              <input type="checkbox" :value="g" v-model="form.applicable_grades" class="rounded text-indigo-600 focus:ring-indigo-500" />
              <span class="text-sm text-gray-700">Khối {{ g }}</span>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
          <select v-model="form.status" class="input">
            <option value="active">Hoạt động</option>
            <option value="inactive">Vô hiệu</option>
          </select>
        </div>

        <div v-if="formError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ formError }}</div>
      </form>
      <template #footer>
        <button @click="modal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
        <button @click="save" :disabled="saving" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm hover:bg-indigo-700 disabled:opacity-60 font-medium">
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

const subjects = ref([])
const loading = ref(true)
const modal = ref(false)
const editItem = ref(null)
const saving = ref(false)
const formError = ref('')
const filters = reactive({ search: '', status: '' })
const form = reactive({ name: '', code: '', color: '#6366f1', applicable_grades: [], status: 'active' })

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetch, 400)
}

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/subjects', { params: filters })
    subjects.value = data.data ?? []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editItem.value = null
  Object.assign(form, { name: '', code: '', color: '#6366f1', applicable_grades: [], status: 'active' })
  formError.value = ''
  modal.value = true
}

function openEdit(s) {
  editItem.value = s
  Object.assign(form, {
    name: s.name,
    code: s.code,
    color: s.color || '#6366f1',
    applicable_grades: s.applicable_grades ?? [],
    status: s.status,
  })
  formError.value = ''
  modal.value = true
}

async function save() {
  saving.value = true
  formError.value = ''
  try {
    if (editItem.value) {
      await api.put(`/admin/subjects/${editItem.value.id}`, form)
    } else {
      await api.post('/admin/subjects', form)
    }
    modal.value = false
    fetch()
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra'
  } finally {
    saving.value = false
  }
}

async function deleteSubject(s) {
  if (!confirm(`Xóa môn học "${s.name}"?`)) return
  try {
    await api.delete(`/admin/subjects/${s.id}`)
    fetch()
  } catch (e) {
    alert(e.response?.data?.message ?? 'Không thể xóa')
  }
}

onMounted(fetch)
</script>

<style scoped>
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm; }
</style>
