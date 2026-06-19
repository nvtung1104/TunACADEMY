<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <p class="text-sm text-gray-500">Quản lý các năm học trong hệ thống</p>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Thêm năm học
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tên năm học</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thời gian</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Số lớp</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="loading"><td colspan="5" class="py-12 text-center text-gray-400">Đang tải...</td></tr>
          <tr v-else-if="items.length === 0"><td colspan="5" class="py-12 text-center text-gray-400">Chưa có năm học nào</td></tr>
          <tr v-for="sy in items" :key="sy.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-medium text-gray-900">{{ sy.name }}</td>
            <td class="px-5 py-3 text-gray-600">{{ formatDate(sy.start_date) }} — {{ formatDate(sy.end_date) }}</td>
            <td class="px-5 py-3 text-gray-600">{{ sy.classrooms_count ?? 0 }} lớp</td>
            <td class="px-5 py-3">
              <span class="px-2.5 py-1 rounded-full text-xs font-medium" :class="{
                'bg-green-100 text-green-700': sy.status === 'active',
                'bg-blue-100 text-blue-600':   sy.status === 'upcoming',
                'bg-gray-100 text-gray-500':   sy.status === 'ended',
              }">
                {{ sy.status === 'active' ? 'Đang hoạt động' : sy.status === 'upcoming' ? 'Sắp diễn ra' : 'Kết thúc' }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <div class="flex justify-end gap-1">
                <button @click="openEdit(sy)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-[#d63015]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                <button @click="deleteItem(sy)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa năm học' : 'Thêm năm học mới'">
      <form class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tên năm học</label>
          <input v-model="form.name" class="input" placeholder="VD: 2024-2025" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
            <input v-model="form.start_date" type="date" class="input" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
            <input v-model="form.end_date" type="date" class="input" required />
          </div>
        </div>
        <p class="text-xs text-gray-400 bg-gray-50 rounded-xl px-3 py-2">
          Trạng thái được tự động tính theo ngày: <span class="font-medium text-gray-600">Sắp diễn ra → Đang hoạt động → Kết thúc</span>
        </p>
        <div v-if="formError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ formError }}</div>
      </form>
      <template #footer>
        <button @click="modal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm">Hủy</button>
        <button @click="save" :disabled="saving" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm disabled:opacity-60 font-medium hover:bg-[#c02a10]">{{ saving ? 'Đang lưu...' : 'Lưu' }}</button>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'
import { useToastStore }   from '@stores/toast'
import { useConfirmStore } from '@stores/confirm'

const toast        = useToastStore()
const confirmStore = useConfirmStore()
const items = ref([])
const loading = ref(true)
const modal = ref(false)
const editItem = ref(null)
const saving = ref(false)
const formError = ref('')
const form = reactive({ name: '', start_date: '', end_date: '' })

async function fetch() {
  loading.value = true
  const { data } = await api.get('/admin/school-years')
  items.value = data.data ?? []
  loading.value = false
}

function openCreate() { editItem.value = null; Object.assign(form, { name: '', start_date: '', end_date: '' }); formError.value = ''; modal.value = true }
function openEdit(sy) { editItem.value = sy; Object.assign(form, { name: sy.name, start_date: sy.start_date, end_date: sy.end_date }); formError.value = ''; modal.value = true }

async function save() {
  saving.value = true; formError.value = ''
  try {
    if (editItem.value) await api.put(`/admin/school-years/${editItem.value.id}`, form)
    else await api.post('/admin/school-years', form)
    modal.value = false; fetch()
  } catch (e) { formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

async function deleteItem(sy) {
  if (!await confirmStore.ask(`Xóa năm học "${sy.name}"?`)) return
  try { await api.delete(`/admin/school-years/${sy.id}`); fetch() }
  catch (e) { toast.error(e.response?.data?.message ?? 'Không thể xóa') }
}

function formatDate(d) { return d ? new Date(d).toLocaleDateString('vi-VN') : '' }

onMounted(fetch)
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
