<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <input v-model="filters.search" @input="debouncedFetch" placeholder="Tìm tên, email..."
          class="px-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#d63015] w-56" />
        <select v-model="filters.role" @change="fetch" class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#d63015]">
          <option value="">Tất cả vai trò</option>
          <option value="admin">Admin</option>
          <option value="teacher">Giáo viên</option>
          <option value="student">Học sinh</option>
        </select>
        <select v-model="filters.status" @change="fetch" class="px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#d63015]">
          <option value="">Tất cả trạng thái</option>
          <option value="active">Hoạt động</option>
          <option value="inactive">Vô hiệu</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Thêm người dùng
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="py-12 text-center text-gray-400">
        <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>
        Đang tải...
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người dùng</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vai trò</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="users.length === 0"><td colspan="4" class="py-12 text-center text-gray-400">Không có dữ liệu</td></tr>
          <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center font-semibold text-[#d63015] text-sm uppercase shrink-0">
                  {{ u.name?.charAt(0) }}
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ u.name }}</p>
                  <p class="text-xs text-gray-400">{{ u.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium" :class="roleClass(u.roles?.[0])">
                {{ roleLabel(u.roles?.[0]) }}
              </span>
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium"
                :class="u.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                {{ u.status === 'active' ? 'Hoạt động' : 'Vô hiệu' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex justify-end gap-1">
                <button @click="openEdit(u)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-[#d63015] transition-colors" title="Chỉnh sửa">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button @click="toggleStatus(u)" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors" :title="u.status === 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </button>
                <button @click="deleteUser(u)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Xóa">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ pagination.total }} người dùng</p>
        <div class="flex gap-1">
          <button v-for="p in pagination.last_page" :key="p" @click="goPage(p)"
            class="w-8 h-8 rounded-lg text-xs font-medium transition-colors"
            :class="p === pagination.current_page ? 'bg-[#d63015] text-white' : 'hover:bg-gray-100 text-gray-600'">
            {{ p }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <AppModal v-model="modal" :title="editUser ? 'Chỉnh sửa người dùng' : 'Thêm người dùng mới'">
      <form @submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Họ tên</label>
            <input v-model="form.name" class="input" required />
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" class="input" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ editUser ? 'Mật khẩu mới (bỏ trống = giữ nguyên)' : 'Mật khẩu' }}</label>
            <input v-model="form.password" type="password" class="input" :required="!editUser" minlength="8" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Vai trò</label>
            <select v-model="form.role" class="input" required>
              <option value="student">Học sinh</option>
              <option value="teacher">Giáo viên</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
            <input v-model="form.phone" class="input" />
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
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const users = ref([])
const loading = ref(true)
const modal = ref(false)
const editUser = ref(null)
const saving = ref(false)
const formError = ref('')
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const filters = reactive({ search: '', role: '', status: '', page: 1 })

const form = reactive({ name: '', email: '', password: '', role: 'student', phone: '', status: 'active' })

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { filters.page = 1; fetch() }, 400)
}

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/users', { params: filters })
    users.value = data.data?.data ?? []
    pagination.value = { current_page: data.data?.current_page, last_page: data.data?.last_page, total: data.data?.total }
  } finally { loading.value = false }
}

function goPage(p) { filters.page = p; fetch() }

function openCreate() {
  editUser.value = null
  Object.assign(form, { name: '', email: '', password: '', role: 'student', phone: '', status: 'active' })
  formError.value = ''
  modal.value = true
}

function openEdit(u) {
  editUser.value = u
  Object.assign(form, { name: u.name, email: u.email, password: '', role: u.roles?.[0] ?? 'student', phone: u.phone ?? '', status: u.status })
  formError.value = ''
  modal.value = true
}

async function save() {
  saving.value = true
  formError.value = ''
  try {
    const payload = { ...form }
    if (editUser.value && !payload.password) delete payload.password
    if (editUser.value) {
      await api.put(`/admin/users/${editUser.value.id}`, payload)
    } else {
      await api.post('/admin/users', payload)
    }
    modal.value = false
    fetch()
  } catch (e) {
    formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra'
  } finally { saving.value = false }
}

async function toggleStatus(u) {
  await api.post(`/admin/users/${u.id}/toggle-status`)
  fetch()
}

async function deleteUser(u) {
  if (!confirm(`Xóa người dùng "${u.name}"?`)) return
  await api.delete(`/admin/users/${u.id}`)
  fetch()
}

function roleLabel(r) { return { admin: 'Admin', teacher: 'Giáo viên', student: 'Học sinh' }[r] ?? r }
function roleClass(r) { return { admin: 'bg-violet-100 text-violet-700', teacher: 'bg-emerald-100 text-emerald-700', student: 'bg-blue-100 text-blue-700' }[r] ?? 'bg-gray-100 text-gray-600' }

onMounted(fetch)
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
