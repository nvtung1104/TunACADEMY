<template>
  <div class="min-h-[80vh] pb-12">
    <!-- View 1: List of Classrooms -->
    <div v-if="viewState === 'list'" class="space-y-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
            <span>🏫 Lớp học của tôi</span>
          </h2>
          <p class="text-sm text-slate-500 mt-1">Các lớp học chính khóa bạn đang tham gia học tập</p>
        </div>
        <div class="relative w-full md:w-80">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Tìm kiếm lớp học..." 
            class="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/20 focus:border-[#d63015] bg-white transition-all shadow-sm"
          />
        </div>
      </div>

      <!-- Loading skeleton -->
      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 3" :key="i" class="bg-white rounded-3xl border border-slate-100 p-6 animate-pulse space-y-4">
          <div class="h-32 bg-slate-100 rounded-2xl w-full"></div>
          <div class="h-6 bg-slate-100 rounded-lg w-3/4"></div>
          <div class="h-4 bg-slate-100 rounded-lg w-1/2"></div>
          <div class="h-10 bg-slate-100 rounded-xl w-full"></div>
        </div>
      </div>

      <!-- Classroom list -->
      <div v-else>
        <div v-if="filteredClassrooms.length === 0" class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-3xl border border-slate-100 shadow-sm px-6">
          <div class="w-16 h-16 rounded-2xl bg-rose-50 flex items-center justify-center text-3xl mb-4">🏫</div>
          <h3 class="font-bold text-slate-800 text-lg">Bạn chưa tham gia lớp học nào</h3>
          <p class="text-sm text-slate-500 mt-1 max-w-sm leading-relaxed">Hãy liên hệ với giáo viên hoặc quản trị viên để được thêm vào danh sách lớp học của bạn.</p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="c in filteredClassrooms" 
            :key="c.id"
            class="group bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-xl hover:border-red-500/10 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between cursor-pointer"
            @click="openClass(c)"
          >
            <div>
              <!-- Stylized Card Header banner -->
              <div class="h-32 welcome-banner relative overflow-hidden flex items-end p-5">
                <!-- Overlay glow design -->
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute right-4 top-4 text-xs font-bold px-2.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-white border border-white/15">
                  Đang học
                </div>
                <div class="relative">
                  <span class="text-[10px] uppercase font-medium tracking-wider text-white/80 block">{{ c.school_year?.name }}</span>
                  <h3 class="text-white font-medium text-xl mt-1 tracking-tight">{{ c.name }}</h3>
                </div>
              </div>

              <!-- Card body details -->
              <div class="p-6 space-y-4">
                <div class="flex items-center gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                  <div class="w-9 h-9 rounded-xl bg-[#d63015]/10 text-[#d63015] flex items-center justify-center text-xs shrink-0 font-bold">
                    {{ getInitials(c.homeroom_teacher?.name || 'GV') }}
                  </div>
                  <div class="min-w-0">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider leading-none">GV Chủ nhiệm</span>
                    <span class="text-xs font-bold text-slate-700 truncate block mt-1 leading-snug">{{ c.homeroom_teacher?.name || 'Chưa cập nhật' }}</span>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                  <div class="text-center p-3 rounded-2xl bg-slate-50/50 border border-slate-100">
                    <span class="text-lg block">👥</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block mt-1.5">Sĩ số</span>
                    <span class="text-xs font-extrabold text-slate-700 block mt-0.5">{{ c.students_count ?? 0 }} học sinh</span>
                  </div>
                  <div class="text-center p-3 rounded-2xl bg-slate-50/50 border border-slate-100">
                    <span class="text-lg block">📚</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block mt-1.5">Bài học</span>
                    <span class="text-xs font-extrabold text-slate-700 block mt-0.5">{{ c.lessons_count ?? 0 }} bài học</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card actions -->
            <div class="px-6 pb-6 pt-1">
              <button class="w-full py-3 bg-[#d63015] hover:bg-[#c02a10] text-white rounded-2xl text-xs font-extrabold transition-all shadow-md shadow-red-500/10 group-hover:shadow-lg group-hover:shadow-red-500/25 flex items-center justify-center gap-2">
                <span>Vào lớp học</span>
                <span class="group-hover:translate-x-1 transition-transform">➡️</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- View 2: Detailed Classroom Dashboard -->
    <div v-else-if="viewState === 'detail' && selectedClass" class="space-y-6">
      <!-- Back button and layout header -->
      <div class="flex items-center justify-between border-b border-slate-100 pb-4">
        <button 
          @click="viewState = 'list'"
          class="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all shadow-sm active:scale-95"
        >
          <span>⬅️ Quay lại danh sách</span>
        </button>
        <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">
          {{ selectedClass.school_year?.name }}
        </div>
      </div>

      <!-- Pulsing red banner for active live class -->
      <div v-if="activeLiveSession" class="bg-[#7c3aed] rounded-3xl p-5 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 animate-pulse">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-2xl bg-white/20 border border-white/20 flex items-center justify-center text-2xl shrink-0">
            🎥
          </div>
          <div>
            <h3 class="font-medium text-base tracking-tight uppercase">Lớp học trực tuyến đang diễn ra!</h3>
            <p class="text-xs text-white/85 mt-1.5 opacity-90 leading-relaxed font-medium">Lớp học trực tuyến do giáo viên chủ trì đang diễn ra. Bấm tham gia ngay để bắt đầu buổi học.</p>
          </div>
        </div>
        <RouterLink :to="`/live/${activeLiveSession.id}/room`" class="px-5 py-3 bg-white hover:bg-slate-100 text-[#7c3aed] rounded-2xl text-xs font-medium transition-all shadow-sm text-center active:scale-95 shrink-0">
          Tham gia phòng học trực tuyến 🎥
        </RouterLink>
      </div>

      <!-- Main classroom detail title block -->
      <div class="welcome-banner p-8 rounded-3xl text-white shadow-sm relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <span class="text-xs uppercase font-medium tracking-wider text-white/80 bg-white/10 px-3 py-1 rounded-full border border-white/10">{{ selectedClass.grade?.name }}</span>
            <h1 class="text-3xl font-medium mt-3 tracking-tight">{{ selectedClass.name }}</h1>
            <p class="text-sm text-white/80 mt-2 font-medium">Mã phòng học: <span class="font-mono bg-white/15 px-2 py-0.5 rounded border border-white/10 select-all">{{ selectedClass.room_code }}</span></p>
          </div>
          <div class="flex items-center gap-6 divide-x divide-white/20">
            <div class="text-center pl-6 first:pl-0">
              <span class="block text-2xl">👥</span>
              <span class="block text-[10px] text-white/80 font-medium uppercase mt-1">Sĩ số</span>
              <span class="block text-sm font-medium mt-0.5">{{ selectedClass.students?.length ?? selectedClass.students_count ?? 0 }}</span>
            </div>
            <div class="text-center pl-6">
              <span class="block text-2xl">📚</span>
              <span class="block text-[10px] text-white/80 font-medium uppercase mt-1">Bài học</span>
              <span class="block text-sm font-medium mt-0.5">{{ classLessons.length }}</span>
            </div>
            <div class="text-center pl-6">
              <span class="block text-2xl">✏️</span>
              <span class="block text-[10px] text-white/80 font-medium uppercase mt-1">Bài tập</span>
              <span class="block text-sm font-medium mt-0.5">{{ classAssignments.length }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Classroom content structure -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Column (Tabs + Content List) -->
        <div class="lg:col-span-2 space-y-4">
          <!-- Navigation Tabs -->
          <div class="bg-white rounded-2xl border border-slate-100 p-1.5 shadow-sm flex flex-wrap gap-1">
            <button 
              @click="activeTab = 'lessons'"
              class="flex-1 py-3 text-xs font-extrabold text-center rounded-xl transition-all uppercase tracking-wider flex items-center justify-center gap-1.5"
              :class="activeTab === 'lessons' ? 'bg-[#d63015] text-white shadow-md' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'"
            >
              <span>📚 Bài học</span>
            </button>
            <button 
              @click="activeTab = 'assignments'"
              class="flex-1 py-3 text-xs font-extrabold text-center rounded-xl transition-all uppercase tracking-wider flex items-center justify-center gap-1.5"
              :class="activeTab === 'assignments' ? 'bg-[#d63015] text-white shadow-md' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'"
            >
              <span>✏️ Bài tập</span>
              <span v-if="pendingAssignmentsCount > 0" class="px-1.5 py-0.5 rounded-full bg-amber-500 text-white text-[9px] font-bold">{{ pendingAssignmentsCount }}</span>
            </button>
            <button 
              @click="activeTab = 'exams'"
              class="flex-1 py-3 text-xs font-extrabold text-center rounded-xl transition-all uppercase tracking-wider flex items-center justify-center gap-1.5"
              :class="activeTab === 'exams' ? 'bg-[#d63015] text-white shadow-md' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'"
            >
              <span>📝 Đề thi</span>
            </button>
            <button 
              @click="activeTab = 'classmates'"
              class="flex-1 py-3 text-xs font-extrabold text-center rounded-xl transition-all uppercase tracking-wider flex items-center justify-center gap-1.5"
              :class="activeTab === 'classmates' ? 'bg-[#d63015] text-white shadow-md' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'"
            >
              <span>👥 Bạn học</span>
            </button>
          </div>

          <!-- Tab Content Cards Container -->
          <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm min-h-[40vh]">
            <!-- Tab 1: Lessons -->
            <div v-if="activeTab === 'lessons'" class="space-y-4">
              <div v-if="loadingDetail" class="py-12 text-center text-slate-400">
                <div class="animate-spin w-8 h-8 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-3"></div>
                Đang tải bài học...
              </div>
              <div v-else-if="classLessons.length === 0" class="text-center py-12 text-slate-400">
                <span class="text-4xl block mb-2">📚</span>
                Lớp học chưa có bài học nào được xuất bản.
              </div>
              <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div 
                  v-for="l in classLessons" 
                  :key="l.id"
                  class="group border border-slate-100 rounded-2xl p-5 hover:border-red-500/20 hover:shadow-md transition-all duration-300 flex flex-col justify-between"
                >
                  <div>
                    <div class="flex items-center justify-between mb-3">
                      <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-lg uppercase tracking-wider"
                        :style="{ backgroundColor: (l.subject?.color || '#d63015') + '15', color: l.subject?.color || '#d63015' }">
                        {{ l.subject?.name || 'Môn học' }}
                      </span>
                      <span class="text-[10px] text-slate-400 font-medium">Lượt xem: {{ l.view_count ?? 0 }}</span>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2 group-hover:text-[#c02a10] transition-colors mb-1.5">{{ l.title }}</h3>
                    <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed mb-4" v-if="l.description">{{ l.description }}</p>
                  </div>
                  <div class="flex items-center justify-between pt-3 border-t border-slate-50">
                    <span class="text-[10px] text-slate-400 font-medium">Tạo: {{ formatDate(l.created_at) }}</span>
                    <RouterLink :to="`/student/lessons/${l.id}`" class="px-3 py-1.5 bg-[#d63015]/10 hover:bg-[#d63015] text-[#d63015] hover:text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                      <span>Học ngay</span>
                      <span>📖</span>
                    </RouterLink>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab 2: Assignments -->
            <div v-if="activeTab === 'assignments'" class="space-y-4">
              <div v-if="loadingDetail" class="py-12 text-center text-slate-400">
                <div class="animate-spin w-8 h-8 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-3"></div>
                Đang tải bài tập...
              </div>
              <div v-else-if="classAssignments.length === 0" class="text-center py-12 text-slate-400">
                <span class="text-4xl block mb-2">✏️</span>
                Lớp học chưa có bài tập nào.
              </div>
              <div v-else class="space-y-3">
                <div 
                  v-for="a in classAssignments" 
                  :key="a.id"
                  class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-2xl border border-slate-100 hover:border-red-500/10 hover:bg-red-50/10 transition-all gap-4"
                >
                  <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                      :style="{ backgroundColor: (a.subject?.color || '#d63015') + '15' }">
                      <span class="text-lg" :style="{ color: a.subject?.color || '#d63015' }">✏️</span>
                    </div>
                    <div>
                      <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-1">{{ a.title }}</h3>
                      <p class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                        <span class="font-semibold" :style="{ color: a.subject?.color || '#d63015' }">{{ a.subject?.name }}</span>
                        <span>•</span>
                        <span :class="isOverdue(a.deadline) ? 'text-red-500 font-bold' : 'text-slate-400 font-medium'">
                          Hạn chót: {{ a.deadline ? formatDate(a.deadline) : 'Không có hạn' }}
                        </span>
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3 justify-end shrink-0">
                    <span 
                      class="text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase border"
                      :class="a.submissions_count > 0 || a.submitted ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'"
                    >
                      {{ a.submissions_count > 0 || a.submitted ? 'Đã nộp bài' : 'Chưa nộp bài' }}
                    </span>
                    <RouterLink :to="`/student/assignments/${a.id}`" class="px-4 py-2 bg-slate-100 hover:bg-[#d63015] text-slate-700 hover:text-white rounded-xl text-xs font-bold transition-all">
                      Chi tiết
                    </RouterLink>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab 3: Exams -->
            <div v-if="activeTab === 'exams'" class="space-y-4">
              <div v-if="loadingDetail" class="py-12 text-center text-slate-400">
                <div class="animate-spin w-8 h-8 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-3"></div>
                Đang tải đề thi...
              </div>
              <div v-else-if="classExams.length === 0" class="text-center py-12 text-slate-400">
                <span class="text-4xl block mb-2">📝</span>
                Lớp học chưa có đề thi hoặc bài kiểm tra nào.
              </div>
              <div v-else class="space-y-3">
                <div 
                  v-for="e in classExams" 
                  :key="e.id"
                  class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-2xl border border-slate-100 hover:border-red-500/10 hover:bg-red-50/10 transition-all gap-4"
                >
                  <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                      :style="{ backgroundColor: (e.subject?.color || '#d63015') + '15' }">
                      <span class="text-lg" :style="{ color: e.subject?.color || '#d63015' }">📝</span>
                    </div>
                    <div>
                      <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-1">{{ e.title }}</h3>
                      <p class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                        <span class="font-semibold" :style="{ color: e.subject?.color || '#d63015' }">{{ e.subject?.name }}</span>
                        <span>•</span>
                        <span>Thời lượng: {{ e.duration_minutes }} phút</span>
                        <span>•</span>
                        <span>Số câu: {{ e.questions_count ?? 0 }}</span>
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3 justify-end shrink-0">
                    <span class="text-[10px] text-slate-400 font-bold uppercase mr-2">Hạn: {{ formatDate(e.closed_at) }}</span>
                    <RouterLink :to="`/student/exams/${e.id}`" class="px-4 py-2 bg-[#d63015] hover:bg-[#c02a10] text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                      Vào thi 🚀
                    </RouterLink>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab 4: Classmates -->
            <div v-if="activeTab === 'classmates'" class="space-y-4">
              <div v-if="!selectedClass.students || selectedClass.students.length === 0" class="text-center py-12 text-slate-400">
                <span class="text-4xl block mb-2">👥</span>
                Không tìm thấy danh sách học sinh.
              </div>
              <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div 
                  v-for="s in selectedClass.students" 
                  :key="s.id"
                  class="flex items-center gap-3 p-3.5 rounded-2xl border border-slate-50 bg-slate-50/50 hover:bg-slate-50 transition-colors"
                >
                  <div class="w-10 h-10 rounded-full bg-[#7c3aed] text-white font-medium flex items-center justify-center shadow-inner text-sm shrink-0">
                    {{ getInitials(s.name) }}
                  </div>
                  <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 truncate leading-snug">{{ s.name }}</p>
                    <p class="text-[10px] text-slate-400 truncate leading-none mt-1">{{ s.email }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Column (Teacher details) -->
        <div class="space-y-6">
          <!-- Homeroom teacher card -->
          <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm space-y-4">
            <h4 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider border-l-3 border-[#d63015] pl-2.5 leading-none">Giáo viên chủ nhiệm</h4>
            
            <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
              <div class="w-12 h-12 rounded-2xl bg-[#1a1a1a] text-white flex items-center justify-center font-medium text-lg shadow-sm shrink-0">
                {{ getInitials(selectedClass.homeroom_teacher?.name || 'GV') }}
              </div>
              <div class="min-w-0">
                <h5 class="font-extrabold text-slate-800 text-sm leading-snug truncate">{{ selectedClass.homeroom_teacher?.name || 'Chưa cập nhật' }}</h5>
                <p class="text-xs text-slate-400 mt-1 truncate">{{ selectedClass.homeroom_teacher?.email || 'email@example.com' }}</p>
              </div>
            </div>
          </div>

          <!-- Subject teachers list -->
          <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm space-y-4">
            <h4 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider border-l-3 border-[#d63015] pl-2.5 leading-none">Giáo viên bộ môn</h4>

            <div v-if="!selectedClass.subject_teachers || selectedClass.subject_teachers.length === 0" class="text-xs text-slate-400 text-center py-4">
              Chưa gán giáo viên bộ môn
            </div>
            <div v-else class="space-y-2.5">
              <div 
                v-for="(st, idx) in selectedClass.subject_teachers" 
                :key="idx"
                class="flex items-center justify-between p-3 rounded-2xl border border-slate-50 bg-slate-50/50 hover:bg-slate-50 transition-colors gap-3"
              >
                <div class="flex items-center gap-2.5 min-w-0">
                  <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: st.subject?.color || '#d63015' }"></span>
                  <div class="min-w-0">
                    <span class="text-xs font-bold text-slate-700 truncate block">{{ st.subject?.name }}</span>
                    <span class="text-[10px] text-slate-400 block mt-0.5 truncate">{{ st.teacher?.name || 'Chưa phân công' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@api/axios'

const classrooms = ref([])
const loading = ref(true)
const viewState = ref('list') // 'list' or 'detail'
const loadingDetail = ref(false)
const selectedClass = ref(null)
const classLessons = ref([])
const classAssignments = ref([])
const classExams = ref([])
const activeTab = ref('lessons')
const searchQuery = ref('')
const activeLiveSession = ref(null)

// Gradient cards mapper
function getGradientClass(id) {
  const gradients = [
    'from-[#5d0e06] to-[#8b1c0e]',
    'from-[#8b1c0e] to-[#b82a17]',
    'from-[#b82a17] to-[#d63015]',
    'from-[#d63015] to-[#f4513b]',
    'from-[#7a1409] to-[#a22014]',
    'from-[#c02a10] to-[#e63c22]'
  ]
  return gradients[Number(id) % gradients.length]
}

// Helper methods
function getInitials(name = '') {
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
}

function formatDate(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function isOverdue(due) {
  return due && new Date(due) < new Date()
}

const pendingAssignmentsCount = computed(() => {
  return classAssignments.value.filter(a => !(a.submissions_count > 0 || a.submitted)).length
})

const filteredClassrooms = computed(() => {
  if (!searchQuery.value) return classrooms.value
  const q = searchQuery.value.toLowerCase()
  return classrooms.value.filter(c => 
    c.name?.toLowerCase().includes(q) || 
    c.school_year?.name?.toLowerCase().includes(q) ||
    c.room_code?.toLowerCase().includes(q) ||
    c.homeroom_teacher?.name?.toLowerCase().includes(q)
  )
})

async function openClass(c) {
  selectedClass.value = c
  viewState.value = 'detail'
  loadingDetail.value = true
  classLessons.value = []
  classAssignments.value = []
  classExams.value = []
  activeLiveSession.value = null
  activeTab.value = 'lessons'

  try {
    // 1. Fetch details
    const detailPromise = api.get(`/student/classrooms/${c.id}`).then(({ data }) => {
      selectedClass.value = data.data ?? c
    }).catch(() => {})

    // 2. Fetch lessons
    const lessonsPromise = api.get('/student/lessons', { params: { classroom_id: c.id } }).then(({ data }) => {
      classLessons.value = data.data?.data ?? data.data ?? []
    }).catch(() => {})

    // 3. Fetch assignments
    const assignmentsPromise = api.get('/student/assignments', { params: { classroom_id: c.id } }).then(({ data }) => {
      classAssignments.value = data.data?.data ?? data.data ?? []
    }).catch(() => {})

    // 4. Fetch exams
    const examsPromise = api.get('/student/exams', { params: { classroom_id: c.id } }).then(({ data }) => {
      classExams.value = data.data?.data ?? data.data ?? []
    }).catch(() => {})

    // 5. Fetch active live room
    const livePromise = api.get('/student/my-rooms').then(({ data }) => {
      const activeRooms = data.data ?? []
      // Find live rooms for this classroom
      activeLiveSession.value = activeRooms.find(s => s.status === 'live' && Number(s.classroom_id) === Number(c.id)) || null
    }).catch(() => {})

    await Promise.all([detailPromise, lessonsPromise, assignmentsPromise, examsPromise, livePromise])
  } finally {
    loadingDetail.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await api.get('/student/classrooms')
    classrooms.value = data.data ?? []
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.welcome-banner {
  background-image: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%) !important;
  position: relative;
  overflow: hidden;
}

.welcome-banner::before {
  content: '';
  position: absolute;
  top: -50px;
  right: -50px;
  width: 150px;
  height: 150px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.08);
  pointer-events: none;
}

.welcome-banner::after {
  content: '';
  position: absolute;
  bottom: -30px;
  right: 120px;
  width: 80px;
  height: 80px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.05);
  pointer-events: none;
}
</style>
