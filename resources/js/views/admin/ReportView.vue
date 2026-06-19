<template>
  <div class="space-y-6">
    <!-- Overview stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div 
        v-for="stat in stats" 
        :key="stat.label" 
        class="relative overflow-hidden bg-white rounded-2xl p-5 border border-gray-100 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md group"
      >
        <!-- Background Glow -->
        <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full opacity-10 blur-xl group-hover:scale-125 transition-transform duration-500" :class="stat.bgGlow" />

        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">{{ stat.label }}</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-2 leading-none">
              {{ overview[stat.key] ?? '...' }}
            </p>
          </div>
          <div class="p-3 rounded-xl backdrop-blur-sm shadow-inner" :class="stat.iconBg">
            <span class="w-6 h-6 block" v-html="stat.svgIcon" />
          </div>
        </div>
      </div>
    </div>

    <!-- Export Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <div class="flex items-center gap-2 mb-4">
        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        <h3 class="font-bold text-gray-800">Xuất báo cáo thống kê</h3>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button 
          @click="exportData('students')" 
          class="flex items-center justify-between p-4 rounded-xl border border-blue-100 bg-blue-50/20 text-left hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 group active:scale-98"
        >
          <div>
            <p class="text-sm font-semibold text-blue-800">Danh sách học sinh</p>
            <p class="text-xs text-blue-500 mt-1">Định dạng file: Excel (.xlsx)</p>
          </div>
          <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center group-hover:translate-y-0.5 transition-transform duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
          </div>
        </button>

        <button 
          @click="exportData('teachers')" 
          class="flex items-center justify-between p-4 rounded-xl border border-emerald-100 bg-emerald-50/20 text-left hover:bg-emerald-50 hover:border-emerald-300 transition-all duration-200 group active:scale-98"
        >
          <div>
            <p class="text-sm font-semibold text-emerald-800">Danh sách giáo viên</p>
            <p class="text-xs text-emerald-500 mt-1">Định dạng file: Excel (.xlsx)</p>
          </div>
          <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:translate-y-0.5 transition-transform duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
          </div>
        </button>

        <button 
          @click="exportData('exam-results')" 
          class="flex items-center justify-between p-4 rounded-xl border border-amber-100 bg-amber-50/20 text-left hover:bg-amber-50 hover:border-amber-300 transition-all duration-200 group active:scale-98"
        >
          <div>
            <p class="text-sm font-semibold text-amber-800">Kết quả bài kiểm tra</p>
            <p class="text-xs text-amber-500 mt-1">Định dạng file: Excel (.xlsx)</p>
          </div>
          <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center group-hover:translate-y-0.5 transition-transform duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
          </div>
        </button>
      </div>
    </div>

    <!-- Exam attempts section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <!-- Section Header with Filters -->
      <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/30">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h3 class="font-bold text-gray-800 text-base">Lịch sử kiểm tra</h3>
            <p class="text-xs text-gray-400 mt-0.5">Danh sách học sinh nộp bài thi hệ thống</p>
          </div>
          
          <!-- Filters Row -->
          <div class="flex flex-wrap items-center gap-2.5">
            <!-- Search bar -->
            <div class="relative w-full sm:w-48">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Tìm học sinh, đề thi..." 
                class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-[#d63015] transition-all"
              />
            </div>

            <!-- Subject filter -->
            <select 
              v-model="selectedSubject" 
              class="px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-[#d63015] transition-all"
            >
              <option v-for="sub in subjects" :key="sub" :value="sub">{{ sub }}</option>
            </select>

            <!-- Grade Filter -->
            <select 
              v-model="selectedGradeRange" 
              class="px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-[#d63015] transition-all"
            >
              <option value="Tất cả điểm">Tất cả điểm</option>
              <option value="Giỏi (>= 8.0)">Giỏi (≥ 8.0)</option>
              <option value="Trung bình/Khá (5.0 - 8.0)">Khá/Trung bình (5.0 - 8.0)</option>
              <option value="Yếu (< 5.0)">Yếu (< 5.0)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table content -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50/80 border-b border-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Học sinh</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bài kiểm tra</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Môn học</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Điểm số</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thời gian nộp</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="filteredAttempts.length === 0">
              <td colspan="5" class="py-12 text-center text-gray-400">
                <div class="flex flex-col items-center justify-center gap-2">
                  <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>Không tìm thấy kết quả phù hợp</span>
                </div>
              </td>
            </tr>
            <tr 
              v-for="a in filteredAttempts" 
              :key="a.id" 
              class="hover:bg-gray-50/50 transition-colors duration-150"
            >
              <!-- Student Column with AvatarFrame -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <AvatarFrame
                    :src="a.student?.avatar"
                    :name="a.student?.name"
                    :gender="a.student?.gender"
                    :frame="a.student?.frame"
                    :size="38"
                  />
                  <div>
                    <p class="font-semibold text-gray-800 leading-tight">{{ a.student?.name }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ a.student?.email }}</p>
                  </div>
                </div>
              </td>
              
              <td class="px-6 py-4">
                <span class="font-medium text-gray-700">{{ a.exam?.title }}</span>
                <p class="text-[10px] text-gray-400 mt-0.5">Lớp: {{ a.exam?.classroom?.name ?? 'Tự do' }}</p>
              </td>
              
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-600">
                  {{ a.exam?.subject?.name ?? 'Không rõ' }}
                </span>
              </td>
              
              <td class="px-6 py-4">
                <span 
                  :class="scoreBadgeClass(a.score)" 
                  class="inline-block px-2.5 py-1 rounded-full text-xs font-bold border"
                >
                  {{ a.score !== null ? Number(a.score).toFixed(2) : '-' }}
                </span>
              </td>
              
              <td class="px-6 py-4 text-gray-400 text-xs font-medium">
                {{ formatDate(a.submitted_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@api/axios'
import AvatarFrame from '@components/common/AvatarFrame.vue'

const overview = ref({})
const attempts = ref([])

// Dynamic filter variables
const searchQuery = ref('')
const selectedSubject = ref('Tất cả môn')
const selectedGradeRange = ref('Tất cả điểm')

const stats = [
  { 
    key: 'total_students', 
    label: 'Học sinh', 
    bgGlow: 'bg-blue-500', 
    iconBg: 'bg-blue-50 text-blue-600',
    svgIcon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>` 
  },
  { 
    key: 'total_teachers', 
    label: 'Giáo viên', 
    bgGlow: 'bg-emerald-500', 
    iconBg: 'bg-emerald-50 text-emerald-600',
    svgIcon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>` 
  },
  { 
    key: 'total_classrooms', 
    label: 'Lớp học', 
    bgGlow: 'bg-violet-500', 
    iconBg: 'bg-violet-50 text-violet-600',
    svgIcon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>` 
  },
  { 
    key: 'total_exams', 
    label: 'Bài kiểm tra', 
    bgGlow: 'bg-orange-500', 
    iconBg: 'bg-orange-50 text-orange-600',
    svgIcon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>` 
  },
]

// Extract unique subjects from reports list for drop down
const subjects = computed(() => {
  const all = attempts.value.map(a => a.exam?.subject?.name).filter(Boolean)
  return ['Tất cả môn', ...new Set(all)]
})

// Client-side filtering logic
const filteredAttempts = computed(() => {
  return attempts.value.filter(a => {
    const matchesSearch = !searchQuery.value || 
      a.student?.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      a.student?.email?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      a.exam?.title?.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesSubject = selectedSubject.value === 'Tất cả môn' || 
      a.exam?.subject?.name === selectedSubject.value
    
    const score = a.score ?? 0
    let matchesGrade = true
    if (selectedGradeRange.value === 'Giỏi (>= 8.0)') {
      matchesGrade = score >= 8
    } else if (selectedGradeRange.value === 'Trung bình/Khá (5.0 - 8.0)') {
      matchesGrade = score >= 5 && score < 8
    } else if (selectedGradeRange.value === 'Yếu (< 5.0)') {
      matchesGrade = score < 5
    }
    
    return matchesSearch && matchesSubject && matchesGrade
  })
})

async function exportData(type) {
  const link = document.createElement('a')
  link.href = `/api/admin/reports/export?type=${type}`
  link.setAttribute('Authorization', `Bearer ${localStorage.getItem('auth_token')}`)
  const { data } = await api.get(`/admin/reports/export?type=${type}`, { responseType: 'blob' })
  const url = URL.createObjectURL(data)
  link.href = url
  link.download = `${type}-${new Date().toISOString().slice(0, 10)}.xlsx`
  link.click()
  URL.revokeObjectURL(url)
}

function formatDate(d) { return d ? new Date(d).toLocaleString('vi-VN') : '' }

function scoreBadgeClass(score) {
  if (score === null || score === undefined) return 'bg-gray-50 text-gray-400 border-gray-200'
  const numeric = Number(score)
  if (numeric >= 8.0) return 'bg-emerald-50 text-emerald-700 border-emerald-200/50'
  if (numeric >= 5.0) return 'bg-amber-50 text-amber-700 border-amber-200/50'
  return 'bg-red-50 text-red-700 border-red-200/50'
}

onMounted(async () => {
  const [ov, ex] = await Promise.all([
    api.get('/admin/reports/overview').catch(() => ({ data: { data: {} } })),
    api.get('/admin/reports/exams').catch(() => ({ data: { data: [] } })),
  ])
  overview.value = ov.data.data ?? {}
  attempts.value = ex.data.data?.data ?? []
})
</script>
