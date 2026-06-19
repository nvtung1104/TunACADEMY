<template>
  <!-- Loading -->
  <div v-if="loading" class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="text-center">
      <div class="animate-spin w-7 h-7 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-3"/>
      <p class="text-gray-400 text-sm">Đang tải bài tập...</p>
    </div>
  </div>

  <!-- Error -->
  <div v-else-if="loadError" class="min-h-screen flex items-center justify-center p-4 bg-gray-100">
    <div class="text-center max-w-sm">
      <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      </div>
      <h2 class="text-lg font-bold text-gray-900 mb-1">Không thể tải bài tập</h2>
      <p class="text-sm text-gray-500 mb-4">{{ loadError }}</p>
      <button @click="$router.back()" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm font-medium hover:bg-[#c02a10]">
        Quay lại
      </button>
    </div>
  </div>

  <!-- Result screen (Already submitted) -->
  <div v-else-if="submission" class="min-h-screen bg-gray-50 flex flex-col font-sans text-gray-800 select-none">
    <!-- Clean, Premium Top Header -->
    <header class="h-16 bg-white border-b border-gray-200/85 px-6 flex items-center justify-between shrink-0 shadow-sm relative z-20">
      <div class="flex items-center gap-3 text-left">
        <div class="w-10 h-10 rounded-2xl bg-[#d63015]/10 flex items-center justify-center">
          <span class="text-xl">📊</span>
        </div>
        <div>
          <h1 class="font-black text-gray-900 text-base leading-tight">Kết quả bài tập</h1>
          <p class="text-[11px] text-gray-400 font-medium">{{ assignment?.title || 'Bài tập' }} · {{ assignment?.subject?.name || 'Tự do' }}</p>
        </div>
      </div>
      <button @click="$router.push('/student/assignments')" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all duration-200">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Thoát
      </button>
    </header>

    <!-- Content Area: Responsive Grid Layout -->
    <div class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 lg:p-8 grid grid-cols-1 lg:grid-cols-3 gap-6 overflow-y-auto">
      
      <!-- LEFT SECTION: Main Content Area (Toggled by Tabs) -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Segmented Tab Switcher (Only when questions exist) -->
        <div v-if="questions.length > 0" class="bg-gray-100/80 p-1.5 rounded-2xl flex gap-1 shadow-sm">
          <button @click="changeTab('solutions')" 
            :class="activeTab === 'solutions' ? 'bg-white text-[#d63015] shadow-sm font-black' : 'text-gray-500 hover:text-gray-800 font-semibold'"
            class="flex-1 py-3 text-center text-sm rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
            📝 Chi tiết bài làm
          </button>
          <button @click="changeTab('review')" 
            :class="activeTab === 'review' ? 'bg-white text-[#d63015] shadow-sm font-black' : 'text-gray-500 hover:text-gray-800 font-semibold'"
            class="flex-1 py-3 text-center text-sm rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
            ✨ Nhận xét
          </button>
        </div>

        <!-- TAB CONTENT 1: AI Comments / Nhận xét -->
        <div v-if="activeTab === 'review' && questions.length > 0" class="bg-white border border-gray-200/80 rounded-[28px] p-6 shadow-sm space-y-4 animate-fade-in">
          <div class="flex items-center gap-2.5 pb-2.5 border-b border-gray-100 text-left">
            <div class="w-8 h-8 rounded-xl bg-[#7c3aed] flex items-center justify-center shrink-0">
              <svg class="w-4.5 h-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z"/>
              </svg>
            </div>
            <h3 class="font-extrabold text-gray-900 text-sm">Nhận xét</h3>
          </div>

          <!-- AI Evaluation Content -->
          <div v-if="submission.ai_evaluation" class="space-y-5 text-left animate-fade-in">
            <!-- Competency comment -->
            <div v-if="submission.ai_evaluation.competency_comment" class="bg-red-50/60 border border-red-100 rounded-2xl p-4 md:p-5">
              <p class="text-[10px] font-black text-[#c02a10] uppercase tracking-wider mb-2">Nhận xét tổng quan</p>
              <p class="text-sm text-gray-700 leading-relaxed font-medium">{{ submission.ai_evaluation.competency_comment }}</p>
            </div>

            <!-- Mistake explanations / Wrong answers warning -->
            <div v-if="submission.ai_evaluation.mistake_explanations && submission.ai_evaluation.mistake_explanations.length > 0" class="bg-amber-50/60 border border-amber-100 rounded-2xl p-4 md:p-5">
              <p class="text-[10px] font-black text-amber-700 uppercase tracking-wider mb-2">Điểm cần cải thiện</p>
              <ul class="list-disc pl-5 text-sm text-gray-700 space-y-1 font-medium">
                <li v-for="(ex, ei) in submission.ai_evaluation.mistake_explanations" :key="ei" class="leading-relaxed">{{ ex }}</li>
              </ul>
            </div>

            <!-- Next study method -->
            <div v-if="submission.ai_evaluation.next_study_method" class="bg-blue-50/60 border border-blue-100 rounded-2xl p-4 md:p-5">
              <p class="text-[10px] font-black text-blue-600 uppercase tracking-wider mb-2">Phương pháp học tiếp theo</p>
              <p class="text-sm text-gray-700 leading-relaxed font-medium whitespace-pre-line">{{ submission.ai_evaluation.next_study_method }}</p>
            </div>

            <!-- Chi tiết các câu sai (Nhận xét) -->
            <div v-if="wrongAnswers && wrongAnswers.length > 0" class="space-y-3 pt-3 border-t border-gray-100">
              <p class="text-xs font-black text-amber-700 uppercase tracking-wide">Nhận xét chi tiết các câu sai</p>
              <div class="space-y-3">
                <div v-for="wa in wrongAnswers" :key="wa.question_id" class="p-4 rounded-2xl bg-amber-50/50 border border-amber-100/80 text-xs space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="font-extrabold text-amber-800">Câu {{ getQNumber(wa.question_id) }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-700">Sai</span>
                  </div>
                  <p class="text-gray-900 leading-relaxed font-semibold"><span class="font-bold text-gray-500">Nội dung:</span> <span v-html="renderMath(getQFullContent(wa.question_id))"></span></p>
                  <p class="text-gray-700"><span class="font-bold text-[#c02a10]">Nhận xét & Lỗi sai:</span> {{ wa.reason ?? wa }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="py-10 text-center">
            <p class="text-sm text-gray-500 font-medium leading-relaxed">Kết quả bài làm của bạn đã được ghi nhận thành công.</p>
            <p class="text-xs text-gray-400 mt-1">Nhấp vào tab "Chi tiết bài làm" để xem toàn bộ lời giải chi tiết cho từng câu hỏi.</p>
          </div>
        </div>

        <!-- TAB CONTENT 2: Detailed Solutions Review Panel / Chi tiết bài làm -->
        <div v-if="activeTab === 'solutions' && questions.length > 0" class="bg-white border border-gray-200/80 rounded-[28px] p-6 shadow-sm space-y-4 animate-fade-in">
          <div class="flex items-center justify-between pb-2.5 border-b border-gray-100 text-left">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-[#d63015]/10 flex items-center justify-center text-base">📝</div>
              <h3 class="text-sm font-extrabold text-gray-900">Chi tiết bài làm & Đáp án đúng</h3>
            </div>
            <span class="text-xs font-bold text-gray-400">Tổng số: {{ reviewedQuestions.length }} câu hỏi</span>
          </div>

          <div class="space-y-4 text-left">
            <div v-for="(q, idx) in reviewedQuestions" :key="q.id" 
              class="border rounded-2xl p-4 md:p-5 transition-all duration-300"
              :class="q.is_correct ? 'border-emerald-200 bg-emerald-50/[0.08]' : 'border-rose-200 bg-rose-50/[0.08]'">
              
              <!-- Card Header -->
              <div class="flex items-center justify-between gap-3 mb-3">
                <span class="px-3 py-1 rounded-full text-xs font-extrabold"
                  :class="q.is_correct ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                  Câu {{ idx + 1 }}
                </span>
                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold flex items-center gap-1"
                  :class="q.is_correct ? 'text-emerald-600' : 'text-rose-600'">
                  <svg v-if="q.is_correct" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                  <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                  {{ q.is_correct ? 'Đúng' : 'Sai' }}
                </span>
              </div>

              <!-- Question Content -->
              <div class="text-gray-900 text-sm leading-relaxed q-content mb-4 font-semibold" v-html="renderMath(q.content)"></div>

              <!-- Answers options list for Multiple Choice / Select / True-False -->
              <div v-if="['multiple_choice', 'multiple_select', 'true_false', 'listening'].includes(q.type)" class="space-y-2 mb-4">
                <div v-for="(opt, oi) in q.options" :key="oi"
                  class="flex items-center gap-3 p-3 rounded-xl border text-xs font-medium transition-all"
                  :class="getOptionBgClass(q, oi)">
                  <span class="w-6 h-6 rounded-full text-xs font-black flex items-center justify-center shrink-0"
                    :class="getOptionCircleClass(q, oi)">
                    {{ String.fromCharCode(65 + oi) }}
                  </span>
                  <span class="flex-1 leading-relaxed text-left" v-html="renderMath(optionText(opt))"></span>
                  
                  <!-- Icons on the right -->
                  <svg v-if="isCorrectOption(q, oi) && (!q.is_correct || isStudentSelectedOption(q, oi))" class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                  <svg v-else-if="isStudentSelectedOption(q, oi) && !q.is_correct" class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </div>
              </div>

              <!-- Display Choice Info for all questions -->
              <div class="text-xs font-semibold py-2.5 px-4 rounded-xl bg-gray-50 flex items-center justify-between gap-3 border border-gray-100 mb-4">
                <span class="text-gray-500">
                  Bạn đã chọn: <strong :class="q.is_correct ? 'text-emerald-600' : 'text-rose-600'">{{ formatStudentAnswer(q) }}</strong>
                </span>
                <span class="text-gray-500">
                  Đáp án đúng: <strong class="text-emerald-600">{{ formatCorrectAnswer(q.correct_answer, q) }}</strong>
                </span>
              </div>

              <!-- Detailed Explanation (KaTeX) -->
              <div v-if="!q.is_correct && q.explanation" class="border-t border-gray-100 pt-3.5 mt-3.5 space-y-1.5">
                <p class="text-[10px] font-black text-indigo-600 uppercase tracking-wider flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  Lời giải chi tiết
                </p>
                <div class="text-sm text-gray-700 leading-relaxed explanation-content bg-slate-50/50 p-4 rounded-xl border border-slate-100" v-html="renderMath(q.explanation)"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Essay text content display (When no questions exist) -->
        <div v-else class="bg-white border border-gray-200/80 rounded-[28px] p-6 shadow-sm space-y-4 animate-fade-in text-left">
          <h3 class="text-sm font-extrabold text-gray-900 border-b border-gray-100 pb-2.5">Bài làm tự luận của bạn</h3>
          <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm text-gray-700 leading-relaxed whitespace-pre-line">
            {{ submission.content || '—' }}
          </div>
          
          <!-- Submitted files download -->
          <div v-if="submission.file_paths && submission.file_paths.length > 0" class="space-y-2 pt-4">
            <h4 class="text-xs font-bold text-gray-500">File đã nộp:</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <a v-for="(path, pi) in submission.file_paths" :key="pi" 
                :href="'/storage/' + path" target="_blank"
                class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 transition-colors text-xs font-semibold text-gray-700 truncate">
                <span class="text-base">📎</span>
                <span class="truncate flex-1">{{ path.split('/').pop() }}</span>
              </a>
            </div>
          </div>
        </div>

      </div>

      <!-- RIGHT SECTION: Statistics & Actions Sidebar (Occupies 1 column) -->
      <div class="lg:col-span-1 space-y-6">
        
        <!-- Score Card -->
        <div class="bg-white border border-gray-200/80 rounded-[28px] p-6 shadow-sm text-center space-y-5 sticky top-6">
          <h3 class="font-black text-gray-900 text-base border-b border-gray-100 pb-3">Kết quả chung</h3>

          <!-- Polished Small Score Badge -->
          <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl p-4 flex items-center justify-between text-left">
            <div>
              <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Điểm số</p>
              <p class="text-3xl font-black text-emerald-700 mt-1">
                {{ submission.score != null ? submission.score : '—' }} 
                <span class="text-sm font-normal text-emerald-600">/ 10</span>
              </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-xl text-emerald-600">
              🎯
            </div>
          </div>

          <!-- Basic Statistics (Only when questions exist) -->
          <div v-if="questions.length > 0 && submission.score != null" class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-2xl border border-gray-100">
            <div class="text-center">
              <p class="text-xl font-extrabold text-gray-900">
                {{ questions.filter(q => (submission.ai_evaluation?.detail?.[q.id]?.is_correct ?? submission.ai_evaluation?.wrong_answers?.every(wa => String(wa.question_id) !== String(q.id)) ?? false)).length }}
              </p>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">Câu đúng</p>
            </div>
            <div class="text-center border-l border-gray-200">
              <p class="text-xl font-extrabold text-gray-900">
                {{ questions.filter(q => !(submission.ai_evaluation?.detail?.[q.id]?.is_correct ?? submission.ai_evaluation?.wrong_answers?.every(wa => String(wa.question_id) !== String(q.id)) ?? false)).length }}
              </p>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">Câu sai / bỏ</p>
            </div>
          </div>

          <!-- Additional Statistics -->
          <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 space-y-2.5 text-left text-xs font-semibold text-gray-600">
            <div class="flex justify-between items-center">
              <span>Số lần đã làm bài:</span>
              <span class="text-gray-900 font-extrabold text-sm bg-gray-200/60 px-2.5 py-0.5 rounded-md">{{ attemptsCount }} lần</span>
            </div>
            <div class="flex justify-between items-center border-t border-gray-200/65 pt-2">
              <span>Số lần thoát tab:</span>
              <span class="text-gray-900 font-extrabold text-sm bg-gray-200/60 px-2.5 py-0.5 rounded-md text-amber-600">{{ tabExitsCount }} lần</span>
            </div>
          </div>

          <!-- Main Actions -->
          <div class="flex flex-col gap-2.5 pt-2">
            <button @click="$router.push('/student/assignments')"
              class="w-full px-4 py-3.5 rounded-2xl bg-[#d63015] hover:bg-[#c02a10] text-white text-sm font-bold shadow-md shadow-red-500/10 hover:scale-[1.02] active:scale-95 transition-all duration-200">
              Về danh sách bài tập
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- ── 3-panel doing interface ── -->
  <div v-else class="h-screen bg-gray-100 flex overflow-hidden select-none">

    <!-- ── LEFT SIDEBAR ── -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col shrink-0">
      <div class="flex-1 overflow-y-auto">
        <!-- Assignment info -->
        <div class="p-5 border-b border-gray-100">
          <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 rounded-lg bg-[#d63015]/10 flex items-center justify-center shrink-0">
              <svg class="w-4 h-4 text-[#d63015]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
            <span class="text-xs font-semibold text-[#d63015] uppercase tracking-wide">Bài tập</span>
          </div>
          <h1 class="font-bold text-gray-900 text-sm leading-snug mb-1">{{ assignment?.title }}</h1>
          <p class="text-xs text-gray-400">{{ assignment?.subject?.name }}</p>
          <span v-if="isOverdue" class="inline-block mt-2 text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-600">Quá hạn</span>
        </div>

        <!-- Deadline -->
        <div v-if="assignment?.deadline" class="px-5 py-4 border-b border-gray-100">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Hạn nộp</p>
          <p class="text-sm text-gray-700">{{ formatDate(assignment.deadline) }}</p>
        </div>

        <!-- Progress (question-based) -->
        <div v-if="questions.length > 0" class="px-5 py-4 border-b border-gray-100">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Tiến độ</p>
          <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-2">
            <div class="h-full bg-[#d63015] rounded-full transition-all duration-500"
              :style="{ width: (answeredCount / questions.length * 100) + '%' }"/>
          </div>
          <p class="text-xs text-gray-500">
            <strong class="text-gray-800">{{ answeredCount }}</strong>/{{ questions.length }} câu đã trả lời
          </p>
        </div>

        <!-- Attempts & Violations -->
        <div class="px-5 py-4 border-b border-gray-100 space-y-3 text-xs font-semibold text-gray-500">
          <div class="flex justify-between items-center">
            <span>Số lần đã làm bài:</span>
            <span class="text-gray-800 font-extrabold bg-gray-100 px-2.5 py-0.5 rounded">{{ attemptsCount }} lần</span>
          </div>
          <div class="flex justify-between items-center border-t border-gray-100/60 pt-2.5">
            <span>Số lần thoát tab:</span>
            <span class="text-amber-600 font-extrabold bg-amber-50 px-2.5 py-0.5 rounded">{{ tabExitsCount }} lần</span>
          </div>
        </div>

        <!-- Description -->
        <div v-if="assignment?.description" class="px-5 py-4 border-b border-gray-100">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Hướng dẫn</p>
          <p class="text-xs text-gray-600 leading-relaxed">{{ assignment.description }}</p>
        </div>
      </div>

      <!-- Back button -->
      <div class="p-4 border-t border-gray-100">
        <button @click="$router.back()"
          class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
          Trở về
        </button>
      </div>
    </aside>

    <!-- ── CENTER PANEL ── -->
    <main class="flex-1 flex flex-col overflow-hidden">

      <!-- Question types (question-based assignment) -->
      <template v-if="questions.length > 0">
        <!-- Question header bar -->
        <div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-3">
            <span class="font-bold text-gray-900">Câu {{ currentIndex + 1 }}</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-md" :class="qTypeClass(currentQ.type)">
              {{ qTypeLabel(currentQ.type) }}
            </span>
          </div>
          <span v-if="currentQ.points" class="text-sm font-semibold text-[#d63015]">{{ currentQ.points }} điểm</span>
        </div>

        <!-- Question body -->
        <div class="flex-1 overflow-y-auto p-6">
          <div class="max-w-2xl mx-auto">
            <div class="text-gray-900 leading-relaxed mb-6 q-content font-medium"
              v-html="renderMath(currentQ.content)"></div>

            <!-- Image media display -->
            <div v-if="currentQ.media_type === 'image' && currentQ.media_path" class="mb-5 text-center">
              <img :src="'/storage/' + currentQ.media_path" class="max-h-80 object-contain rounded-2xl border border-gray-200 mx-auto shadow-sm" />
            </div>

            <!-- Audio -->
            <div v-if="currentQ.audio_path" class="mb-5">
              <audio :src="'/storage/' + currentQ.audio_path" controls class="w-full rounded-xl h-10"/>
            </div>

            <!-- Multiple choice / True-False / Listening -->
            <div v-if="['multiple_choice','true_false','listening'].includes(currentQ.type)" class="space-y-3">
              <label v-for="(opt, oi) in currentQ.options" :key="oi"
                class="flex items-center gap-4 p-4 rounded-xl border cursor-pointer transition-all"
                :class="answers[currentQ.id] === String(oi)
                  ? 'border-[#d63015] bg-[#d63015]/5'
                  : 'border-gray-200 bg-white hover:border-[#d63015]/40 hover:bg-gray-50'">
                <input type="radio" :name="`q${currentQ.id}`" :value="String(oi)" v-model="answers[currentQ.id]"
                  class="w-4 h-4 accent-[#d63015] shrink-0" />
                <span class="text-sm font-semibold text-gray-400 w-5 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
                <span class="text-sm text-gray-700 leading-relaxed" v-html="renderMath(optionText(opt))"></span>
              </label>
            </div>

            <!-- Multiple select -->
            <div v-else-if="currentQ.type === 'multiple_select'" class="space-y-3">
              <label v-for="(opt, oi) in currentQ.options" :key="oi"
                class="flex items-center gap-4 p-4 rounded-xl border cursor-pointer transition-all"
                :class="(answers[currentQ.id] || []).includes(String(oi))
                  ? 'border-[#d63015] bg-[#d63015]/5'
                  : 'border-gray-200 bg-white hover:border-[#d63015]/40 hover:bg-gray-50'">
                <input type="checkbox" :value="String(oi)"
                  :checked="(answers[currentQ.id] || []).includes(String(oi))"
                  @change="toggleMulti(currentQ.id, String(oi))"
                  class="w-4 h-4 accent-[#d63015] rounded shrink-0" />
                <span class="text-sm font-semibold text-gray-400 w-5 shrink-0">{{ String.fromCharCode(65 + oi) }}.</span>
                <span class="text-sm text-gray-700 leading-relaxed" v-html="renderMath(optionText(opt))"></span>
              </label>
            </div>

            <!-- Fill blank -->
            <div v-else-if="currentQ.type === 'fill_blank'" class="space-y-3">
              <div v-for="(_, bi) in fillBlanks(currentQ)" :key="bi" class="flex items-center gap-3">
                <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-3 py-2.5 rounded-lg w-24 text-center shrink-0">
                  Chỗ [{{ bi + 1 }}]
                </span>
                <input :value="(answers[currentQ.id] || [])[bi] || ''"
                  @input="setFillBlank(currentQ.id, bi, $event.target.value)"
                  class="flex-1 px-4 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] transition-colors"
                  :placeholder="`Điền vào chỗ trống ${bi + 1}`" />
              </div>
            </div>

            <!-- Ordering -->
            <div v-else-if="currentQ.type === 'ordering'" class="space-y-2">
              <p class="text-xs text-gray-400 mb-3">Dùng nút ↑↓ để sắp xếp theo thứ tự đúng.</p>
              <div v-for="(itemIdx, pos) in (answers[currentQ.id] || currentQ.options?.map((_,i) => String(i)))" :key="pos"
                class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-white">
                <span class="w-6 text-xs font-bold text-gray-400 shrink-0">{{ pos + 1 }}.</span>
                <span class="flex-1 text-sm text-gray-800" v-html="renderMath(optionText(currentQ.options?.[Number(itemIdx)]))"></span>
                <div class="flex gap-1 shrink-0">
                  <button type="button" @click="moveOrder(currentQ, pos, -1)" :disabled="pos === 0"
                    class="p-1.5 rounded-lg hover:bg-gray-100 disabled:opacity-30 text-gray-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                  </button>
                  <button type="button" @click="moveOrder(currentQ, pos, 1)"
                    :disabled="pos === (currentQ.options?.length ?? 0) - 1"
                    class="p-1.5 rounded-lg hover:bg-gray-100 disabled:opacity-30 text-gray-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Matching -->
            <div v-else-if="currentQ.type === 'matching'" class="space-y-3">
              <div v-for="(left, li) in currentQ.options?.left ?? []" :key="li"
                class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-white">
                <span class="flex-1 text-sm text-gray-800" v-html="renderMath(optionText(left))"></span>
                <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
                <select :value="(answers[currentQ.id] || {})[String(li)] ?? ''"
                  @change="setMatch(currentQ.id, li, $event.target.value)"
                  class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] bg-white">
                  <option value="">-- Chọn --</option>
                  <option v-for="(right, ri) in currentQ.options?.right ?? []" :key="ri" :value="String(ri)">{{ optionText(right) }}</option>
                </select>
              </div>
            </div>

            <!-- Calculation -->
            <div v-else-if="currentQ.type === 'calculation'">
              <p class="text-xs text-gray-400 mb-3">
                Nhập kết quả số
                <template v-if="currentQ.correct_answer?.tolerance"> (sai số ±{{ currentQ.correct_answer.tolerance }})</template>
                <template v-if="currentQ.correct_answer?.unit"> · đơn vị: {{ currentQ.correct_answer.unit }}</template>
              </p>
              <input v-model="answers[currentQ.id]" type="number" step="any"
                class="w-full max-w-xs px-4 py-2.5 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015]"
                placeholder="Nhập kết quả..." />
            </div>

            <!-- Essay / Short answer / Writing / Speaking -->
            <div v-else-if="['essay','short_answer','writing','speaking'].includes(currentQ.type)">
              <textarea v-model="answers[currentQ.id]" rows="7"
                class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] resize-none transition-colors"
                placeholder="Nhập câu trả lời của bạn..." />
            </div>
          </div>
        </div>

        <!-- Bottom navigation -->
        <div class="bg-white border-t border-gray-200 px-6 py-3 flex items-center justify-between shrink-0">
          <button @click="currentIndex = Math.max(0, currentIndex - 1)" :disabled="currentIndex === 0"
            class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 disabled:opacity-30 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Trước
          </button>
          <span class="text-sm text-gray-400 tabular-nums">{{ currentIndex + 1 }} / {{ questions.length }}</span>
          <button @click="currentIndex = Math.min(questions.length - 1, currentIndex + 1)"
            :disabled="currentIndex === questions.length - 1"
            class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 disabled:opacity-30 hover:bg-gray-50 transition-colors">
            Sau
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </template>

      <!-- Essay / upload (no questions) -->
      <template v-else>
        <div class="bg-white border-b border-gray-200 px-6 py-3 shrink-0">
          <h2 class="font-bold text-gray-900">{{ assignment?.title }}</h2>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
          <div class="max-w-2xl mx-auto space-y-5">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Bài làm của bạn</label>
              <textarea v-model="textContent" rows="12"
                class="w-full px-4 py-3 text-sm rounded-2xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] resize-none"
                placeholder="Nhập bài làm của bạn tại đây..." />
            </div>

            <!-- Upload Zone -->
            <div v-if="assignment?.type === 'upload'" class="space-y-4 pt-2">
              <label class="block text-sm font-bold text-gray-700">Tệp đính kèm bài làm</label>
              <div @click="fileInput.click()" @dragover.prevent @drop.prevent="onDrop"
                class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center cursor-pointer hover:border-[#d63015] hover:bg-red-50/20 transition-all duration-300">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <p class="text-sm text-gray-500 font-semibold">Kéo thả file hoặc <span class="text-[#d63015] hover:underline">chọn tệp từ máy tính</span></p>
                <p class="text-xs text-gray-400 mt-1.5">Hỗ trợ PDF, Word, Ảnh, Audio, Zip (Tối đa 10MB)</p>
              </div>
              <input ref="fileInput" type="file" multiple class="hidden" @change="onFileChange" />

              <!-- Render selected files -->
              <div v-if="uploadFiles.length > 0" class="space-y-2">
                <div v-for="(f, i) in uploadFiles" :key="i"
                  class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200">
                  <span class="text-lg">📄</span>
                  <span class="flex-1 text-xs text-gray-700 font-semibold truncate">{{ f.name }}</span>
                  <span class="text-xs text-gray-400 shrink-0">{{ (f.size / 1024).toFixed(0) }} KB</span>
                  <button @click="uploadFiles.splice(i, 1)" class="p-1 rounded-lg hover:bg-red-100 text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-white border-t border-gray-200 px-6 py-3 flex justify-end shrink-0">
          <button @click="confirmSubmit" :disabled="submitting"
            class="px-6 py-2.5 rounded-xl bg-[#d63015] text-white text-sm font-bold hover:bg-[#c02a10] disabled:opacity-60 transition-all duration-200">
            {{ submitting ? 'Đang nộp...' : 'Nộp bài' }}
          </button>
        </div>
      </template>
    </main>

    <!-- ── RIGHT SIDEBAR ── -->
    <aside v-if="questions.length > 0" class="w-52 bg-white border-l border-gray-200 flex flex-col shrink-0">
      <div class="px-4 py-3 border-b border-gray-100">
        <p class="font-bold text-gray-900 text-sm">Mục lục câu hỏi</p>
      </div>

      <div class="flex-1 overflow-y-auto p-3">
        <div class="grid grid-cols-5 gap-1.5">
          <button v-for="(q, idx) in questions" :key="q.id"
            @click="currentIndex = idx"
            class="aspect-square rounded-lg text-xs font-bold transition-all"
            :class="idx === currentIndex
              ? 'bg-[#d63015] text-white shadow-sm'
              : isAnswered(q)
                ? 'bg-green-100 text-green-700 border border-green-200 hover:bg-green-200'
                : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
            {{ idx + 1 }}
          </button>
        </div>
      </div>

      <!-- Legend + Submit -->
      <div class="p-4 border-t border-gray-100 space-y-3">
        <div class="space-y-1.5">
          <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 rounded bg-[#d63015] shrink-0"/>
            <span class="text-xs text-gray-500">Đang xem</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 rounded bg-green-100 border border-green-200 shrink-0"/>
            <span class="text-xs text-gray-500">Đã trả lời</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 rounded bg-gray-100 shrink-0"/>
            <span class="text-xs text-gray-500">Chưa trả lời</span>
          </div>
        </div>
        <button @click="confirmSubmit" :disabled="submitting"
          class="w-full px-4 py-2.5 rounded-xl bg-[#d63015] text-white text-sm font-bold hover:bg-[#c02a10] disabled:opacity-60 transition-colors">
          {{ submitting ? 'Đang nộp...' : 'Nộp bài' }}
        </button>
      </div>
    </aside>
  </div>

  <!-- Confirm modal -->
  <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full">
      <h3 class="font-bold text-gray-900 text-lg mb-2">Xác nhận nộp bài</h3>
      <template v-if="questions.length > 0">
        <p class="text-sm text-gray-500 mb-1">
          Bạn đã trả lời <strong class="text-gray-900">{{ answeredCount }}/{{ questions.length }}</strong> câu hỏi.
        </p>
        <p v-if="answeredCount < questions.length" class="text-sm text-amber-600 font-medium mb-4">
          Còn {{ questions.length - answeredCount }} câu chưa trả lời. Bạn có chắc muốn nộp?
        </p>
        <p v-else class="text-sm text-green-600 font-medium mb-4">Bạn đã hoàn thành tất cả câu hỏi.</p>
      </template>
      <p v-else class="text-sm text-gray-500 mb-4">Sau khi nộp bạn sẽ không thể chỉnh sửa. Xác nhận nộp bài?</p>
      <div class="flex gap-3">
        <button @click="showConfirm = false"
          class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
          Huỷ
        </button>
        <button @click="doSubmit"
          class="flex-1 px-4 py-2.5 rounded-xl bg-[#d63015] text-white text-sm font-bold hover:bg-[#c02a10]">
          Nộp bài
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@api/axios'
import { useToastStore } from '@stores/toast'
import { renderMath } from '@/utils/math'

const route        = useRoute()
const router       = useRouter()
const assignmentId = route.params.id
const toast        = useToastStore()

const assignment  = ref(null)
const questions   = ref([])
const submission  = ref(null)
const answers     = reactive({})
const textContent = ref('')
const uploadFiles = ref([])
const fileInput   = ref(null)

const loading     = ref(true)
const loadError   = ref('')
const submitting  = ref(false)
const showConfirm = ref(false)
const currentIndex = ref(0)

const activeTab   = ref(localStorage.getItem(`student_assignment_active_tab_${assignmentId}`) || 'solutions')
const attemptsCount = ref(0)
const tabExitsCount = ref(0)

function changeTab(tab) {
  activeTab.value = tab
  localStorage.setItem(`student_assignment_active_tab_${assignmentId}`, tab)
}

function onVisibilityChange() {
  if (document.visibilityState === 'hidden' && !submission.value && !loading.value) {
    tabExitsCount.value++
    localStorage.setItem(`student_assignment_tab_exits_${assignmentId}`, String(tabExitsCount.value))
  }
}

const wrongAnswers = computed(() => submission.value?.ai_evaluation?.wrong_answers ?? [])
const mistakeExplanations = computed(() => submission.value?.ai_evaluation?.mistake_explanations ?? [])

const getQNumber = (qId) => {
  const idx = questions.value.findIndex(q => String(q.id) === String(qId))
  return idx !== -1 ? idx + 1 : '—'
}

const getQFullContent = (qId) => {
  const q = questions.value.find(q => String(q.id) === String(qId))
  return q ? q.content : ''
}

function getOptionBgClass(q, oi) {
  const isSelected = isStudentSelectedOption(q, oi)
  const isCorrect = isCorrectOption(q, oi)

  if (q.is_correct) {
    if (isSelected) return 'border-emerald-300 bg-emerald-50 text-emerald-800'
    return 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
  } else {
    if (isCorrect) return 'border-emerald-300 bg-emerald-50 text-emerald-800'
    if (isSelected) return 'border-rose-300 bg-rose-50 text-rose-800'
    return 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
  }
}

function getOptionCircleClass(q, oi) {
  const isSelected = isStudentSelectedOption(q, oi)
  const isCorrect = isCorrectOption(q, oi)

  if (q.is_correct) {
    if (isSelected) return 'bg-emerald-500 text-white'
    return 'bg-gray-100 text-gray-500'
  } else {
    if (isCorrect) return 'bg-emerald-500 text-white'
    if (isSelected) return 'bg-rose-500 text-white'
    return 'bg-gray-100 text-gray-500'
  }
}

function preventCopy(e) {
  e.preventDefault()
  return false
}

function preventShortcuts(e) {
  if (
    (e.ctrlKey && (e.key === 'c' || e.key === 'C' || e.key === 'a' || e.key === 'A' || e.key === 'u' || e.key === 'U' || e.key === 'x' || e.key === 'X')) ||
    e.key === 'F12'
  ) {
    e.preventDefault()
    return false
  }
}

const reviewedQuestions = computed(() => {
  if (!submission.value) return []
  return questions.value.map((q) => {
    // Check if correct using the grading detail or evaluation detail
    const isCorrect = submission.value.ai_evaluation?.detail?.[q.id]?.is_correct 
      ?? (submission.value.ai_evaluation?.wrong_answers?.every(wa => String(wa.question_id) !== String(q.id)) ?? true)
    
    return {
      ...q,
      is_correct: isCorrect
    }
  })
})

const submittedAnswers = computed(() => {
  if (!submission.value || !submission.value.content) return {}
  try {
    return JSON.parse(submission.value.content)
  } catch (e) {
    return {}
  }
})

function isStudentSelectedOption(q, oi) {
  const ansList = submission.value ? submittedAnswers.value : answers
  const ans = ansList[q.id]
  if (ans === undefined || ans === null || ans === '') return false
  if (q.type === 'multiple_select') {
    return Array.isArray(ans) ? ans.includes(String(oi)) : String(ans) === String(oi)
  }
  return String(ans) === String(oi)
}

function isCorrectOption(q, oi) {
  const correct = q.correct_answer
  if (correct === null || correct === undefined) return false
  if (Array.isArray(correct)) {
    return correct.includes(String(oi))
  }
  return String(correct) === String(oi)
}

function formatStudentAnswer(q) {
  const ansList = submission.value ? submittedAnswers.value : answers
  const ans = ansList[q.id]
  if (ans === undefined || ans === null || ans === '') return '—'
  if (q.type === 'true_false') {
    return ans === 'T' ? 'Đúng' : 'Sai'
  }
  if (Array.isArray(ans)) {
    if (q.type === 'fill_blank') return ans.map((v, i) => `[Chỗ ${i+1}]: ${v || '—'}`).join(', ')
    return ans.map(i => {
      const optVal = q.options?.[Number(i)]
      const label = String.fromCharCode(65 + Number(i))
      return optVal ? `${label}. ${optionText(optVal)}` : i
    }).join(', ')
  }
  if (typeof ans === 'object') {
    if (q.type === 'matching') {
      return Object.entries(ans).map(([k, v]) => {
        const leftText = optionText(q.options?.left?.[Number(k)]) ?? `Mục ${Number(k)+1}`
        const rightText = optionText(q.options?.right?.[Number(v)]) ?? `Đáp án ${Number(v)+1}`
        return `${leftText} → ${rightText}`
      }).join('; ')
    }
    return JSON.stringify(ans)
  }
  const num = Number(ans)
  if (!isNaN(num) && q.options && q.options[num] !== undefined) {
    const label = String.fromCharCode(65 + num)
    return `${label}. ${optionText(q.options[num])}`
  }
  return String(ans)
}

function formatCorrectAnswer(correct, q) {
  if (correct === null || correct === undefined) return '—'
  if (q.type === 'true_false') {
    const val = Array.isArray(correct) ? correct[0] : correct
    return String(val) === '0' || String(val) === 'T' ? 'Đúng' : 'Sai'
  }
  if (Array.isArray(correct)) {
    if (q.type === 'fill_blank') return correct.map((v, i) => `[Chỗ ${i+1}]: ${v}`).join(', ')
    return correct.map(i => {
      const optVal = q.options?.[Number(i)]
      const label = String.fromCharCode(65 + Number(i))
      return optVal ? `${label}. ${optionText(optVal)}` : i
    }).join(', ')
  }
  if (typeof correct === 'object') {
    if (q.type === 'matching') {
      return Object.entries(correct).map(([k, v]) => {
        const leftText = optionText(q.options?.left?.[Number(k)]) ?? `Mục ${Number(k)+1}`
        const rightText = optionText(q.options?.right?.[Number(v)]) ?? `Đáp án ${Number(v)+1}`
        return `${leftText} → ${rightText}`
      }).join('; ')
    }
    return JSON.stringify(correct)
  }
  const num = Number(correct)
  if (!isNaN(num) && q.options && q.options[num] !== undefined) {
    const label = String.fromCharCode(65 + num)
    return `${label}. ${optionText(q.options[num])}`
  }
  return String(correct)
}

const currentQ      = computed(() => questions.value[currentIndex.value] ?? {})
const answeredCount = computed(() => questions.value.filter(q => isAnswered(q)).length)
const isOverdue     = computed(() => assignment.value?.deadline && new Date(assignment.value.deadline) < new Date())

const draftKey = `assignment_draft_${assignmentId}`

function saveDraft() {
  if (submission.value || !assignment.value) return
  localStorage.setItem(draftKey, JSON.stringify({
    answers: JSON.parse(JSON.stringify(answers)),
    textContent: textContent.value
  }))
}

function clearDraft() { localStorage.removeItem(draftKey) }
function onVisibilityHide() { if (document.hidden) saveDraft() }

onUnmounted(() => {
  window.removeEventListener('beforeunload', saveDraft)
  document.removeEventListener('visibilitychange', onVisibilityHide)
  document.removeEventListener('contextmenu', preventCopy)
  document.removeEventListener('copy', preventCopy)
  document.removeEventListener('cut', preventCopy)
  document.removeEventListener('keydown', preventShortcuts)
  document.removeEventListener('visibilitychange', onVisibilityChange)
})

onMounted(async () => {
  document.addEventListener('contextmenu', preventCopy)
  document.addEventListener('copy', preventCopy)
  document.addEventListener('cut', preventCopy)
  document.addEventListener('keydown', preventShortcuts)
  document.addEventListener('visibilitychange', onVisibilityChange)

  const countKey = `student_assignment_attempts_count_${assignmentId}`
  attemptsCount.value = Number(localStorage.getItem(countKey) || 0)
  tabExitsCount.value = Number(localStorage.getItem(`student_assignment_tab_exits_${assignmentId}`) || 0)

  try {
    const { data } = await api.get(`/student/assignments/${assignmentId}`)
    assignment.value = data.data?.assignment ?? data.data
    submission.value = data.data?.submission ?? null
    questions.value  = assignment.value?.questions ?? []

    questions.value.forEach(q => {
      if (q.type === 'ordering' && Array.isArray(q.options))
        answers[q.id] = q.options.map((_, i) => String(i))
    })

    // If already submitted, stop drafting
    if (submission.value) {
      loading.value = false
      return
    }

    const draftRaw = localStorage.getItem(draftKey)
    if (draftRaw) {
      try {
        const draft = JSON.parse(draftRaw)
        if (draft.answers) Object.assign(answers, draft.answers)
        if (draft.textContent) textContent.value = draft.textContent
      } catch {}
    }

    window.addEventListener('beforeunload', saveDraft)
    document.addEventListener('visibilitychange', onVisibilityHide)
  } catch (e) {
    loadError.value = e.response?.data?.message ?? 'Không thể tải bài tập'
  } finally {
    loading.value = false
  }
})

function isAnswered(q) {
  const a = answers[q.id]
  if (a === undefined || a === null || a === '') return false
  if (Array.isArray(a)) return a.some(v => v !== '' && v !== undefined && v !== null)
  if (typeof a === 'object') return Object.keys(a).length > 0
  return true
}

function toggleMulti(qId, val) {
  if (!answers[qId]) answers[qId] = []
  const idx = answers[qId].indexOf(val)
  if (idx === -1) answers[qId].push(val)
  else answers[qId].splice(idx, 1)
}

function fillBlanks(q) {
  const matches = (q.content || '').match(/___/g)
  return Array(matches ? matches.length : 1).fill(null)
}

function setFillBlank(qId, idx, val) {
  if (!Array.isArray(answers[qId])) answers[qId] = []
  answers[qId][idx] = val
}

function setMatch(qId, leftIdx, rightVal) {
  if (typeof answers[qId] !== 'object' || Array.isArray(answers[qId])) answers[qId] = {}
  answers[qId][String(leftIdx)] = rightVal
}

function moveOrder(q, pos, dir) {
  if (!answers[q.id]) answers[q.id] = q.options.map((_, i) => String(i))
  const arr = [...answers[q.id]]
  const newPos = pos + dir
  if (newPos < 0 || newPos >= arr.length) return
  ;[arr[pos], arr[newPos]] = [arr[newPos], arr[pos]]
  answers[q.id] = arr
}

// File select & drag drop helpers
function onFileChange(e) {
  for (const f of e.target.files) {
    if (!uploadFiles.value.find(x => x.name === f.name)) uploadFiles.value.push(f)
  }
}

function onDrop(e) {
  for (const f of e.dataTransfer.files) {
    if (!uploadFiles.value.find(x => x.name === f.name)) uploadFiles.value.push(f)
  }
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : ''
}

function confirmSubmit() { showConfirm.value = true }

async function doSubmit() {
  showConfirm.value = false
  submitting.value  = true
  try {
    const formData = new FormData()

    if (questions.value.length > 0) {
      formData.append('content', JSON.stringify(answers))
    } else {
      formData.append('content', textContent.value)
    }

    for (const f of uploadFiles.value) {
      formData.append('files[]', f)
    }

    const { data } = await api.post(
      `/student/assignments/${assignmentId}/submit`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    clearDraft()

    // Show submitted state by reloading details
    const reload = await api.get(`/student/assignments/${assignmentId}`)
    submission.value = reload.data.data?.submission ?? data.data

    const countKey = `student_assignment_attempts_count_${assignmentId}`
    const newCount = attemptsCount.value + 1
    attemptsCount.value = newCount
    localStorage.setItem(countKey, String(newCount))
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Nộp bài thất bại. Thử lại.')
    submitting.value = false
  }
}

const stripHtml  = (html) => html ? html.replace(/<[^>]*>/g, '').trim() : ''
const optionText = (opt)  => {
  if (opt === null || opt === undefined) return ''
  if (typeof opt === 'object') return stripHtml(String(opt.text ?? opt.label ?? opt.content ?? ''))
  return stripHtml(String(opt))
}

const qTypeLabel = (t) => ({
  multiple_choice: 'Trắc nghiệm',   multiple_select: 'Nhiều đáp án',  true_false: 'Đúng/Sai',
  fill_blank:      'Điền chỗ trống', short_answer: 'Trả lời ngắn',     essay: 'Tự luận',
  ordering:        'Sắp xếp',        matching: 'Ghép đôi',              listening: 'Nghe hiểu',
  writing:         'Viết',           calculation: 'Tính toán',           reading: 'Đọc hiểu',
  speaking:        'Nói',
}[t] ?? t)

const qTypeClass = (t) => ({
  multiple_choice: 'bg-blue-100 text-blue-700',    multiple_select: 'bg-red-100 text-[#c02a10]',
  true_false:      'bg-teal-100 text-teal-700',     fill_blank: 'bg-amber-100 text-amber-700',
  short_answer:    'bg-yellow-100 text-yellow-700', essay: 'bg-gray-100 text-gray-600',
  ordering:        'bg-pink-100 text-pink-700',     matching: 'bg-purple-100 text-purple-700',
  listening:       'bg-green-100 text-green-700',   writing: 'bg-cyan-100 text-cyan-700',
  calculation:     'bg-red-100 text-[#c02a10]', reading: 'bg-emerald-100 text-emerald-700',
  speaking:        'bg-rose-100 text-rose-700',
}[t] ?? 'bg-gray-100 text-gray-500')
</script>

<style>
/* Styling math structures rendered by KaTeX */
.q-content .katex {
  font-size: 1.05em;
  line-height: inherit;
}
.katex-display {
  margin: 0.5em 0 !important;
}
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
.select-none {
  user-select: none !important;
  -webkit-user-select: none !important;
  -moz-user-select: none !important;
  -ms-user-select: none !important;
}
</style>
