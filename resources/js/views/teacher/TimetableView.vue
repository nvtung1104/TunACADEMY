<template>
  <div class="space-y-6 text-left">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 text-white shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h2 class="text-xl font-bold mb-1">📅 Quản lý Thời khóa biểu</h2>
        <p class="text-white/80 text-sm">Thiết lập thời gian biểu học tập cho các lớp học</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <span class="text-xs font-bold text-purple-100">Chọn lớp:</span>
        <select v-model="selectedClassroomId" @change="fetchClassroomSchedules" class="px-3 py-2 text-xs rounded-xl bg-white/10 text-white font-bold border border-white/20 focus:outline-none focus:bg-purple-800 focus:border-white">
          <option value="" disabled class="text-gray-800">-- Chọn lớp học --</option>
          <option v-for="c in classrooms" :key="c.id" :value="c.id" class="text-gray-800">{{ c.name }}</option>
        </select>
      </div>
    </div>

    <!-- No Classroom Selected State -->
    <div v-if="!selectedClassroomId" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center text-gray-400 space-y-3">
      <div class="text-5xl">🏫</div>
      <p class="text-sm font-semibold">Vui lòng chọn một lớp học ở góc trên bên phải để quản lý thời khóa biểu.</p>
    </div>

    <!-- Active Timetable Workspace -->
    <div v-else class="space-y-6 animate__animated animate__fadeIn animate__faster">
      <!-- Info & Actions block -->
      <div class="flex justify-between items-center bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
        <span class="text-sm text-gray-500 font-medium">Lịch học thiết lập dưới đây sẽ hiển thị trong trang tổng quan của tất cả học sinh thuộc lớp này.</span>
        <button @click="openCreateModal" class="px-4 py-2 rounded-xl bg-purple-600 text-white text-xs font-bold hover:bg-purple-700 transition-all shrink-0">
          + Thêm lịch học lớp
        </button>
      </div>

      <!-- Grid view of 7 days -->
      <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-start">
        <div v-for="day in daysOfWeek" :key="day.val" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col min-h-[250px]">
          <!-- Day Header -->
          <div :class="isToday(day.val) ? 'bg-purple-500 text-white font-extrabold' : 'bg-slate-50 text-gray-700 border-b border-gray-100'" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider shrink-0">
            {{ day.label }}
            <span v-if="isToday(day.val)" class="block text-[9px] uppercase tracking-normal font-medium mt-0.5">(Hôm nay)</span>
          </div>

          <!-- Schedules -->
          <div class="p-3 space-y-2.5 flex-1 overflow-y-auto max-h-[400px]">
            <div v-if="getSchedulesForDay(day.val).length === 0" class="py-10 text-center text-gray-300 text-[11px] font-medium">
              Trống
            </div>
            <div v-for="item in getSchedulesForDay(day.val)" :key="item.id" 
              @click="openEditModal(item)"
              :class="getColorClass(item.color)"
              class="p-3 rounded-xl border text-left cursor-pointer transition-all hover:scale-[1.03] hover:shadow-sm group">
              <p class="font-bold text-xs leading-snug truncate group-hover:text-purple-950">{{ item.title }}</p>
              <p class="text-[10px] opacity-75 mt-1 font-semibold">
                🕒 {{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}
              </p>
              <p v-if="item.note" class="text-[9px] opacity-60 mt-1 truncate font-medium">{{ item.note }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
      <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full space-y-4 text-left animate__animated animate__fadeInUp animate__faster">
        <div class="flex justify-between items-center border-b pb-2">
          <h3 class="font-bold text-gray-900 text-base">{{ isEditing ? 'Sửa lịch học lớp' : 'Thêm lịch học lớp' }}</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <form @submit.prevent="saveSchedule" class="space-y-4 text-xs font-semibold">
          <div>
            <label class="block text-gray-700 mb-1">Môn học / Tiêu đề học phần</label>
            <input v-model="form.title" type="text" placeholder="Ví dụ: Tiết Toán Đại Số" class="w-full px-3 py-2 rounded-lg border focus:ring-2 focus:ring-purple-200" required />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-gray-700 mb-1">Thứ</label>
              <select v-model="form.day_of_week" class="w-full px-3 py-2 rounded-lg border focus:ring-2" required>
                <option v-for="d in daysOfWeek" :key="d.val" :value="d.val">{{ d.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-gray-700 mb-1">Màu sắc</label>
              <select v-model="form.color" class="w-full px-3 py-2 rounded-lg border focus:ring-2">
                <option value="indigo">Chàm</option>
                <option value="blue">Xanh dương</option>
                <option value="emerald">Lục bảo</option>
                <option value="amber">Vàng hổ phách</option>
                <option value="rose">Hồng cánh sen</option>
                <option value="purple">Tím</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-gray-700 mb-1">Giờ bắt đầu</label>
              <input v-model="form.start_time" type="time" class="w-full px-3 py-2 rounded-lg border" required />
            </div>
            <div>
              <label class="block text-gray-700 mb-1">Giờ kết thúc</label>
              <input v-model="form.end_time" type="time" class="w-full px-3 py-2 rounded-lg border" required />
            </div>
          </div>

          <div>
            <label class="block text-gray-700 mb-1">Ghi chú / Yêu cầu chuẩn bị</label>
            <textarea v-model="form.note" rows="3" placeholder="Mang theo máy tính cầm tay, sách bài tập..." class="w-full px-3 py-2 rounded-lg border resize-none"></textarea>
          </div>

          <div class="flex gap-2.5 pt-2">
            <button v-if="isEditing" type="button" @click="confirmDelete" class="flex-1 py-2.5 rounded-xl border border-red-200 text-red-600 font-bold hover:bg-red-50 transition-all text-center">
              Xóa lịch
            </button>
            <button type="button" @click="showModal = false" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-all">
              Hủy
            </button>
            <button type="submit" :disabled="submitting" class="flex-1 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold transition-all shadow-md">
              {{ submitting ? 'Đang lưu...' : 'Lưu lại' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@api/axios'
import { useToastStore } from '@stores/toast'

const toast = useToastStore()
const classrooms = ref([])
const selectedClassroomId = ref('')
const schedules = ref([])
const showModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)

const form = reactive({
  id: null,
  title: '',
  classroom_id: '',
  day_of_week: 2,
  start_time: '',
  end_time: '',
  color: 'indigo',
  note: '',
})

const daysOfWeek = [
  { val: 2, label: 'Thứ 2' },
  { val: 3, label: 'Thứ 3' },
  { val: 4, label: 'Thứ 4' },
  { val: 5, label: 'Thứ 5' },
  { val: 6, label: 'Thứ 6' },
  { val: 7, label: 'Thứ 7' },
  { val: 8, label: 'Chủ Nhật' },
]

async function fetchClassrooms() {
  try {
    const { data } = await api.get('/teacher/all-classrooms')
    classrooms.value = data.data ?? []
    if (classrooms.value.length > 0) {
      selectedClassroomId.value = classrooms.value[0].id
      fetchClassroomSchedules()
    }
  } catch (e) {
    toast.error('Không thể tải danh sách lớp học')
  }
}

async function fetchClassroomSchedules() {
  if (!selectedClassroomId.value) return
  try {
    const { data } = await api.get('/teacher/schedules', {
      params: { classroom_id: selectedClassroomId.value }
    })
    schedules.value = data.data ?? []
  } catch (e) {
    toast.error('Không thể tải thời khóa biểu của lớp')
  }
}

function getSchedulesForDay(day) {
  return schedules.value.filter(s => Number(s.day_of_week) === Number(day))
}

function isToday(dayVal) {
  const isoDay = new Date().getDay()
  const currentDayOfWeek = isoDay === 0 ? 8 : isoDay + 1
  return Number(dayVal) === Number(currentDayOfWeek)
}

function formatTime(t) {
  return t ? t.substring(0, 5) : ''
}

function getColorClass(color) {
  switch (color) {
    case 'blue':
      return 'bg-blue-50 border-blue-200 text-blue-800'
    case 'emerald':
      return 'bg-emerald-50 border-emerald-200 text-emerald-800'
    case 'amber':
      return 'bg-amber-50 border-amber-200 text-amber-800'
    case 'rose':
      return 'bg-rose-50 border-rose-200 text-rose-800'
    case 'purple':
      return 'bg-purple-50 border-purple-200 text-purple-800'
    default:
      return 'bg-indigo-50 border-indigo-200 text-indigo-800'
  }
}

function openCreateModal() {
  isEditing.value = false
  form.id = null
  form.title = ''
  form.classroom_id = selectedClassroomId.value
  form.day_of_week = 2
  form.start_time = ''
  form.end_time = ''
  form.color = 'indigo'
  form.note = ''
  showModal.value = true
}

function openEditModal(item) {
  isEditing.value = true
  form.id = item.id
  form.title = item.title
  form.classroom_id = selectedClassroomId.value
  form.day_of_week = item.day_of_week
  form.start_time = item.start_time.substring(0, 5)
  form.end_time = item.end_time.substring(0, 5)
  form.color = item.color || 'indigo'
  form.note = item.note || ''
  showModal.value = true
}

async function saveSchedule() {
  submitting.value = true
  try {
    if (isEditing.value) {
      await api.put(`/teacher/schedules/${form.id}`, form)
      toast.success('Cập nhật thời khóa biểu thành công')
    } else {
      await api.post('/teacher/schedules', form)
      toast.success('Thêm tiết học thành công')
    }
    showModal.value = false
    fetchClassroomSchedules()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Có lỗi xảy ra khi lưu lịch')
  } finally {
    submitting.value = false
  }
}

async function confirmDelete() {
  if (!confirm('Bạn có chắc chắn muốn xóa tiết học này không?')) return
  submitting.value = true
  try {
    await api.delete(`/teacher/schedules/${form.id}`)
    toast.success('Xóa tiết học thành công')
    showModal.value = false
    fetchClassroomSchedules()
  } catch (e) {
    toast.error('Không thể xóa tiết học')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchClassrooms()
})
</script>
