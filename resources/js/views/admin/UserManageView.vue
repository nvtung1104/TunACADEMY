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
                <button @click="openDetail(u)" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors" title="Xem chi tiết">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
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
    <AppModal v-model="modal" :title="editUser ? 'Chỉnh sửa người dùng' : 'Thêm người dùng mới'" size="lg">
      <!-- Tabs -->
      <div class="flex border-b border-gray-100 -mx-6 px-6 mb-5 gap-0 overflow-x-auto">
        <button v-for="tab in visibleTabs" :key="tab.key" @click="activeTab = tab.key"
          class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors"
          :class="activeTab === tab.key ? 'border-[#d63015] text-[#d63015]' : 'border-transparent text-gray-500 hover:text-gray-700'">
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab: Tài khoản -->
      <div v-show="activeTab === 'account'" class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="label">Họ tên</label>
          <input v-model="form.name" class="input" required />
        </div>
        <div class="col-span-2">
          <label class="label">Email</label>
          <input v-model="form.email" type="email" class="input" required />
        </div>
        <div>
          <label class="label">{{ editUser ? 'Mật khẩu mới (bỏ trống = giữ nguyên)' : 'Mật khẩu' }}</label>
          <input v-model="form.password" type="password" class="input" :required="!editUser" minlength="8" />
        </div>
        <div>
          <label class="label">Vai trò</label>
          <select v-model="form.role" class="input" required @change="onRoleChange">
            <option value="student">Học sinh</option>
            <option value="teacher">Giáo viên</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div>
          <label class="label">Số điện thoại</label>
          <input v-model="form.phone" class="input" />
        </div>
        <div>
          <label class="label">Trạng thái</label>
          <select v-model="form.status" class="input">
            <option value="active">Hoạt động</option>
            <option value="inactive">Vô hiệu</option>
          </select>
        </div>
      </div>

      <!-- Tab: Cá nhân -->
      <div v-show="activeTab === 'personal'" class="grid grid-cols-2 gap-4">
        <div>
          <label class="label">Ngày sinh</label>
          <input v-model="form.date_of_birth" type="date" class="input" />
        </div>
        <div>
          <label class="label">Giới tính</label>
          <select v-model="form.gender" class="input">
            <option value="">-- Chọn --</option>
            <option value="male">Nam</option>
            <option value="female">Nữ</option>
            <option value="other">Khác</option>
          </select>
        </div>
        <div class="col-span-2">
          <label class="label">Địa chỉ</label>
          <input v-model="form.address" class="input" />
        </div>
        <div class="col-span-2">
          <label class="label">Chuyên môn / Bằng cấp</label>
          <input v-model="form.qualification" class="input" placeholder="VD: Thạc sĩ Toán học" />
        </div>
      </div>

      <!-- Tab: Phụ huynh (student only) -->
      <div v-show="activeTab === 'parent'" class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="label">Họ tên phụ huynh</label>
          <input v-model="form.parent_name" class="input" />
        </div>
        <div>
          <label class="label">SĐT phụ huynh</label>
          <input v-model="form.parent_phone" class="input" />
        </div>
        <div class="col-span-2">
          <label class="label">Địa chỉ phụ huynh</label>
          <input v-model="form.parent_address" class="input" />
        </div>
      </div>

      <!-- Tab: Lớp học (student only) -->
      <div v-show="activeTab === 'class'" class="space-y-4">
        <p class="text-xs text-gray-400">Chọn lớp để thêm vào. Để trống nếu không thay đổi. Chọn "Xóa khỏi lớp" để gỡ học sinh ra.</p>
        <div>
          <label class="label">Lớp học</label>
          <select v-model="form.classroom_id" class="input">
            <option value="">-- Không thay đổi --</option>
            <option value="null">Xóa khỏi lớp hiện tại</option>
            <option v-for="c in classrooms" :key="c.id" :value="c.id">
              {{ c.name }}{{ c.grade?.name ? ` (${c.grade.name})` : '' }}{{ c.school_year?.name ? ` - ${c.school_year.name}` : '' }}
            </option>
          </select>
        </div>
      </div>

      <!-- Tab: Môn dạy (teacher only) -->
      <div v-show="activeTab === 'subjects'" class="space-y-3">
        <p class="text-xs text-gray-400">Chọn những môn giáo viên này có thể giảng dạy. Admin xác nhận = đánh dấu đã xác minh.</p>
        <div v-if="allSubjects.length === 0" class="py-6 text-center text-gray-400 text-sm">Đang tải...</div>
        <div v-else class="grid grid-cols-2 gap-2">
          <label v-for="s in allSubjects" :key="s.id"
            class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
            :class="selectedSubjectIds.includes(s.id)
              ? 'border-[#d63015] bg-red-50'
              : 'border-gray-100 hover:bg-gray-50'">
            <input type="checkbox" :value="s.id" v-model="selectedSubjectIds" class="hidden" />
            <span class="w-3 h-3 rounded-full shrink-0" :style="{ backgroundColor: s.color ?? '#6b7280' }" />
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-800 truncate">{{ s.name }}</p>
              <p class="text-xs text-gray-400">{{ s.code }}</p>
            </div>
            <svg v-if="selectedSubjectIds.includes(s.id)" class="w-4 h-4 text-[#d63015] shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
          </label>
        </div>
      </div>

      <div v-if="formError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl mt-4">{{ formError }}</div>

      <template #footer>
        <button @click="modal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
        <button @click="save" :disabled="saving" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] disabled:opacity-60">
          {{ saving ? 'Đang lưu...' : 'Lưu' }}
        </button>
      </template>
    </AppModal>

    <!-- Detail Drawer -->
    <UserDetailDrawer v-model="drawer" :user-id="drawerUserId" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'
import UserDetailDrawer from './UserDetailDrawer.vue'

const users = ref([])
const loading = ref(true)
const modal = ref(false)
const editUser = ref(null)
const saving = ref(false)
const formError = ref('')
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const filters = reactive({ search: '', role: '', status: '', page: 1 })

const drawer = ref(false)
const drawerUserId = ref(null)

function openDetail(u) {
  drawerUserId.value = u.id
  drawer.value = true
}

// ── Tabs ────────────────────────────────────────────────────────────────────
const activeTab = ref('account')
const ALL_TABS = [
  { key: 'account',  label: 'Tài khoản' },
  { key: 'personal', label: 'Cá nhân' },
  { key: 'subjects', label: 'Môn dạy' },
  { key: 'parent',   label: 'Phụ huynh' },
  { key: 'class',    label: 'Lớp học' },
]
const visibleTabs = computed(() => {
  if (form.role === 'teacher') return ALL_TABS.filter(t => ['account', 'personal', 'subjects'].includes(t.key))
  if (form.role === 'student') return ALL_TABS.filter(t => ['account', 'personal', 'parent', 'class'].includes(t.key))
  return ALL_TABS.filter(t => ['account', 'personal'].includes(t.key))
})

// ── Classrooms list ─────────────────────────────────────────────────────────
const classrooms = ref([])
async function fetchClassrooms() {
  if (classrooms.value.length > 0) return
  try {
    const { data } = await api.get('/admin/classrooms', { params: { per_page: 200 } })
    classrooms.value = data.data ?? []
  } catch {}
}

// ── Subjects list ────────────────────────────────────────────────────────────
const allSubjects = ref([])
const selectedSubjectIds = ref([])

async function fetchSubjects() {
  if (allSubjects.value.length > 0) return
  try {
    const { data } = await api.get('/admin/subjects', { params: { per_page: 200 } })
    allSubjects.value = data.data ?? []
  } catch {}
}

function onRoleChange() {
  if (!visibleTabs.value.find(t => t.key === activeTab.value)) activeTab.value = 'account'
  if (form.role === 'student') fetchClassrooms()
  if (form.role === 'teacher') fetchSubjects()
}

// ── Form ────────────────────────────────────────────────────────────────────
const emptyForm = {
  name: '', email: '', password: '', role: 'student', phone: '', status: 'active',
  date_of_birth: '', gender: '', address: '', qualification: '',
  parent_name: '', parent_phone: '', parent_address: '',
  classroom_id: '',
}
const form = reactive({ ...emptyForm })

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { filters.page = 1; fetch() }, 400)
}

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/users', { params: filters })
    users.value = data.data ?? []
    pagination.value = { current_page: data.meta?.current_page, last_page: data.meta?.last_page, total: data.meta?.total }
  } finally { loading.value = false }
}

function goPage(p) { filters.page = p; fetch() }

function openCreate() {
  editUser.value = null
  activeTab.value = 'account'
  Object.assign(form, { ...emptyForm })
  selectedSubjectIds.value = []
  formError.value = ''
  modal.value = true
  fetchClassrooms()
  fetchSubjects()
}

async function openEdit(u) {
  editUser.value = u
  activeTab.value = 'account'
  const role = u.roles?.[0] ?? 'student'
  Object.assign(form, {
    name: u.name, email: u.email, password: '', role,
    phone: u.phone ?? '', status: u.status,
    date_of_birth: u.date_of_birth ?? '', gender: u.gender ?? '',
    address: u.address ?? '', qualification: u.qualification ?? '',
    parent_name: u.parent_name ?? '', parent_phone: u.parent_phone ?? '',
    parent_address: u.parent_address ?? '',
    classroom_id: '',
  })
  selectedSubjectIds.value = []
  formError.value = ''
  modal.value = true

  if (role === 'student') fetchClassrooms()
  if (role === 'teacher') {
    fetchSubjects()
    // Pre-fill current subjects
    try {
      const { data } = await api.get(`/admin/users/${u.id}`)
      const payload = data.data
      selectedSubjectIds.value = (payload.subjects ?? []).map(s => s.id).filter(Boolean)
    } catch {}
  }
}

async function save() {
  saving.value = true
  formError.value = ''
  try {
    const payload = { ...form }
    if (editUser.value && !payload.password) delete payload.password
    // '' = không thay đổi lớp → bỏ field; 'null' = xóa khỏi lớp → gửi null
    if (payload.classroom_id === '') delete payload.classroom_id
    else if (payload.classroom_id === 'null') payload.classroom_id = null
    // Gắn môn dạy nếu là giáo viên
    if (form.role === 'teacher') payload.subject_ids = selectedSubjectIds.value

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
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
</style>
