<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <input v-model="filters.search" @input="debouncedFetch" placeholder="Tìm môn học..."
          class="px-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#d63015] w-52" />
        <select v-model="filters.status" @change="fetch" class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#d63015]">
          <option value="">Tất cả trạng thái</option>
          <option value="active">Hoạt động</option>
          <option value="inactive">Vô hiệu</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Thêm môn học
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-16 text-center text-gray-400">
      <div class="animate-spin w-7 h-7 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-3"></div>
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
        class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group overflow-hidden">
        <!-- Cover / Color strip -->
        <div class="h-24 relative overflow-hidden">
          <img v-if="s.avatar_url" :src="s.avatar_url" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full" :style="{ backgroundColor: s.color || '#d63015' }"></div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
          <span class="absolute bottom-2 left-3 text-xs px-2 py-0.5 rounded-full font-medium"
            :class="s.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
            {{ s.status === 'active' ? 'Hoạt động' : 'Vô hiệu' }}
          </span>
        </div>

        <div class="p-4">
          <h3 class="font-semibold text-gray-900">{{ s.name }}</h3>
          <p class="text-xs text-gray-400 font-mono mt-0.5 mb-3">{{ s.code }}</p>

          <!-- Stats -->
          <div class="grid grid-cols-3 gap-2 mb-3">
            <div class="text-center py-1.5 bg-gray-50 rounded-lg">
              <p class="text-base font-bold text-gray-800">{{ s.lessons_count ?? 0 }}</p>
              <p class="text-[10px] text-gray-500">Bài học</p>
            </div>
            <div class="text-center py-1.5 bg-gray-50 rounded-lg">
              <p class="text-base font-bold text-gray-800">{{ s.exams_count ?? 0 }}</p>
              <p class="text-[10px] text-gray-500">Đề thi</p>
            </div>
            <div class="text-center py-1.5 bg-gray-50 rounded-lg">
              <p class="text-base font-bold text-gray-800">{{ s.assignments_count ?? 0 }}</p>
              <p class="text-[10px] text-gray-500">Bài tập</p>
            </div>
          </div>

          <!-- Applicable grades -->
          <div v-if="s.applicable_grades?.length" class="flex flex-wrap gap-1 mb-3">
            <span v-for="g in s.applicable_grades" :key="g"
              class="text-[10px] px-1.5 py-0.5 rounded-md bg-red-50 text-[#d63015] font-medium">
              Khối {{ g }}
            </span>
          </div>

          <!-- Actions -->
          <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click="openEdit(s)" class="flex-1 py-1.5 rounded-xl border border-gray-200 text-xs text-gray-600 hover:border-[#d63015] hover:text-[#d63015] transition-colors font-medium">
              Chỉnh sửa
            </button>
            <button @click="deleteSubject(s)" class="py-1.5 px-3 rounded-xl border border-red-200 text-xs text-red-500 hover:bg-red-50 transition-colors">
              Xóa
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa môn học' : 'Thêm môn học mới'" size="md">
      <form class="space-y-4">
        <!-- Avatar upload -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Ảnh đại diện môn học</label>
          <div class="flex gap-4 items-center">
            <div class="relative w-20 h-20 rounded-2xl overflow-hidden bg-gray-100 border-2 border-dashed border-gray-200 hover:border-[#d63015] cursor-pointer transition-colors shrink-0"
              @click="$refs.avatarInput.click()">
              <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" />
              <div v-else class="absolute inset-0 flex items-center justify-center">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <button v-if="avatarPreview" @click.stop="clearAvatar"
                class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-[10px] flex items-center justify-center">✕</button>
            </div>
            <div class="text-xs text-gray-500 leading-relaxed">
              <p>Click để chọn ảnh đại diện</p>
              <p class="text-gray-400">PNG, JPG, WEBP · Tối đa 5MB</p>
            </div>
          </div>
          <input ref="avatarInput" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="handleAvatarChange" />
        </div>

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
              <input v-model="form.color" class="input flex-1 font-mono text-xs" placeholder="#d63015" />
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Áp dụng cho khối</label>
          <div class="flex flex-wrap gap-2">
            <label v-for="g in 12" :key="g" class="flex items-center gap-1.5 cursor-pointer">
              <input type="checkbox" :value="g" v-model="form.applicable_grades" class="rounded text-[#d63015] focus:ring-[#d63015]" />
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
        <button @click="save" :disabled="saving" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] disabled:opacity-60 font-medium">
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
const form = reactive({ name: '', code: '', color: '#d63015', applicable_grades: [], status: 'active' })

const avatarPreview = ref(null)
const avatarFile = ref(null)

function handleAvatarChange(e) {
  const file = e.target.files?.[0]
  if (!file) return
  avatarFile.value = file
  avatarPreview.value = URL.createObjectURL(file)
  e.target.value = ''
}
function clearAvatar() {
  avatarPreview.value = null
  avatarFile.value = null
}

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
  Object.assign(form, { name: '', code: '', color: '#d63015', applicable_grades: [], status: 'active' })
  avatarPreview.value = null
  avatarFile.value = null
  formError.value = ''
  modal.value = true
}

function openEdit(s) {
  editItem.value = s
  Object.assign(form, {
    name: s.name,
    code: s.code,
    color: s.color || '#d63015',
    applicable_grades: s.applicable_grades ?? [],
    status: s.status,
  })
  avatarPreview.value = s.avatar_url ?? null
  avatarFile.value = null
  formError.value = ''
  modal.value = true
}

async function save() {
  saving.value = true
  formError.value = ''
  try {
    let subjectId
    if (editItem.value) {
      await api.put(`/admin/subjects/${editItem.value.id}`, form)
      subjectId = editItem.value.id
    } else {
      const { data } = await api.post('/admin/subjects', form)
      subjectId = data.data?.id
    }
    if (avatarFile.value && subjectId) {
      const fd = new FormData()
      fd.append('avatar', avatarFile.value)
      const { data: imgData } = await api.post(`/admin/subjects/${subjectId}/avatar`, fd)
      if (!editItem.value) {
        const idx = subjects.value.findIndex(s => s.id === subjectId)
        if (idx > -1) subjects.value[idx].avatar_url = imgData.data?.avatar_url
      }
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
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
