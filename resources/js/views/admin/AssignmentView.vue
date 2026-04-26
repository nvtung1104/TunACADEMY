<template>
  <div class="space-y-5">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-gray-900">Quản lý bài tập</h2>
        <p class="text-sm text-gray-400 mt-0.5">Tất cả bài tập trong hệ thống</p>
      </div>
      <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ meta.total ?? 0 }} bài tập</span>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3">
      <div class="relative flex-1 min-w-48">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" @input="debounceFetch" type="text" placeholder="Tìm tên bài tập..."
          class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
      </div>
      <select v-model="filterStatus" @change="fetchData()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
        <option value="">Tất cả trạng thái</option>
        <option value="published">Đã xuất bản</option>
        <option value="draft">Bản nháp</option>
      </select>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="py-16 text-center">
        <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"/>
        <p class="text-sm text-gray-400">Đang tải...</p>
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Bài tập</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Giáo viên</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Hạn nộp</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
            <th class="px-5 py-3 w-16"/>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="!assignments.length"><td colspan="5" class="py-12 text-center text-gray-400">Không có bài tập nào</td></tr>
          <tr v-for="a in assignments" :key="a.id" class="hover:bg-gray-50">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                  :style="{ backgroundColor: (a.subject?.color || '#f59e0b') + '20', color: a.subject?.color || '#f59e0b' }">
                  {{ a.subject?.name?.[0] }}
                </div>
                <div>
                  <p class="font-medium text-gray-800 line-clamp-1">{{ a.title }}</p>
                  <p class="text-xs text-gray-400">{{ a.subject?.name }} · {{ a.classroom?.name }}</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ a.teacher?.name ?? '—' }}</td>
            <td class="px-5 py-3 text-gray-600 hidden lg:table-cell">
              <span :class="isOverdue(a.deadline) ? 'text-red-500' : 'text-gray-600'">
                {{ formatDate(a.deadline) }}
              </span>
            </td>
            <td class="px-5 py-3">
              <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                :class="a.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                {{ a.status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <button @click="confirmDelete(a)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :meta="meta" @page="fetchData"/>

    <AppModal v-model="deleteModal" title="Xác nhận xóa" size="sm">
      <p class="text-sm text-gray-600">Bạn có chắc muốn xóa bài tập <strong>{{ deleteTarget?.title }}</strong>?</p>
      <template #footer>
        <button @click="deleteModal = false" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">Hủy</button>
        <button @click="doDelete" :disabled="deleting" class="px-4 py-2 text-sm rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold disabled:opacity-60">
          {{ deleting ? 'Đang xóa...' : 'Xóa' }}
        </button>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'
import Pagination from '@components/common/Pagination.vue'

const assignments = ref([])
const loading = ref(true)
const search = ref('')
const filterStatus = ref('')
const meta = ref({ total: 0, current_page: 1, last_page: 1 })
const deleteModal = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)

let debounceTimer = null
function debounceFetch() { clearTimeout(debounceTimer); debounceTimer = setTimeout(() => fetchData(), 400) }
function formatDate(d) { return d ? new Date(d).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—' }
function isOverdue(d) { return d && new Date(d) < new Date() }

async function fetchData(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/admin/content/assignments', { params: { search: search.value, status: filterStatus.value, page } })
    assignments.value = data.data
    meta.value = data.meta ?? { total: data.total, current_page: data.current_page, last_page: data.last_page }
  } finally { loading.value = false }
}

function confirmDelete(item) { deleteTarget.value = item; deleteModal.value = true }

async function doDelete() {
  deleting.value = true
  try {
    await api.delete(`/admin/content/assignments/${deleteTarget.value.id}`)
    deleteModal.value = false
    fetchData(meta.value.current_page)
  } finally { deleting.value = false }
}

onMounted(fetchData)
</script>
