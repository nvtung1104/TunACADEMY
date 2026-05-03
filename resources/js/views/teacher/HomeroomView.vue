<template>
  <div class="space-y-6">
    <!-- Loading -->
    <div v-if="loading" class="py-16 text-center">
      <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"/>
      <p class="text-sm text-gray-400">Đang tải...</p>
    </div>

    <!-- No homeroom -->
    <div v-else-if="!classroom" class="bg-white rounded-2xl border border-gray-100 shadow-sm py-20 text-center">
      <div class="text-5xl mb-4">🏫</div>
      <h3 class="text-lg font-semibold text-gray-700 mb-2">Bạn chưa được phân công lớp chủ nhiệm</h3>
      <p class="text-sm text-gray-400">Liên hệ quản trị viên để được phân công lớp chủ nhiệm.</p>
    </div>

    <template v-else>
      <!-- Class info -->
      <div class="bg-gradient-to-r from-[#d63015] to-[#c02a10] rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-1">
          <span class="text-red-200 text-sm font-medium uppercase tracking-wider">Lớp chủ nhiệm</span>
          <span class="text-xs px-2.5 py-1 rounded-full bg-white/20 backdrop-blur-sm">
            {{ classroom.school_year?.name }}
          </span>
        </div>
        <h1 class="text-3xl font-extrabold mb-1">{{ classroom.name }}</h1>
        <p class="text-red-200">Khối {{ classroom.grade?.level }} · {{ classroom.grade?.name }}</p>

        <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-white/20">
          <div class="text-center">
            <p class="text-2xl font-bold">{{ students.length }}</p>
            <p class="text-xs text-red-200 mt-0.5">Học sinh</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold">{{ classroom.max_students ?? '—' }}</p>
            <p class="text-xs text-red-200 mt-0.5">Sĩ số tối đa</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold">{{ subjectTeachers.length }}</p>
            <p class="text-xs text-red-200 mt-0.5">Giáo viên bộ môn</p>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Student list -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Danh sách học sinh</h3>
            <div class="relative">
              <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input v-model="studentSearch" type="text" placeholder="Tìm học sinh..."
                class="pl-8 pr-3 py-1.5 text-sm rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] w-44"/>
            </div>
          </div>

          <div v-if="!filteredStudents.length" class="py-10 text-center text-gray-400 text-sm">
            {{ studentSearch ? 'Không tìm thấy học sinh' : 'Chưa có học sinh trong lớp' }}
          </div>

          <div class="divide-y divide-gray-50">
            <div v-for="(s, i) in filteredStudents" :key="s.id"
              class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
              <span class="text-xs text-gray-400 w-6 text-right shrink-0">{{ i + 1 }}</span>
              <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-xs font-bold text-[#d63015] uppercase shrink-0">
                {{ s.name?.charAt(0) }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ s.name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ s.email }}</p>
              </div>
              <div class="text-right shrink-0 hidden sm:block">
                <p class="text-xs text-gray-500">{{ s.phone ?? '—' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right panel -->
        <div class="space-y-5">
          <!-- Subject teachers -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-800">Giáo viên bộ môn</h3>
            </div>
            <div v-if="!subjectTeachers.length" class="py-6 text-center text-sm text-gray-400">Chưa phân công</div>
            <div class="divide-y divide-gray-50">
              <div v-for="st in subjectTeachers" :key="st.teacher?.id + '-' + st.subject?.id"
                class="flex items-center gap-3 px-5 py-3">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                  :style="{ backgroundColor: (st.subject?.color || '#6366f1') + '20', color: st.subject?.color || '#6366f1' }">
                  {{ st.subject?.name?.[0] }}
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-800 truncate">{{ st.teacher?.name }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ st.subject?.name }}</p>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@api/axios'
import { useAuthStore } from '@stores/auth'

const auth = useAuthStore()
const loading = ref(true)
const classroom = ref(null)
const students = ref([])
const subjectTeachers = ref([])
const studentSearch = ref('')

const filteredStudents = computed(() => {
  if (!studentSearch.value) return students.value
  const q = studentSearch.value.toLowerCase()
  return students.value.filter(s => s.name?.toLowerCase().includes(q) || s.email?.toLowerCase().includes(q))
})

onMounted(async () => {
  try {
    const { data } = await api.get('/teacher/classrooms')
    const all = data.data?.data ?? data.data ?? []
    // Find the classroom where this teacher is homeroom teacher
    const homeroom = all.find(c => c.homeroom_teacher_id === auth.user?.id)
    if (!homeroom) return

    const detail = await api.get(`/teacher/classrooms/${homeroom.id}`)
    const cls = detail.data.data ?? detail.data
    classroom.value = cls
    students.value = cls.students ?? []
    subjectTeachers.value = cls.subject_teachers ?? []
  } catch { /* ignore */ }
  finally { loading.value = false }
})
</script>
