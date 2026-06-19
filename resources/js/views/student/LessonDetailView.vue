<template>
  <div class="space-y-6 max-w-7xl mx-auto px-1">
    <!-- ── Loading Skeleton ── -->
    <div v-if="loading" class="py-16 text-center">
      <div class="animate-spin w-10 h-10 border-4 border-[#d63015] border-t-transparent rounded-full mx-auto mb-4"/>
      <p class="text-sm text-gray-500 font-medium">Đang tải nội dung bài học...</p>
    </div>

    <!-- ── Not Found / Error ── -->
    <div v-else-if="!lesson" class="py-16 text-center bg-white rounded-3xl border border-gray-100 shadow-sm max-w-xl mx-auto">
      <div class="text-5xl mb-4">📚</div>
      <h3 class="text-lg font-bold text-gray-800 mb-1">Không tìm thấy bài học</h3>
      <p class="text-gray-500 text-sm mb-6 px-6">Bài học này có thể không tồn tại hoặc bạn không có quyền truy cập.</p>
      <RouterLink to="/student/lessons" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#d63015] hover:bg-[#c02a10] text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-red-500/10">
        ← Quay lại danh sách
      </RouterLink>
    </div>

    <!-- ── Content ── -->
    <template v-else>
      <!-- Breadcrumb navigation -->
      <div class="flex items-center justify-between flex-wrap gap-3">
        <RouterLink to="/student/lessons" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-[#d63015] transition-colors group">
          <svg class="w-4 h-4 transform group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
          </svg>
          Quay lại danh sách bài học
        </RouterLink>

        <!-- Learn button -->
        <RouterLink :to="`/student/lessons/${lesson.id}/learn`" class="inline-flex items-center gap-2 px-4 py-2 bg-[#1a1a1a] hover:bg-[#2d2d2d] text-white rounded-xl text-xs font-bold shadow-sm transition-all hover:scale-[1.02]">
          Vào phòng học tương tác →
        </RouterLink>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Area (Left Column) -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Lesson Header Info Card -->
          <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden relative">
            <div class="h-2" :style="{ backgroundColor: lesson.subject?.color || '#d63015' }"/>
            <div class="p-6 sm:p-8">
              <div class="flex flex-wrap items-center gap-2.5 mb-4">
                <span class="text-xs font-bold px-3 py-1 rounded-xl"
                  :style="{ backgroundColor: (lesson.subject?.color || '#d63015') + '15', color: lesson.subject?.color || '#d63015' }">
                  {{ lesson.subject?.name }}
                </span>
                <span class="text-xs font-semibold text-gray-500 bg-gray-50 px-2.5 py-1 rounded-xl border border-gray-100">
                  {{ lesson.classroom?.name }}
                </span>
              </div>
              
              <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight mb-3">
                {{ lesson.title }}
              </h1>
              
              <p v-if="lesson.description" class="text-gray-600 text-sm leading-relaxed mb-6 bg-gray-50/50 p-4 rounded-2xl border border-gray-100/50">
                {{ lesson.description }}
              </p>

              <div class="flex flex-wrap items-center gap-6 pt-5 border-t border-gray-100 text-xs text-gray-400">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center font-bold text-[#d63015] border border-red-100">
                    {{ lesson.teacher?.name?.charAt(0) }}
                  </div>
                  <div>
                    <span class="block text-[10px] text-gray-400">Giảng viên</span>
                    <span class="font-bold text-gray-750">{{ lesson.teacher?.name }}</span>
                  </div>
                </div>
                <div class="flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <span>{{ lesson.view_count ?? 0 }} lượt xem</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Video Player Block -->
          <div v-if="lesson.video_path" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
              <h3 class="font-bold text-gray-800 text-sm sm:text-base flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-[#d63015]"/>
                Video bài giảng
              </h3>
            </div>
            <div class="p-6 bg-gray-50/50">
              <div class="aspect-video w-full rounded-2xl overflow-hidden bg-black shadow-md border border-gray-200/50">
                <video :src="lesson.video_path" controls class="w-full h-full object-contain"/>
              </div>
            </div>
          </div>

          <!-- Detailed Content Prose -->
          <div v-if="lesson.content" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
              <span class="w-1.5 h-4 rounded-full bg-[#d63015]"/>
              <h3 class="font-bold text-gray-800 text-sm sm:text-base">Nội dung học tập</h3>
            </div>
            <div class="p-6 sm:p-8 prose prose-red max-w-none text-gray-700 leading-relaxed text-sm sm:text-base" v-html="renderMath(lesson.content)"/>
          </div>

          <!-- Interactive Review Questions -->
          <div v-if="lesson.questions?.length" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between flex-wrap gap-3 bg-gradient-to-r from-gray-50 to-white">
              <div class="flex items-center gap-2.5">
                <div class="w-2.5 h-5 rounded-full" :style="{ backgroundColor: lesson.subject?.color || '#d63015' }"/>
                <h3 class="font-extrabold text-gray-900 text-base sm:text-lg">Bài tập tự luyện</h3>
              </div>
              <span class="text-xs font-bold text-[#d63015] bg-[#d63015]/10 px-3 py-1 rounded-full">
                {{ lesson.questions.length }} câu hỏi
              </span>
            </div>
            
            <div class="p-6 sm:p-8 space-y-8 divide-y divide-gray-100">
              <div v-for="(q, idx) in lesson.questions" :key="q.id" :class="{ 'pt-8': idx > 0 }" class="space-y-5">
                <!-- Header of Question -->
                <div class="flex items-center justify-between flex-wrap gap-2">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-extrabold px-3 py-1.5 rounded-xl bg-gray-100 text-gray-700">
                      Câu {{ idx + 1 }}
                    </span>
                    <span class="text-xs font-extrabold px-2.5 py-1 rounded-lg" :class="qTypeClass(q.type)">
                      {{ qTypeLabel(q.type) }}
                    </span>
                  </div>
                  <span class="text-xs font-semibold text-gray-400">Độ khó: {{ qDifficultyLabel(q.difficulty) }}</span>
                </div>

                <!-- Text of Question -->
                <div class="text-sm sm:text-base text-gray-800 leading-relaxed font-bold q-content pt-1" v-html="renderMath(q.content)"/>

                <!-- Listening Question Audio Player -->
                <div v-if="q.type === 'listening' && q.audio_path" class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                  <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Âm thanh nghe:</p>
                  <audio :src="'/storage/' + q.audio_path" controls class="w-full h-10 rounded-lg"/>
                </div>

                <!-- Interactive Answers Inputs -->
                <div class="space-y-3 pl-1">
                  <!-- Radio options: multiple choice / true-false / listening -->
                  <div v-if="['multiple_choice', 'true_false', 'listening'].includes(q.type)" class="grid gap-2.5">
                    <label v-for="(opt, oi) in q.options" :key="oi"
                      class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-all hover:scale-[1.005] duration-200 text-xs sm:text-sm"
                      :class="getOptionClass(q.id, String(oi))">
                      <input type="radio" :name="`q-detail-${q.id}`" :value="String(oi)"
                        v-model="answers[q.id]"
                        :disabled="checkedQuestions[q.id]"
                        class="w-4.5 h-4.5 accent-[#d63015] shrink-0 mt-0.5" />
                      <span class="font-bold text-gray-400 w-5 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
                      <span class="text-gray-800 leading-relaxed flex-1" v-html="renderMath(optionText(opt))"></span>
                    </label>
                  </div>

                  <!-- Checkboxes: multiple select -->
                  <div v-else-if="q.type === 'multiple_select'" class="grid gap-2.5">
                    <label v-for="(opt, oi) in q.options" :key="oi"
                      class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition-all hover:scale-[1.005] duration-200 text-xs sm:text-sm"
                      :class="getMultiSelectOptionClass(q.id, String(oi))">
                      <input type="checkbox" :value="String(oi)"
                        :checked="(answers[q.id] || []).includes(String(oi))"
                        @change="togglePracticeMulti(q.id, String(oi))"
                        :disabled="checkedQuestions[q.id]"
                        class="w-4.5 h-4.5 accent-[#d63015] rounded shrink-0 mt-0.5" />
                      <span class="font-bold text-gray-400 w-5 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
                      <span class="text-gray-800 leading-relaxed flex-1" v-html="renderMath(optionText(opt))"></span>
                    </label>
                  </div>

                  <!-- Text input: fill blank -->
                  <div v-else-if="q.type === 'fill_blank'" class="space-y-3">
                    <div v-for="(_, bi) in getFillBlanks(q)" :key="bi" class="flex items-center gap-3">
                      <span class="text-xs font-bold text-gray-500 bg-gray-50 px-3 py-3 rounded-xl border border-gray-100 w-24 text-center shrink-0">
                        Chỗ [{{ bi + 1 }}]
                      </span>
                      <input :value="(answers[q.id] || [])[bi] || ''"
                        @input="setPracticeFillBlank(q.id, bi, $event.target.value)"
                        :disabled="checkedQuestions[q.id]"
                        class="flex-1 px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] transition-all"
                        :placeholder="`Điền vào chỗ trống ${bi + 1}...`" />
                    </div>
                  </div>

                  <!-- Text input: short answer -->
                  <div v-else-if="q.type === 'short_answer'" class="space-y-2">
                    <input v-model="answers[q.id]"
                      :disabled="checkedQuestions[q.id]"
                      class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] transition-all"
                      placeholder="Nhập câu trả lời ngắn của bạn..." />
                  </div>

                  <!-- Textarea: essay -->
                  <div v-else-if="q.type === 'essay'" class="space-y-2">
                    <textarea v-model="answers[q.id]"
                      :disabled="checkedQuestions[q.id]"
                      rows="4"
                      class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] transition-all resize-none"
                      placeholder="Nhập bài làm tự luận của bạn..."></textarea>
                  </div>
                </div>

                <!-- Answer Checked Feedback & Explanation -->
                <div v-if="checkedQuestions[q.id]" class="mt-4 p-5 rounded-2xl border transition-all duration-350"
                  :class="results[q.id] ? 'bg-green-50/50 border-green-200 text-green-800' : 'bg-red-50/50 border-red-200 text-red-800'">
                  <div class="flex items-start gap-3">
                    <span class="text-xl leading-none shrink-0 mt-0.5">{{ results[q.id] ? '✅' : '❌' }}</span>
                    <div class="space-y-2 flex-1">
                      <p class="font-extrabold text-sm sm:text-base">
                        {{ results[q.id] ? 'Chính xác! Bạn làm rất tốt.' : 'Chưa chính xác rồi. Hãy xem đáp án bên dưới.' }}
                      </p>
                      <p v-if="!results[q.id]" class="text-xs sm:text-sm text-red-750">
                        <span class="font-bold">Đáp án đúng:</span> 
                        <span class="font-bold ml-1" v-html="renderMath(formatCorrectAnswerText(q))"></span>
                      </p>
                      <div v-if="q.explanation" class="mt-3 pt-3 border-t border-gray-200/50 text-xs sm:text-sm text-gray-650">
                        <span class="font-bold text-gray-700 block mb-1">Hướng dẫn giải chi tiết:</span>
                        <div class="leading-relaxed" v-html="renderMath(q.explanation)"/>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Question Actions -->
                <div class="flex gap-2 justify-end pt-3">
                  <button v-if="checkedQuestions[q.id]" type="button" @click="resetQuestion(q.id)"
                    class="px-5 py-2.5 border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-xl text-xs font-bold transition-all hover:border-gray-350 cursor-pointer">
                    Làm lại câu này
                  </button>
                  <button v-else type="button" @click="checkAnswer(q)" :disabled="isAnswerEmpty(q)"
                    class="px-5 py-2.5 bg-[#d63015] hover:bg-[#c02a10] disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold shadow-md shadow-red-500/10 hover:shadow-lg transition-all cursor-pointer">
                    Kiểm tra kết quả
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Attachments / Materials Section -->
          <div v-if="lesson.materials?.length" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
              <h3 class="font-bold text-gray-800 text-sm sm:text-base flex items-center gap-2">
                <span class="w-1.5 h-4 rounded-full bg-[#d63015]"/>
                Tài liệu bài học đính kèm
              </h3>
              <span class="text-xs font-bold text-gray-500 bg-gray-50 px-2.5 py-1 rounded-xl border border-gray-150">
                {{ lesson.materials.length }} file
              </span>
            </div>
            <div class="p-6 space-y-3">
              <div v-for="m in lesson.materials" :key="m.id"
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl border border-gray-100 transition-all hover:border-red-200"
                :class="materialHoverClass(m.file_type)">
                <div class="flex items-center gap-3.5 min-w-0">
                  <!-- File Icon Badge -->
                  <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 text-white text-xs font-extrabold shadow-sm"
                    :class="materialBgClass(m.file_type)">
                    {{ materialLabel(m.file_type) }}
                  </div>
                  <!-- File Info -->
                  <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-800 truncate">{{ m.file_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ materialTypeName(m.file_type) }}
                      <span v-if="m.file_size"> · {{ formatSize(m.file_size) }}</span>
                      <span v-if="m.download_count"> · {{ m.download_count }} tải</span>
                    </p>
                  </div>
                </div>

                <!-- Actions buttons -->
                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                  <button @click="openPreview(m)"
                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-xl transition-all cursor-pointer"
                    :class="materialActionClass(m.file_type)">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Xem trực tiếp
                  </button>
                  <a :href="`/api/student/lessons/${lesson.id}/materials/${m.id}/download`"
                    :download="m.file_name"
                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Tải file
                  </a>
                </div>
              </div>
            </div>
          </div>
          
          <!-- File Preview Drawer -->
          <MaterialPreviewModal v-model="previewOpen" :material="previewMaterial" />
        </div>

        <!-- Sidebar Widget (Right Column) -->
        <div class="space-y-5">
          <!-- Study Progress Ring Card -->
          <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 relative overflow-hidden text-center">
            <h3 class="font-extrabold text-gray-800 text-sm sm:text-base text-left mb-5 flex items-center gap-2">
              <span class="w-1.5 h-4 rounded-full bg-[#d63015]"/>
              Tiến trình học tập
            </h3>

            <!-- SVG Ring Progress -->
            <div class="relative w-32 h-32 mx-auto mb-5">
              <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="40" fill="none" stroke="#f3f4f6" stroke-width="8"/>
                <circle cx="50" cy="50" r="40" fill="none"
                  :stroke="lesson.subject?.color || '#d63015'"
                  stroke-width="8" stroke-linecap="round"
                  :stroke-dasharray="`${2.513 * progress} 251.3`"
                  class="transition-all duration-700"/>
              </svg>
              <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-3xl font-extrabold text-gray-800">{{ progress }}%</span>
                <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Tiến độ</span>
              </div>
            </div>

            <!-- Complete check button -->
            <button @click="markComplete" :disabled="progress >= 100 || markingProgress"
              class="w-full py-3 rounded-2xl text-xs sm:text-sm font-bold shadow-md shadow-red-500/5 transition-all hover:scale-[1.01] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
              :class="progress >= 100 ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-[#d63015] hover:bg-[#c02a10] text-white'">
              <span v-if="progress >= 100" class="flex items-center justify-center gap-1">
                ✓ Đã hoàn thành bài học
              </span>
              <span v-else-if="markingProgress">Đang ghi nhận...</span>
              <span v-else>Đánh dấu đã học xong</span>
            </button>
          </div>

          <!-- Related Exams Card -->
          <div v-if="relatedExams.length" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-2">
              <span class="w-1.5 h-4 rounded-full bg-green-500"/>
              <h3 class="font-bold text-gray-800 text-sm sm:text-base">Bài kiểm tra cùng môn</h3>
            </div>
            <div class="divide-y divide-gray-50">
              <RouterLink v-for="e in relatedExams" :key="e.id" to="/student/exams"
                class="flex items-center gap-3.5 px-5 py-4 hover:bg-gray-50/70 transition-colors group">
                <div class="w-9 h-9 rounded-xl bg-green-50 border border-green-150 flex items-center justify-center shrink-0 transition-transform group-hover:scale-105">
                  <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-bold text-gray-850 truncate group-hover:text-green-700 transition-colors">
                    {{ e.title }}
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-2">
                    <span>{{ e.duration_minutes }} phút</span>
                    <span v-if="e.questions_count">· {{ e.questions_count }} câu</span>
                  </p>
                </div>
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@api/axios'
import MaterialPreviewModal from '@components/common/MaterialPreviewModal.vue'
import { renderMath } from '@/utils/math'

const route = useRoute()
const lesson = ref(null)
const loading = ref(true)
const progress = ref(0)
const markingProgress = ref(false)
const relatedExams = ref([])
const previewOpen = ref(false)
const previewMaterial = ref(null)

// Practice Quiz states
const answers = ref({})
const checkedQuestions = ref({})
const results = ref({})

const qTypeLabel = (t) => ({
  multiple_choice: 'Trắc nghiệm',   multiple_select: 'Nhiều đáp án',  true_false: 'Đúng/Sai',
  fill_blank:      'Điền chỗ trống', short_answer: 'Trả lời ngắn',     essay: 'Tự luận',
  ordering:        'Sắp xếp',        matching: 'Ghép đôi',              listening: 'Nghe hiểu',
  writing:         'Viết',           calculation: 'Tính toán',           reading: 'Đọc hiểu',
  speaking:        'Nói',
}[t] ?? t)

const qTypeClass = (t) => ({
  multiple_choice: 'bg-blue-50 text-blue-700 border border-blue-150',    multiple_select: 'bg-red-50 text-[#c02a10] border border-red-150',
  true_false:      'bg-teal-50 text-teal-700 border border-teal-150',     fill_blank: 'bg-amber-50 text-amber-700 border border-amber-150',
  short_answer:    'bg-yellow-50 text-yellow-700 border border-yellow-150', essay: 'bg-gray-50 text-gray-650 border border-gray-150',
  ordering:        'bg-pink-50 text-pink-700 border border-pink-150',     matching: 'bg-purple-50 text-purple-700 border border-purple-150',
  listening:       'bg-green-50 text-green-700 border border-green-150',   writing: 'bg-cyan-50 text-cyan-700 border border-cyan-150',
  calculation:     'bg-red-50 text-[#c02a10] border border-red-150', reading: 'bg-emerald-50 text-emerald-700 border border-emerald-150',
  speaking:        'bg-rose-50 text-rose-700 border border-rose-150',
}[t] ?? 'bg-gray-50 text-gray-500')

const qDifficultyLabel = (d) => ({
  easy: 'Dễ',
  medium: 'Trung bình',
  hard: 'Khó'
}[d] ?? d)

const stripHtml = (html) => html ? html.replace(/<[^>]*>/g, '').trim() : ''
const optionText = (opt) => {
  if (opt === null || opt === undefined) return ''
  if (typeof opt === 'object') return stripHtml(String(opt.text ?? opt.label ?? opt.content ?? ''))
  return stripHtml(String(opt))
}

function getFillBlanks(q) {
  const matches = (q.content || '').match(/___/g)
  return Array(matches ? matches.length : 1).fill(null)
}

function setPracticeFillBlank(qId, idx, val) {
  if (!Array.isArray(answers.value[qId])) answers.value[qId] = []
  answers.value[qId][idx] = val
}

function togglePracticeMulti(qId, val) {
  if (!answers.value[qId]) answers.value[qId] = []
  const idx = answers.value[qId].indexOf(val)
  if (idx === -1) answers.value[qId].push(val)
  else answers.value[qId].splice(idx, 1)
}

function isAnswerEmpty(q) {
  const a = answers.value[q.id]
  if (a === undefined || a === null || a === '') return true
  if (Array.isArray(a)) return !a.some(v => v !== '' && v !== undefined && v !== null)
  if (typeof a === 'object') return Object.keys(a).length === 0
  return false
}

function checkAnswer(q) {
  const studentAns = answers.value[q.id]
  const correct = q.correct_answer

  let isCorrect = false

  if (q.type === 'multiple_choice' || q.type === 'true_false' || q.type === 'listening') {
    const corrVal = Array.isArray(correct) ? correct[0] : correct
    isCorrect = String(studentAns) === String(corrVal)
  } else if (q.type === 'multiple_select') {
    const corrArr = Array.isArray(correct) ? correct.map(String).sort() : []
    const studArr = Array.isArray(studentAns) ? studentAns.map(String).sort() : []
    isCorrect = corrArr.length === studArr.length && corrArr.every((v, i) => v === studArr[i])
  } else if (q.type === 'fill_blank') {
    const corrArr = Array.isArray(correct) ? correct.map(v => String(v).trim().toLowerCase()) : []
    const studArr = Array.isArray(studentAns) ? studentAns.map(v => String(v || '').trim().toLowerCase()) : []
    isCorrect = corrArr.length === studArr.length && corrArr.every((v, i) => v === studArr[i])
  } else if (q.type === 'short_answer') {
    const corrVal = Array.isArray(correct) ? correct[0] : correct
    isCorrect = String(studentAns).trim().toLowerCase() === String(corrVal).trim().toLowerCase()
  } else if (q.type === 'essay') {
    isCorrect = true
  }

  results.value[q.id] = isCorrect
  checkedQuestions.value[q.id] = true
}

function resetQuestion(qId) {
  answers.value[qId] = undefined
  checkedQuestions.value[qId] = false
  results.value[qId] = false
}

function formatCorrectAnswerText(q) {
  const correct = q.correct_answer
  if (correct === null || correct === undefined) return '—'
  if (q.type === 'true_false') {
    const val = Array.isArray(correct) ? correct[0] : correct
    return String(val) === '0' || String(val) === 'T' ? 'Đúng' : 'Sai'
  }
  if (Array.isArray(correct)) {
    if (q.type === 'fill_blank') return correct.map((v, i) => `[Chỗ ${i+1}]: ${v}`).join(', ')
    return correct.map(i => q.options?.[Number(i)] ?? i).join(', ')
  }
  return q.options?.[Number(correct)] ?? String(correct)
}

function isCorrectOption(qId, oi) {
  const q = lesson.value?.questions?.find(item => item.id === qId)
  if (!q) return false
  const correct = q.correct_answer
  if (correct === null || correct === undefined) return false
  if (Array.isArray(correct)) {
    return correct.includes(String(oi))
  }
  return String(correct) === String(oi)
}

function getOptionClass(qId, oi) {
  if (!checkedQuestions.value[qId]) {
    return answers.value[qId] === oi
      ? 'border-[#d63015] bg-[#d63015]/5 text-[#d63015] font-semibold'
      : 'border-gray-200 bg-white hover:border-[#d63015]/40 hover:bg-gray-50 text-gray-700'
  }
  const isSelected = answers.value[qId] === oi
  const isCorrect = isCorrectOption(qId, oi)
  if (isCorrect) return 'border-green-500 bg-green-50/50 text-green-800 font-semibold'
  if (isSelected && !isCorrect) return 'border-red-500 bg-red-50/50 text-red-800 font-semibold'
  return 'border-gray-150 bg-white opacity-60 text-gray-400'
}

function getMultiSelectOptionClass(qId, oi) {
  const selectedArr = answers.value[qId] || []
  const isSelected = selectedArr.includes(oi)
  if (!checkedQuestions.value[qId]) {
    return isSelected
      ? 'border-[#d63015] bg-[#d63015]/5 text-[#d63015] font-semibold'
      : 'border-gray-200 bg-white hover:border-[#d63015]/40 hover:bg-gray-50 text-gray-700'
  }
  const isCorrect = isCorrectOption(qId, oi)
  if (isCorrect) return 'border-green-500 bg-green-50/50 text-green-800 font-semibold'
  if (isSelected && !isCorrect) return 'border-red-500 bg-red-50/50 text-red-800 font-semibold'
  return 'border-gray-150 bg-white opacity-60 text-gray-400'
}

function openPreview(m) {
  previewMaterial.value = m
  previewOpen.value = true
}

function normalizeFileType(type) {
  if (!type) return 'file'
  const t = type.toLowerCase()
  if (['doc', 'docx'].includes(t)) return 'word'
  if (['ppt', 'pptx'].includes(t)) return 'ppt'
  if (t === 'pdf') return 'pdf'
  return t
}

function materialActionClass(type) {
  const norm = normalizeFileType(type)
  return { 
    pdf: 'bg-red-50 text-red-600 hover:bg-red-100', 
    word: 'bg-blue-50 text-blue-600 hover:bg-blue-100', 
    ppt: 'bg-orange-50 text-orange-600 hover:bg-orange-100' 
  }[norm] ?? 'bg-gray-50 text-gray-600 hover:bg-gray-100'
}

function materialBgClass(type) {
  const norm = normalizeFileType(type)
  return { 
    pdf: 'bg-red-500', 
    word: 'bg-blue-500', 
    ppt: 'bg-orange-500' 
  }[norm] ?? 'bg-gray-400'
}

function materialHoverClass(type) {
  const norm = normalizeFileType(type)
  return { 
    pdf: 'hover:border-red-200 hover:bg-red-50/40', 
    word: 'hover:border-blue-200 hover:bg-blue-50/40', 
    ppt: 'hover:border-orange-200 hover:bg-orange-50/40' 
  }[norm] ?? 'hover:border-gray-250 hover:bg-gray-50'
}

function materialLabel(type) {
  const norm = normalizeFileType(type)
  return { pdf: 'PDF', word: 'DOC', ppt: 'PPT' }[norm] ?? 'FILE'
}

function materialTypeName(type) {
  const norm = normalizeFileType(type)
  return { pdf: 'Tài liệu PDF', word: 'Tài liệu Word', ppt: 'Bài giảng PowerPoint' }[norm] ?? 'Tài liệu đính kèm'
}

function formatSize(bytes) {
  if (!bytes) return ''
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1024 / 1024).toFixed(1) + ' MB'
}

async function markComplete() {
  markingProgress.value = true
  try {
    await api.post(`/student/lessons/${route.params.id}/progress`, { completed: true })
    progress.value = 100
  } finally { 
    markingProgress.value = false 
  }
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/student/lessons/${route.params.id}`)
    const payload = data.data ?? data
    lesson.value = payload.lesson ?? payload
    progress.value = (payload.progress?.is_completed || payload.progress?.completed) ? 100 : 0
    
    // Related exams from same subject
    try {
      const ex = await api.get('/student/exams', { params: { per_page: 10 } })
      const all = ex.data.data?.data ?? ex.data.data ?? []
      relatedExams.value = all.filter(e => e.subject_id === lesson.value.subject_id).slice(0, 3)
    } catch { /* ignore */ }
  } finally { 
    loading.value = false 
  }
})
</script>
