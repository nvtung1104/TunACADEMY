<template>
  <!-- -m-5 pulls content flush to the main container edges, overriding the p-5 padding -->
  <div class="-m-5 flex flex-col bg-gray-50/50" style="min-height: calc(100vh - 56px)">

    <!-- ── Loading ── -->
    <div v-if="loading" class="flex-1 flex items-center justify-center py-24">
      <div class="text-center">
        <div class="w-10 h-10 border-4 border-[#d63015] border-t-transparent rounded-full animate-spin mx-auto mb-4"/>
        <p class="text-sm text-gray-500 font-medium">Đang tải phòng học tương tác...</p>
      </div>
    </div>

    <!-- ── Error / Not found ── -->
    <div v-else-if="!lesson" class="flex-1 flex items-center justify-center py-20">
      <div class="text-center max-w-sm px-6 bg-white py-10 rounded-3xl border border-gray-100 shadow-sm mx-4">
        <div class="text-5xl mb-4">{{ errorCode === 403 ? '🔒' : '😕' }}</div>
        <p class="text-gray-900 font-extrabold text-lg mb-2">
          {{ errorCode === 403 ? 'Không có quyền truy cập' : 'Không tìm thấy bài học' }}
        </p>
        <p class="text-gray-500 text-xs sm:text-sm mb-6 leading-relaxed">
          {{ errorCode === 403
            ? 'Bài học này chỉ dành cho học sinh trong lớp học liên kết. Vui lòng liên hệ giáo viên để biết thêm chi tiết.'
            : 'Bài học không tồn tại, đã bị gỡ bỏ hoặc chuyển chế độ lưu trữ.' }}
        </p>
        <RouterLink to="/student/lessons"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#d63015] hover:bg-[#c02a10] text-white text-xs font-bold transition-all shadow-md shadow-red-500/10">
          ← Quay lại danh sách
        </RouterLink>
      </div>
    </div>

    <template v-else>
      <!-- ── Sticky Top Header ── -->
      <div class="sticky top-0 z-20 bg-white border-b border-gray-150/80 shadow-sm">
        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
          <!-- Left side: back link + subject badge -->
          <div class="flex items-center gap-3 min-w-0">
            <RouterLink to="/student/lessons"
              class="flex items-center gap-1.5 text-xs sm:text-sm font-bold text-gray-500 hover:text-[#d63015] transition-colors shrink-0 group">
              <svg class="w-4 h-4 transform group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
              </svg>
              <span class="hidden md:inline">Bài học</span>
            </RouterLink>

            <span class="hidden sm:inline-flex shrink-0 text-[10px] font-extrabold px-2.5 py-1 rounded-lg"
              :style="{ backgroundColor: (lesson.subject?.color || '#d63015') + '15', color: lesson.subject?.color || '#d63015' }">
              {{ lesson.subject?.name }}
            </span>

            <span class="text-gray-300 hidden sm:inline">|</span>

            <h2 class="text-sm font-bold text-gray-800 truncate" :title="lesson.title">
              {{ lesson.title }}
            </h2>
          </div>

          <!-- Right side: progress ring + complete check action -->
          <div class="shrink-0 flex items-center gap-3.5">
            <div class="flex items-center gap-2">
              <div class="relative w-7 h-7">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                  <circle cx="18" cy="18" r="15" fill="none" stroke="#f3f4f6" stroke-width="3.5"/>
                  <circle cx="18" cy="18" r="15" fill="none"
                    :stroke="isCompleted ? '#22c55e' : (lesson.subject?.color || '#d63015')"
                    stroke-width="3.5" stroke-linecap="round"
                    :stroke-dasharray="`${isCompleted ? 94.2 : 0} 94.2`"
                    class="transition-all duration-700"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-[9px] font-extrabold" :class="isCompleted ? 'text-green-600' : 'text-gray-650'">
                    {{ isCompleted ? '100' : '0' }}%
                  </span>
                </div>
              </div>
            </div>

            <button @click="markComplete" :disabled="isCompleted || marking"
              class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all disabled:opacity-85 disabled:cursor-not-allowed shadow-sm shadow-red-500/5 hover:scale-[1.01] cursor-pointer"
              :class="isCompleted
                ? 'bg-green-50 text-green-700 border border-green-200'
                : 'bg-[#d63015] hover:bg-[#c02a10] text-white'">
              <svg v-if="isCompleted" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
              <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span>{{ isCompleted ? 'Đã học xong' : (marking ? 'Đang lưu...' : 'Hoàn thành') }}</span>
            </button>
          </div>
        </div>

        <!-- Progress status line -->
        <div class="h-0.5 bg-gray-100">
          <div class="h-full transition-all duration-700"
            :style="{ width: isCompleted ? '100%' : '0%', backgroundColor: isCompleted ? '#22c55e' : (lesson.subject?.color || '#d63015') }"/>
        </div>
      </div>

      <!-- ── Interactive Study Body ── -->
      <div class="flex-1 w-full max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        <!-- Title block card -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-7 relative overflow-hidden">
          <div class="flex flex-wrap items-center gap-2 mb-3">
            <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-lg"
              :style="{ backgroundColor: (lesson.subject?.color || '#d63015') + '15', color: lesson.subject?.color || '#d63015' }">
              {{ lesson.subject?.name }}
            </span>
            <span class="text-xs text-gray-500 bg-gray-50 px-2 py-0.5 rounded-lg border border-gray-100">{{ lesson.classroom?.name }}</span>
            <span class="text-xs text-gray-400 bg-gray-50/50 px-2 py-0.5 rounded-lg">GV: {{ lesson.teacher?.name }}</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-extrabold text-gray-950">{{ lesson.title }}</h1>
          <p v-if="lesson.description" class="text-gray-500 text-xs sm:text-sm mt-2 leading-relaxed">{{ lesson.description }}</p>
        </div>

        <!-- Video player container -->
        <div v-if="lesson.video_path" class="rounded-3xl overflow-hidden bg-black shadow-lg border border-gray-200/50 relative group">
          <video
            ref="videoEl"
            :src="lesson.video_path"
            controls
            class="w-full aspect-video object-contain bg-black"
            @ended="onVideoEnd"
          />
        </div>

        <!-- Lesson Written Content with LaTeX -->
        <div v-if="lesson.content" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4.5 border-b border-gray-50 flex items-center gap-2">
            <span class="w-1.5 h-4.5 rounded-full bg-[#d63015]"/>
            <h3 class="font-extrabold text-gray-800 text-sm sm:text-base">Nội dung chi tiết</h3>
          </div>
          <div class="p-6 prose prose-red max-w-none text-gray-700 leading-relaxed text-sm sm:text-base" v-html="renderMath(lesson.content)"/>
        </div>

        <!-- Sibling interactive quizzes -->
        <div v-if="lesson.questions?.length" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between flex-wrap gap-2.5 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center gap-2">
              <span class="w-1.5 h-4.5 rounded-full bg-[#d63015]"/>
              <h3 class="font-extrabold text-gray-850 text-sm sm:text-base">Bài tập ôn tập nhanh</h3>
            </div>
            <span class="text-xs font-bold text-gray-400 bg-gray-100 px-3 py-1 rounded-full">
              {{ lesson.questions.length }} câu hỏi tự luyện
            </span>
          </div>

          <div class="p-6 space-y-8 divide-y divide-gray-100">
            <div v-for="(q, idx) in lesson.questions" :key="q.id" :class="{ 'pt-8': idx > 0 }" class="space-y-4">
              <!-- Question metadata -->
              <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-extrabold px-2.5 py-1 bg-gray-100 text-gray-700 rounded-lg">
                    Câu {{ idx + 1 }}
                  </span>
                  <span class="text-xs font-extrabold px-2.5 py-1 rounded-lg" :class="qTypeClass(q.type)">
                    {{ qTypeLabel(q.type) }}
                  </span>
                </div>
                <span class="text-xs font-medium text-gray-400">Độ khó: {{ qDifficultyLabel(q.difficulty) }}</span>
              </div>

              <!-- Question title string -->
              <div class="text-sm sm:text-base text-gray-800 font-bold leading-relaxed q-content pt-1" v-html="renderMath(q.content)"/>

              <!-- Audio player for audio quizzes -->
              <div v-if="q.type === 'listening' && q.audio_path" class="bg-gray-50 p-4 rounded-xl border border-gray-150/80 my-2">
                <audio :src="'/storage/' + q.audio_path" controls class="w-full h-9 rounded-lg"/>
              </div>

              <!-- Inputs/options according to questions types -->
              <div class="space-y-3.5 pl-0.5">
                <!-- Multiple Choice & True/False options -->
                <div v-if="['multiple_choice', 'true_false', 'listening'].includes(q.type)" class="grid gap-2">
                  <label v-for="(opt, oi) in q.options" :key="oi"
                    class="flex items-start gap-3.5 p-4 rounded-2xl border cursor-pointer transition-all hover:scale-[1.002] duration-200 text-xs sm:text-sm"
                    :class="getOptionClass(q.id, String(oi))">
                    <input type="radio" :name="`learn-q-${q.id}`" :value="String(oi)"
                      v-model="answers[q.id]"
                      :disabled="checkedQuestions[q.id]"
                      class="w-4.5 h-4.5 accent-[#d63015] shrink-0 mt-0.5" />
                    <span class="font-bold text-gray-400 w-4.5 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
                    <span class="text-gray-800 leading-relaxed flex-1" v-html="renderMath(optionText(opt))"></span>
                  </label>
                </div>

                <!-- Multiple select options -->
                <div v-else-if="q.type === 'multiple_select'" class="grid gap-2">
                  <label v-for="(opt, oi) in q.options" :key="oi"
                    class="flex items-start gap-3.5 p-4 rounded-2xl border cursor-pointer transition-all hover:scale-[1.002] duration-200 text-xs sm:text-sm"
                    :class="getMultiSelectOptionClass(q.id, String(oi))">
                    <input type="checkbox" :value="String(oi)"
                      :checked="(answers[q.id] || []).includes(String(oi))"
                      @change="togglePracticeMulti(q.id, String(oi))"
                      :disabled="checkedQuestions[q.id]"
                      class="w-4.5 h-4.5 accent-[#d63015] rounded shrink-0 mt-0.5" />
                    <span class="font-bold text-gray-400 w-4.5 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
                    <span class="text-gray-800 leading-relaxed flex-1" v-html="renderMath(optionText(opt))"></span>
                  </label>
                </div>

                <!-- Fill blank blanks -->
                <div v-else-if="q.type === 'fill_blank'" class="space-y-3">
                  <div v-for="(_, bi) in getFillBlanks(q)" :key="bi" class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-500 bg-gray-55 px-3 py-3 rounded-xl border border-gray-100 w-24 text-center shrink-0">
                      Chỗ [{{ bi + 1 }}]
                    </span>
                    <input :value="(answers[q.id] || [])[bi] || ''"
                      @input="setPracticeFillBlank(q.id, bi, $event.target.value)"
                      :disabled="checkedQuestions[q.id]"
                      class="flex-1 px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] transition-all"
                      :placeholder="`Nhập từ/cụm từ điền vào chỗ ${bi + 1}...`" />
                  </div>
                </div>

                <!-- Short answers input -->
                <div v-else-if="q.type === 'short_answer'" class="space-y-2">
                  <input v-model="answers[q.id]"
                    :disabled="checkedQuestions[q.id]"
                    class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] transition-all"
                    placeholder="Nhập đáp án trả lời của bạn..." />
                </div>

                <!-- Essay answers input -->
                <div v-else-if="q.type === 'essay'" class="space-y-2">
                  <textarea v-model="answers[q.id]"
                    :disabled="checkedQuestions[q.id]"
                    rows="4"
                    class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] transition-all resize-none"
                    placeholder="Viết nội dung tự luận chi tiết..."></textarea>
                </div>
              </div>

              <!-- Question evaluations feedback -->
              <div v-if="checkedQuestions[q.id]" class="mt-4 p-5 rounded-2xl border transition-all duration-350"
                :class="results[q.id] ? 'bg-green-50/50 border-green-200 text-green-800' : 'bg-red-50/50 border-red-200 text-red-800'">
                <div class="flex items-start gap-3">
                  <span class="text-xl leading-none shrink-0 mt-0.5">{{ results[q.id] ? '✅' : '❌' }}</span>
                  <div class="space-y-1.5 flex-1">
                    <p class="font-extrabold text-sm sm:text-base">
                      {{ results[q.id] ? 'Chính xác! Câu trả lời rất tốt.' : 'Chưa đúng rồi, hãy so sánh đáp án dưới đây.' }}
                    </p>
                    <p v-if="!results[q.id]" class="text-xs sm:text-sm text-red-750">
                      <span class="font-semibold">Đáp án đúng:</span> 
                      <span class="font-bold ml-1" v-html="renderMath(formatCorrectAnswerText(q))"></span>
                    </p>
                    <div v-if="q.explanation" class="mt-3 pt-3 border-t border-gray-200/50 text-xs sm:text-sm text-gray-650">
                      <span class="font-bold text-gray-700 block mb-1">Giải thích lời giải:</span>
                      <div class="leading-relaxed" v-html="renderMath(q.explanation)"/>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Single question button controls -->
              <div class="flex gap-2 justify-end pt-3">
                <button v-if="checkedQuestions[q.id]" type="button" @click="resetQuestion(q.id)"
                  class="px-5 py-2.5 border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-xl text-xs font-bold transition-all hover:border-gray-300 cursor-pointer">
                  Làm lại
                </button>
                <button v-else type="button" @click="checkAnswer(q)" :disabled="isAnswerEmpty(q)"
                  class="px-5 py-2.5 bg-[#d63015] hover:bg-[#c02a10] disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold shadow-md shadow-red-500/10 hover:shadow-lg transition-all cursor-pointer">
                  Kiểm tra câu này
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Attachments/materials section -->
        <div v-if="lesson.materials?.length" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
            <span class="w-1.5 h-4.5 rounded-full bg-amber-500"/>
            <h3 class="font-extrabold text-gray-800 text-sm sm:text-base">Tài liệu học kèm</h3>
          </div>
          <div class="p-5 space-y-2.5">
            <a v-for="m in lesson.materials" :key="m.id" :href="m.file_path" target="_blank"
              class="flex items-center gap-3.5 px-4 py-3.5 rounded-2xl border border-gray-100 hover:border-red-200 hover:bg-red-50/30 transition-all group">
              <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0 border border-red-100">
                <svg class="w-5 h-5 text-[#d63015]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-800 truncate group-hover:text-[#c02a10] transition-colors">
                  {{ m.title ?? m.file_name }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5 uppercase tracking-wider font-semibold">
                  {{ m.file_type ?? 'File' }} <span v-if="m.file_size"> · {{ formatSize(m.file_size) }}</span>
                </p>
              </div>
              <svg class="w-4 h-4 text-gray-300 group-hover:text-red-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Related exams matching same subject -->
        <div v-if="relatedExams.length" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4.5 border-b border-gray-50 flex items-center gap-2">
            <span class="w-1.5 h-4.5 rounded-full bg-green-500"/>
            <h3 class="font-extrabold text-gray-800 text-sm sm:text-base">Bài thi cùng môn học khuyên làm</h3>
          </div>
          <div class="p-5 grid sm:grid-cols-2 gap-3.5">
            <RouterLink v-for="e in relatedExams" :key="e.id" to="/student/exams"
              class="flex items-center gap-3.5 px-4 py-3.5 rounded-2xl border border-gray-100 hover:border-green-200 hover:bg-green-50/20 transition-all group">
              <div class="w-9.5 h-9.5 rounded-xl bg-green-50 border border-green-150 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-800 truncate group-hover:text-green-700 transition-colors">
                  {{ e.title }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ e.duration_minutes }} phút thi</p>
              </div>
            </RouterLink>
          </div>
        </div>

        <!-- Extra spacing at bottom -->
        <div class="h-6"/>
      </div>

      <!-- ── Bottom navigation sticky pagination controls ── -->
      <div class="bg-white border-t border-gray-150/80 shadow-[0_-2px_12px_rgba(0,0,0,0.04)] sticky bottom-0 z-10">
        <div class="max-w-4xl mx-auto px-5 py-4 flex items-center justify-between gap-4">
          <!-- Sibling Previous lesson -->
          <button v-if="prevLesson" @click="router.push(`/student/lessons/${prevLesson.id}/learn`)"
            class="flex items-center gap-2.5 px-3 py-2 rounded-xl border border-gray-200 hover:border-red-200 hover:bg-red-50/30 transition-all text-xs sm:text-sm text-gray-600 hover:text-[#c02a10] group max-w-xs min-w-0 cursor-pointer">
            <svg class="w-4 h-4 shrink-0 transform group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="truncate text-left hidden sm:block">
              <span class="block text-[9px] text-gray-400 font-bold uppercase tracking-wider leading-none">Bài học trước</span>
              <span class="font-bold leading-normal truncate block mt-0.5 max-w-[120px]">{{ prevLesson.title }}</span>
            </span>
          </button>
          <div v-else class="w-8 h-8"/>

          <!-- Middle action status check -->
          <button @click="markComplete" :disabled="isCompleted || marking"
            class="flex items-center justify-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold shadow-md shadow-red-500/10 transition-all hover:scale-[1.01] shrink-0 cursor-pointer disabled:opacity-90 disabled:cursor-not-allowed"
            :class="isCompleted
              ? 'bg-green-50 text-green-700 border border-green-200 shadow-green-500/5'
              : 'bg-[#d63015] hover:bg-[#c02a10] text-white'">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ isCompleted ? 'Đã hoàn thành bài học này' : (marking ? 'Đang lưu...' : 'Đánh dấu học xong bài này') }}</span>
          </button>

          <!-- Sibling Next lesson -->
          <button v-if="nextLesson" @click="router.push(`/student/lessons/${nextLesson.id}/learn`)"
            class="flex items-center gap-2.5 px-3 py-2 rounded-xl border border-gray-200 hover:border-red-200 hover:bg-red-50/30 transition-all text-xs sm:text-sm text-gray-600 hover:text-[#c02a10] group max-w-xs min-w-0 cursor-pointer">
            <span class="truncate text-right hidden sm:block">
              <span class="block text-[9px] text-gray-400 font-bold uppercase tracking-wider leading-none">Bài học kế tiếp</span>
              <span class="font-bold leading-normal truncate block mt-0.5 max-w-[120px]">{{ nextLesson.title }}</span>
            </span>
            <svg class="w-4 h-4 shrink-0 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
          <div v-else class="w-8 h-8"/>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@api/axios'
import { renderMath } from '@/utils/math'

const route = useRoute()
const router = useRouter()

const lesson = ref(null)
const loading = ref(true)
const errorCode = ref(null)
const isCompleted = ref(false)
const marking = ref(false)
const relatedExams = ref([])
const prevLesson = ref(null)
const nextLesson = ref(null)
const videoEl = ref(null)

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
  short_answer:    'bg-yellow-50 text-yellow-700 border border-yellow-150', essay: 'bg-gray-50 text-gray-655 border border-gray-150',
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
      : 'border-gray-250 bg-white hover:border-[#d63015]/40 hover:bg-gray-50 text-gray-700'
  }
  const isSelected = answers.value[qId] === oi
  const isCorrect = isCorrectOption(qId, oi)
  if (isCorrect) return 'border-green-500 bg-green-50/50 text-green-800 font-semibold'
  if (isSelected && !isCorrect) return 'border-red-500 bg-red-50/50 text-red-800 font-semibold'
  return 'border-gray-150 bg-white opacity-60 text-gray-405'
}

function getMultiSelectOptionClass(qId, oi) {
  const selectedArr = answers.value[qId] || []
  const isSelected = selectedArr.includes(oi)
  if (!checkedQuestions.value[qId]) {
    return isSelected
      ? 'border-[#d63015] bg-[#d63015]/5 text-[#d63015] font-semibold'
      : 'border-gray-250 bg-white hover:border-[#d63015]/40 hover:bg-gray-50 text-gray-700'
  }
  const isCorrect = isCorrectOption(qId, oi)
  if (isCorrect) return 'border-green-500 bg-green-50/50 text-green-800 font-semibold'
  if (isSelected && !isCorrect) return 'border-red-500 bg-red-50/50 text-red-800 font-semibold'
  return 'border-gray-150 bg-white opacity-60 text-gray-405'
}

async function markComplete() {
  if (isCompleted.value || marking.value) return
  marking.value = true
  try {
    await api.post(`/student/lessons/${route.params.id}/progress`, { completed: true })
    isCompleted.value = true
  } finally {
    marking.value = false
  }
}

function onVideoEnd() {
  if (!isCompleted.value) markComplete()
}

function formatSize(bytes) {
  if (!bytes) return ''
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1024 / 1024).toFixed(1) + ' MB'
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/student/lessons/${route.params.id}`)
    const payload = data.data ?? data
    lesson.value = payload.lesson ?? payload
    isCompleted.value = !!(payload.progress?.is_completed ?? payload.progress?.completed ?? false)
  } catch (err) {
    errorCode.value = err.response?.status ?? 0
    loading.value = false
    return
  }

  // Non-critical sibling lessons and related exams loads
  try {
    // Load siblings for prev/next buttons
    if (lesson.value.classroom_id) {
      try {
        const res = await api.get('/student/lessons', {
          params: { classroom_id: lesson.value.classroom_id, per_page: 200 },
        })
        const siblings = res.data.data?.data ?? res.data.data ?? []
        const idx = siblings.findIndex(l => l.id === lesson.value.id)
        if (idx > 0) prevLesson.value = siblings[idx - 1]
        if (idx >= 0 && idx < siblings.length - 1) nextLesson.value = siblings[idx + 1]
      } catch { /* ignored */ }
    }

    // Related exams from same subject
    try {
      const ex = await api.get('/student/exams', { params: { per_page: 10 } })
      const all = ex.data.data?.data ?? ex.data.data ?? []
      relatedExams.value = all
        .filter(e => e.subject_id === (lesson.value.subject?.id ?? lesson.value.subject_id))
        .slice(0, 4)
    } catch { /* optional */ }
  } finally {
    loading.value = false
  }
})
</script>
