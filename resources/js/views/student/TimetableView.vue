<template>
  <div class="space-y-6">
    <!-- Welcome banner / Title -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 text-white shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h2 class="text-xl font-bold mb-1">📅 Thời khóa biểu học tập</h2>
        <p class="text-white/80 text-sm">Theo dõi lịch học trên lớp và quản lý thời gian tự học hiệu quả</p>
      </div>
      <div class="flex gap-2">
        <button @click="changeTab('classroom')" 
          :class="activeTab === 'classroom' ? 'bg-white text-purple-700 font-extrabold shadow-sm' : 'bg-white/15 text-white hover:bg-white/25 font-semibold'"
          class="px-4 py-2 text-xs rounded-xl transition-all duration-200">
          🏫 Lịch học lớp
        </button>
        <button @click="changeTab('personal')" 
          :class="activeTab === 'personal' ? 'bg-white text-purple-700 font-extrabold shadow-sm' : 'bg-white/15 text-white hover:bg-white/25 font-semibold'"
          class="px-4 py-2 text-xs rounded-xl transition-all duration-200">
          ✨ Lịch tự học
        </button>
      </div>
    </div>

    <!-- Actions block -->
    <div class="flex justify-between items-center bg-white p-4 rounded-xl border border-gray-100 shadow-sm" v-if="activeTab === 'personal'">
      <span class="text-sm text-gray-500 font-medium">Bạn có thể tự tạo thời khóa biểu ôn tập tại nhà. Khi đến giờ, hệ thống sẽ gửi thông báo tài khoản và gửi email nhắc nhở.</span>
      <button @click="openCreateModal" class="px-4 py-2 rounded-xl bg-purple-600 text-white text-xs font-bold hover:bg-purple-700 transition-all shrink-0">
        + Thêm lịch tự học
      </button>
    </div>

    <!-- Timetable Grid -->
    <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-start">
      <div v-for="day in daysOfWeek" :key="day.val" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col min-h-[250px]">
        <!-- Day header -->
        <div :class="isToday(day.val) ? 'bg-purple-500 text-white font-extrabold' : 'bg-slate-50 text-gray-700 border-b border-gray-100'" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider shrink-0">
          {{ day.label }}
          <span v-if="isToday(day.val)" class="block text-[9px] uppercase tracking-normal font-medium mt-0.5">(Hôm nay)</span>
        </div>

        <!-- Schedule list for this day -->
        <div class="p-3 space-y-2.5 flex-1 overflow-y-auto max-h-[400px]">
          <div v-if="getSchedulesForDay(day.val).length === 0" class="py-10 text-center text-gray-300 text-[11px] font-medium">
            Trống
          </div>
          <div v-for="item in getSchedulesForDay(day.val)" :key="item.id" 
            @click="openDetailModal(item)"
            :class="getColorClass(item.color)"
            class="p-3 rounded-xl border text-left cursor-pointer transition-all hover:scale-[1.03] hover:shadow-sm group">
            <p class="font-bold text-xs leading-snug truncate group-hover:text-purple-950">{{ item.title }}</p>
            <p class="text-[10px] opacity-75 mt-1 font-semibold">
              🕒 {{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}
            </p>
            <p v-if="item.classroom" class="text-[9px] opacity-65 mt-0.5 truncate font-medium">
              🏫 {{ item.classroom.name }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
      <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full space-y-4 text-left animate__animated animate__fadeInUp animate__faster">
        <div class="flex justify-between items-center border-b pb-2">
          <h3 class="font-bold text-gray-900 text-base">{{ isEditing ? 'Sửa lịch tự học' : 'Thêm lịch tự học' }}</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <form @submit.prevent="saveSchedule" class="space-y-4 text-xs font-semibold">
          <div>
            <label class="block text-gray-700 mb-1">Tiêu đề / Môn học</label>
            <input v-model="form.title" type="text" placeholder="Ví dụ: Ôn tập Toán Đại Số" class="w-full px-3 py-2 rounded-lg border focus:ring-2 focus:ring-purple-200" required />
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
            <label class="block text-gray-700 mb-1">Ghi chú học tập</label>
            <textarea v-model="form.note" rows="3" placeholder="Đọc chương 2, làm bài tập về nhà..." class="w-full px-3 py-2 rounded-lg border resize-none"></textarea>
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

    <!-- Detail Readonly Modal -->
    <div v-if="showDetailModalFlag" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
      <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full space-y-4 text-left animate__animated animate__fadeInUp animate__faster">
        <div class="flex justify-between items-center border-b pb-2">
          <h3 class="font-bold text-gray-900 text-base">Chi tiết lịch học</h3>
          <button @click="showDetailModalFlag = false" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <span class="text-gray-400 block mb-0.5">Tiêu đề:</span>
            <p class="font-bold text-gray-800 text-sm bg-gray-50 p-2.5 rounded-lg border">{{ selectedItem.title }}</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <span class="text-gray-400 block mb-0.5">Thứ:</span>
              <p class="font-bold text-gray-800 bg-gray-50 p-2 rounded-lg border">{{ getDayLabel(selectedItem.day_of_week) }}</p>
            </div>
            <div>
              <span class="text-gray-400 block mb-0.5">Thời gian:</span>
              <p class="font-bold text-gray-800 bg-gray-50 p-2 rounded-lg border">
                {{ formatTime(selectedItem.start_time) }} - {{ formatTime(selectedItem.end_time) }}
              </p>
            </div>
          </div>
          <div v-if="selectedItem.classroom">
            <span class="text-gray-400 block mb-0.5">Lớp học:</span>
            <p class="font-bold text-gray-800 bg-gray-50 p-2.5 rounded-lg border">{{ selectedItem.classroom.name }}</p>
          </div>
          <div v-if="selectedItem.teacher">
            <span class="text-gray-400 block mb-0.5">Giáo viên phụ trách:</span>
            <p class="font-bold text-gray-800 bg-gray-50 p-2.5 rounded-lg border">{{ selectedItem.teacher.name }}</p>
          </div>
          <div v-if="selectedItem.note">
            <span class="text-gray-400 block mb-0.5">Ghi chú:</span>
            <p class="font-medium text-gray-600 bg-gray-50 p-3 rounded-lg border whitespace-pre-line leading-relaxed">{{ selectedItem.note }}</p>
          </div>
        </div>

        <div class="flex gap-2 pt-2">
          <button v-if="activeTab === 'personal'" @click="switchToEdit" class="flex-1 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold transition-all shadow-md text-center text-xs">
            Chỉnh sửa
          </button>
          <button @click="showDetailModalFlag = false" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-all text-xs text-center">
            Đóng
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import api from '@api/axios'
import { useToastStore } from '@stores/toast'

const toast = useToastStore()
const activeTab = ref('classroom')
const schedules = ref([])
const showModal = ref(false)
const showDetailModalFlag = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const selectedItem = ref({})

const form = reactive({
  id: null,
  title: '',
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

function changeTab(tab) {
  activeTab.value = tab
  fetchSchedules()
}

async function fetchSchedules() {
  try {
    if (activeTab.value === 'classroom') {
      const { data } = await api.get('/student/classroom-schedules')
      schedules.value = data.data ?? []
    } else {
      const { data } = await api.get('/student/personal-schedules')
      schedules.value = data.data ?? []
    }
  } catch (e) {
    toast.error('Không thể tải thời khóa biểu')
  }
}

function getSchedulesForDay(day) {
  return schedules.value.filter(s => Number(s.day_of_week) === Number(day))
}

function isToday(dayVal) {
  const isoDay = new Date().getDay() // 0 = Sun, 1 = Mon, ..., 6 = Sat
  const currentDayOfWeek = isoDay === 0 ? 8 : isoDay + 1 // Sun=8, Mon=2, ..., Sat=7
  return Number(dayVal) === Number(currentDayOfWeek)
}

function formatTime(t) {
  return t ? t.substring(0, 5) : ''
}

function getDayLabel(val) {
  return daysOfWeek.find(d => d.val === Number(val))?.label ?? ''
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
  form.day_of_week = 2
  form.start_time = ''
  form.end_time = ''
  form.color = 'indigo'
  form.note = ''
  showModal.value = true
}

function openDetailModal(item) {
  selectedItem.value = item
  showDetailModalFlag.value = true
}

function switchToEdit() {
  showDetailModalFlag.value = false
  isEditing.value = true
  form.id = selectedItem.value.id
  form.title = selectedItem.value.title
  form.day_of_week = selectedItem.value.day_of_week
  form.start_time = selectedItem.value.start_time.substring(0, 5)
  form.end_time = selectedItem.value.end_time.substring(0, 5)
  form.color = selectedItem.value.color || 'indigo'
  form.note = selectedItem.value.note || ''
  showModal.value = true
}

async function saveSchedule() {
  submitting.value = true
  try {
    if (isEditing.value) {
      await api.put(`/student/personal-schedules/${form.id}`, form)
      toast.success('Cập nhật lịch tự học thành công')
    } else {
      await api.post('/student/personal-schedules', form)
      toast.success('Thêm lịch tự học thành công')
    }
    showModal.value = false
    fetchSchedules()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Có lỗi xảy ra khi lưu lịch')
  } finally {
    submitting.value = false
  }
}

async function confirmDelete() {
  if (!confirm('Bạn có chắc chắn muốn xóa lịch này không?')) return
  submitting.value = true
  try {
    await api.delete(`/student/personal-schedules/${form.id}`)
    toast.success('Xóa lịch tự học thành công')
    showModal.value = false
    fetchSchedules()
  } catch (e) {
    toast.error('Không thể xóa lịch')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchSchedules()
})
</script>
