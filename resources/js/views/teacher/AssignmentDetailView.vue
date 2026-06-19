<template>
  <div>
    <div class="space-y-5">
    <!-- Back + Header -->
    <div class="flex items-start gap-4">
      <button @click="$router.back()"
        class="mt-0.5 p-2.5 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-500 hover:text-gray-700 transition-all shrink-0 shadow-sm bg-white">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <div class="flex-1 min-w-0">
        <div v-if="assignment" class="flex flex-wrap items-center gap-2 mb-1.5">
          <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">
            Bài tập về nhà
          </span>
          <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
            :class="assignment.status === 'published' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-500 border border-gray-200'">
            {{ assignment.status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
          </span>
          <span v-if="assignment.deadline && isOverdue(assignment.deadline)"
            class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600 border border-red-200">
            Hết hạn
          </span>
        </div>
        <h2 class="text-xl font-bold text-gray-900 truncate leading-tight">{{ assignment?.title ?? 'Đang tải...' }}</h2>
        <p class="text-sm text-gray-400 mt-1 flex items-center gap-1.5 flex-wrap">
          <span>📚 {{ assignment?.subject?.name }}</span>
          <span class="text-gray-300">·</span>
          <span>🏫 {{ assignment?.classroom?.name ?? 'Công khai' }}</span>
          <span v-if="assignment?.deadline" class="text-gray-300">·</span>
          <span v-if="assignment?.deadline" class="text-red-500 font-semibold">⏰ Hạn: {{ formatDate(assignment.deadline) }}</span>
        </p>
      </div>
      <!-- Stats pills -->
      <div v-if="assignment" class="hidden md:flex items-center gap-3 shrink-0">
        <div class="text-center px-4 py-2 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <p class="text-xl font-black text-gray-900">{{ questions.length }}</p>
          <p class="text-xs text-gray-400 font-semibold mt-0.5">Câu hỏi</p>
        </div>
        <div class="text-center px-4 py-2 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <p class="text-xl font-black text-gray-900">{{ subMeta.total ?? 0 }}</p>
          <p class="text-xs text-gray-400 font-semibold mt-0.5">Đã nộp</p>
        </div>
        <div class="text-center px-4 py-2 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <p class="text-xl font-black text-[#d63015]">{{ avgScore ?? '—' }}</p>
          <p class="text-xs text-gray-400 font-semibold mt-0.5">Điểm TB</p>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 border-b border-gray-100 bg-white/40 p-1.5 rounded-2xl">
      <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
        class="px-4 py-2 text-sm font-semibold rounded-xl transition-all flex items-center gap-2"
        :class="activeTab === tab.key
          ? 'text-white bg-[#d63015] shadow-sm shadow-red-500/10'
          : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'">
        <span>{{ tab.icon }}</span>
        <span>{{ tab.label }}</span>
        <span v-if="tab.key === 'questions' && questions.length"
          class="ml-1.5 text-xs px-2 py-0.5 rounded-full"
          :class="activeTab === tab.key ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'">
          {{ questions.length }}
        </span>
      </button>
    </div>

    <!-- ── Tab: Câu hỏi ───────────────────────────────────────────────────── -->
    <div v-if="activeTab === 'questions'" class="space-y-4">
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div>
          <h3 class="text-base font-bold text-gray-800">Danh sách câu hỏi</h3>
          <p class="text-xs text-gray-400 mt-0.5">
            Tổng số: <strong class="text-gray-700">{{ questions.length }}</strong> câu hỏi · Tổng điểm: <strong class="text-[#d63015]">{{ totalPoints }}</strong> điểm
          </p>
        </div>
        <div class="flex items-center gap-2">
          <!-- Add from Bank -->
          <button @click="openBankModal"
            class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-bold shadow-sm transition-all">
            📂 Thêm từ ngân hàng
          </button>
          <!-- Create New -->
          <button @click="openCreate"
            class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-bold hover:bg-[#c02a10] shadow-sm transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tạo câu hỏi mới
          </button>
        </div>
      </div>

      <div v-if="qLoading" class="py-16 text-center text-gray-400">
        <div class="animate-spin w-8 h-8 border-3 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"/>
        Đang tải câu hỏi...
      </div>

      <div v-else-if="!questions.length"
        class="py-20 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
        <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4 border border-gray-100">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
          </svg>
        </div>
        <h4 class="font-bold text-gray-700">Chưa có câu hỏi nào trong bài tập này</h4>
        <p class="text-sm text-gray-400 mt-1 max-w-sm mx-auto leading-relaxed">
          Bài tập này hiện đang trống. Hãy tạo một câu hỏi mới hoặc gán các câu hỏi có sẵn từ Ngân hàng câu hỏi của bạn.
        </p>
        <div class="mt-5 flex items-center justify-center gap-3">
          <button @click="openBankModal" class="px-4 py-2 border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-xl text-xs font-bold transition-all bg-white">Chọn từ ngân hàng</button>
          <button @click="openCreate" class="px-4 py-2 bg-[#d63015] hover:bg-[#c02a10] text-white rounded-xl text-xs font-bold transition-all">Tạo câu hỏi mới</button>
        </div>
      </div>

      <div v-else class="space-y-3">
        <div v-for="(q, idx) in questions" :key="q.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-all group flex items-start gap-4">
          <!-- Index badge -->
          <div class="w-8 h-8 rounded-xl bg-gray-50 text-gray-600 flex items-center justify-center text-xs font-extrabold border border-gray-100 shrink-0 mt-0.5 shadow-sm">
            {{ idx + 1 }}
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold" :class="qTypeClass(q.type)">
                {{ qTypeLabel(q.type) }}
              </span>
              <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold" :class="diffClass(q.difficulty)">
                {{ diffLabel(q.difficulty) }}
              </span>
              <span v-if="q.chapter_tag" class="px-2 py-0.5 rounded-lg text-[10px] bg-gray-100 text-gray-500 font-medium">
                # {{ q.chapter_tag }}
              </span>
              <span v-if="q.question_bank_id" class="px-2 py-0.5 rounded-lg text-[10px] bg-green-50 text-green-600 font-semibold border border-green-100" title="Đã đồng bộ với ngân hàng câu hỏi">
                Linked Bank
              </span>
              <span class="ml-auto text-xs font-bold text-[#d63015] bg-red-50/50 border border-red-100 px-2 py-0.5 rounded-lg">
                {{ q.points }}đ
              </span>
            </div>

            <!-- Content text with math rendering support -->
            <div class="text-sm text-gray-800 leading-relaxed" v-html="renderMath(q.content)"></div>

            <!-- Image media display -->
            <div v-if="q.media_type === 'image' && q.media_path" class="mt-3 max-w-md">
              <img :src="'/storage/' + q.media_path" class="max-h-48 object-contain rounded-xl border border-gray-200 shadow-sm" />
            </div>

            <!-- Audio Player (Listening Type) -->
            <div v-if="q.type === 'listening' && q.audio_path" class="mt-3 max-w-md">
              <audio controls :src="'/storage/' + q.audio_path" class="w-full h-8 rounded-lg" />
            </div>

            <!-- Options rendered cleanly -->
            <div v-if="Array.isArray(q.options) && q.options.length" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
              <div v-for="(opt, oi) in q.options" :key="oi"
                class="text-xs px-3 py-2 rounded-xl flex items-start gap-2 border"
                :class="q.type === 'ordering'
                  ? 'bg-gray-50 border-gray-100 text-gray-600'
                  : isCorrectOption(q, oi)
                    ? 'bg-green-50/70 border-green-200 text-green-700 font-semibold'
                    : 'bg-white border-gray-100 text-gray-500 hover:bg-gray-50'">
                <span class="font-bold text-gray-400 shrink-0">
                  {{ q.type === 'ordering' ? (oi + 1) : String.fromCharCode(65 + oi) }}.
                </span>
                <span class="break-words" v-html="renderMath(optionText(opt))"></span>
                <span v-if="q.type !== 'ordering' && isCorrectOption(q, oi)" class="text-green-600 ml-auto shrink-0 text-[10px] font-bold">✔ Đúng</span>
              </div>
            </div>
            
            <!-- Explanation preview -->
            <div v-if="q.explanation" class="mt-3 bg-gray-50 rounded-xl p-3 border border-gray-100 text-xs text-gray-500 leading-relaxed flex gap-2">
              <span class="font-bold text-[#d63015] shrink-0">💡 Giải thích:</span>
              <span v-html="renderMath(q.explanation)"></span>
            </div>
          </div>

          <!-- Question Actions -->
          <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click="openEdit(q)"
              class="p-2 rounded-xl hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-all" title="Chỉnh sửa câu hỏi">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
            <button @click="confirmDelete(q)"
              class="p-2 rounded-xl hover:bg-red-50 text-gray-400 hover:text-red-600 transition-all" title="Xóa khỏi bài tập">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Tab: Xem trước làm bài (Student Preview) ────────────────────────── -->
    <div v-else-if="activeTab === 'preview'" class="space-y-4">
      <div v-if="!questions.length" class="py-16 text-center text-gray-400 bg-white rounded-3xl border border-gray-100">
        Hãy thêm ít nhất một câu hỏi vào bài tập để có thể sử dụng tính năng xem trước.
      </div>
      
      <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        <!-- Center Interactor Panel -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col min-h-[500px]">
          <!-- Header info -->
          <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
              XEM TRƯỚC HỌC SINH · CÂU {{ currentPreIndex + 1 }} / {{ questions.length }}
            </span>
            <span class="text-xs text-red-500 font-bold bg-red-50 px-2.5 py-1 rounded-lg border border-red-100">
              👁️ CHẾ ĐỘ XEM TRƯỚC
            </span>
          </div>

          <!-- Active Question area -->
          <div class="p-6 flex-1 flex flex-col justify-between">
            <div class="space-y-5">
              <!-- Content -->
              <div class="text-base text-gray-800 leading-relaxed font-medium" v-html="renderMath(activePreQuestion.content)"></div>

              <!-- Audio Player (Listening Type) -->
              <div v-if="activePreQuestion.type === 'listening' && activePreQuestion.audio_path" class="bg-gray-50 p-4 rounded-2xl border max-w-xl">
                <p class="text-xs font-bold text-gray-500 mb-2 flex items-center gap-1.5">🎵 File nghe hội thoại:</p>
                <audio controls :src="'/storage/' + activePreQuestion.audio_path" class="w-full h-8 rounded-lg" />
              </div>

              <!-- Interactor input choices -->
              <!-- MC / Listening / True False -->
              <div v-if="activePreQuestion.type === 'multiple_choice' || activePreQuestion.type === 'true_false' || activePreQuestion.type === 'listening'" class="grid grid-cols-1 gap-3">
                <button v-for="(opt, oi) in activePreQuestion.options" :key="oi"
                  @click="selectPreviewOption(activePreQuestion.id, String(oi))"
                  class="p-4 rounded-2xl border text-left text-sm transition-all flex items-center gap-3"
                  :class="previewAnswers[activePreQuestion.id] === String(oi)
                    ? 'bg-[#d63015]/5 border-[#d63015] text-[#d63015] font-semibold'
                    : 'bg-white hover:bg-gray-50 border-gray-200 text-gray-700'">
                  <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-black transition-colors"
                    :class="previewAnswers[activePreQuestion.id] === String(oi) ? 'bg-[#d63015] text-white' : 'bg-gray-100 text-gray-400'">
                    {{ String.fromCharCode(65 + oi) }}
                  </span>
                  <span class="break-words" v-html="renderMath(optionText(opt))"></span>
                </button>
              </div>

              <!-- Multiple select -->
              <div v-else-if="activePreQuestion.type === 'multiple_select'" class="grid grid-cols-1 gap-3">
                <button v-for="(opt, oi) in activePreQuestion.options" :key="oi"
                  @click="togglePreviewMultiOption(activePreQuestion.id, String(oi))"
                  class="p-4 rounded-2xl border text-left text-sm transition-all flex items-center gap-3"
                  :class="(previewAnswers[activePreQuestion.id] || []).includes(String(oi))
                    ? 'bg-[#d63015]/5 border-[#d63015] text-[#d63015] font-semibold'
                    : 'bg-white hover:bg-gray-50 border-gray-200 text-gray-700'">
                  <div class="w-5 h-5 rounded border flex items-center justify-center shrink-0"
                    :class="(previewAnswers[activePreQuestion.id] || []).includes(String(oi)) ? 'bg-[#d63015] border-transparent text-white' : 'border-gray-300 bg-white'">
                    <span v-if="(previewAnswers[activePreQuestion.id] || []).includes(String(oi))" class="text-[10px]">✔</span>
                  </div>
                  <span class="w-6 text-xs font-black text-gray-400">{{ String.fromCharCode(65 + oi) }}.</span>
                  <span class="break-words" v-html="renderMath(optionText(opt))"></span>
                </button>
              </div>

              <!-- Ordering -->
              <div v-else-if="activePreQuestion.type === 'ordering'" class="space-y-2">
                <p class="text-xs text-gray-400 italic">Học sinh sẽ sắp xếp các đáp án theo thứ tự chuẩn xác.</p>
                <div v-for="(opt, oi) in activePreQuestion.options" :key="oi"
                  class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-600 flex items-center gap-3">
                  <span class="w-5 h-5 bg-gray-200 text-gray-600 font-bold rounded-lg flex items-center justify-center text-[10px]">
                    {{ oi + 1 }}
                  </span>
                  <span v-html="renderMath(optionText(opt))"></span>
                </div>
              </div>

              <!-- Essay -->
              <div v-else-if="activePreQuestion.type === 'essay'" class="space-y-3">
                <textarea v-model="previewAnswers[activePreQuestion.id]" rows="6" class="w-full p-4 rounded-2xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm" placeholder="Nhập bài viết luận của bạn tại đây... (Chế độ xem trước)"></textarea>
              </div>

              <!-- Short Answer -->
              <div v-else-if="activePreQuestion.type === 'short_answer'" class="space-y-2">
                <input v-model="previewAnswers[activePreQuestion.id]" type="text" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm bg-white" placeholder="Nhập đáp án ngắn..." />
              </div>

              <!-- Fill in the blank -->
              <div v-else-if="activePreQuestion.type === 'fill_blank'" class="space-y-3">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-2">Đáp án điền từ:</p>
                <div v-for="(_, fi) in activePreQuestion.correct_answer" :key="fi" class="flex items-center gap-3">
                  <span class="text-xs font-bold text-gray-500 bg-gray-100 rounded-lg px-2.5 py-1.5">Chỗ [{{ fi + 1 }}]</span>
                  <input type="text" class="input flex-1 py-1.5" placeholder="Nhập từ..." />
                </div>
              </div>

              <!-- Matching -->
              <div v-else-if="activePreQuestion.type === 'matching'" class="space-y-2">
                <p class="text-xs text-gray-400 italic">Vế trái sẽ được hiển thị ghép cặp vế phải tương ứng.</p>
                <div class="grid grid-cols-2 gap-3">
                  <div class="space-y-2">
                    <div v-for="(l, li) in activePreQuestion.options?.left" :key="li" class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-800 font-medium">
                      {{ l }}
                    </div>
                  </div>
                  <div class="space-y-2">
                    <div v-for="(r, ri) in activePreQuestion.options?.right" :key="ri" class="p-3 bg-purple-50 border border-purple-100 rounded-xl text-xs text-purple-800 font-medium">
                      {{ r }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer navigation -->
            <div class="mt-8 border-t border-gray-100 pt-5 flex items-center justify-between">
              <button @click="prevPreQuestion" :disabled="currentPreIndex === 0"
                class="px-4 py-2 border border-gray-200 hover:bg-gray-50 rounded-xl text-sm font-semibold text-gray-600 transition-all disabled:opacity-40">
                Câu trước
              </button>
              <button v-if="currentPreIndex < questions.length - 1" @click="nextPreQuestion"
                class="px-4 py-2 bg-gray-900 hover:bg-black text-white rounded-xl text-sm font-semibold transition-all">
                Câu tiếp theo
              </button>
              <button v-else @click="submitPreMock"
                class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-bold shadow-sm transition-all">
                Nộp bài thử nghiệm
              </button>
            </div>
          </div>
        </div>

        <!-- Right Side Index Panel -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-gray-100 shadow-sm p-5 space-y-5">
          <div>
            <h4 class="font-bold text-gray-800 text-sm">Bảng câu hỏi</h4>
            <p class="text-xs text-gray-400 mt-0.5">Click vào các số để chuyển nhanh</p>
          </div>

          <div class="grid grid-cols-5 gap-2">
            <button v-for="(q, index) in questions" :key="q.id"
              @click="currentPreIndex = index"
              class="w-full aspect-square rounded-xl text-xs font-bold transition-all border flex items-center justify-center"
              :class="currentPreIndex === index
                ? 'bg-[#d63015] border-transparent text-white shadow-sm shadow-red-500/20 scale-105'
                : previewAnswers[q.id] != null && String(previewAnswers[q.id]).trim() !== ''
                  ? 'bg-green-50 border-green-200 text-green-600 font-extrabold'
                  : 'bg-white border-gray-200 hover:border-gray-300 text-gray-600'">
              {{ index + 1 }}
            </button>
          </div>

          <div class="border-t border-gray-100 pt-4 space-y-2">
            <div class="flex items-center gap-2 text-xs text-gray-400">
              <span class="w-3.5 h-3.5 rounded-md bg-[#d63015]"></span>
              <span>Đang chọn xem</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-400">
              <span class="w-3.5 h-3.5 rounded-md bg-green-50 border border-green-200"></span>
              <span>Đã làm thử</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-400">
              <span class="w-3.5 h-3.5 rounded-md bg-white border border-gray-200"></span>
              <span>Chưa làm thử</span>
            </div>
          </div>

          <div class="pt-2">
            <button @click="resetPreAnswers" class="w-full py-2 border border-dashed border-red-200 hover:bg-red-50 text-[#d63015] text-xs font-bold rounded-xl transition-all">
              Xóa lịch sử làm thử
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Tab: Bài nộp ───────────────────────────────────────────────────── -->
    <div v-else-if="activeTab === 'submissions'">
      <!-- Filters -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 mb-5">
        <div class="relative flex-1 min-w-[200px]">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="search" @input="debounceFetch" type="text" placeholder="Tìm tên học sinh..."
            class="w-full pl-11 pr-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] bg-gray-50/50" />
        </div>
        <select v-model="filterStatus" @change="fetchSubmissions()" class="px-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] bg-white text-gray-600 font-medium">
          <option value="">Tất cả trạng thái</option>
          <option value="submitted">Đã nộp</option>
          <option value="graded">Đã chấm</option>
        </select>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div v-if="sLoading" class="py-16 text-center text-gray-400">
          <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"/>
          Đang tải bài nộp...
        </div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">#</th>
              <th class="text-left px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Học sinh</th>
              <th class="text-left px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Thời gian nộp</th>
              <th class="text-left px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Điểm số</th>
              <th class="text-left px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Trạng thái</th>
              <th class="text-right px-5 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="!submissions.length">
              <td colspan="6" class="py-16 text-center text-gray-400">
                <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Chưa có học sinh nào nộp bài tập này
              </td>
            </tr>
            <tr v-for="(s, idx) in submissions" :key="s.id" class="hover:bg-gray-50/50 transition-colors">
              <td class="px-5 py-3 text-gray-400 text-xs font-bold">
                {{ (subMeta.current_page - 1) * 30 + idx + 1 }}
              </td>
              <td class="px-5 py-3">
                <p class="font-semibold text-gray-800">{{ s.student?.name ?? '—' }}</p>
                <p class="text-xs text-gray-400">{{ s.student?.email }}</p>
              </td>
              <td class="px-5 py-3 hidden sm:table-cell">
                <p class="text-sm text-gray-700 font-medium">{{ s.submitted_at ? formatDateTime(s.submitted_at) : '—' }}</p>
                <p v-if="s.is_late" class="text-[10px] text-red-500 font-bold mt-0.5">⚠️ Nộp muộn</p>
              </td>
              <td class="px-5 py-3">
                <template v-if="s.score != null">
                  <span class="text-base font-black text-green-600">{{ s.score }}</span>
                  <span class="text-xs text-gray-400 font-semibold">/{{ assignment?.max_score ?? 10 }}</span>
                </template>
                <span v-else class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full font-medium">Chưa chấm</span>
              </td>
              <td class="px-5 py-3 hidden lg:table-cell">
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold"
                  :class="s.status === 'graded' ? 'bg-green-50 text-green-600 border border-green-100' : s.status === 'submitted' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-gray-100 text-gray-500'">
                  {{ { graded: 'Đã chấm', submitted: 'Đã nộp', late: 'Nộp muộn', draft: 'Bản nháp' }[s.status] ?? s.status }}
                </span>
              </td>
              <td class="px-5 py-3 text-right">
                <button @click="openGradeModal(s)"
                  class="px-3 py-1.5 rounded-xl bg-gray-900 text-white text-xs font-bold hover:bg-black transition-all shadow-sm">
                  Chấm điểm
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :meta="subMeta" @page="fetchSubmissions" class="mt-4" />
    </div>

    <!-- ── Modal: Thêm từ ngân hàng câu hỏi ────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="bankModal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4" @click.self="bankModal.show = false">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-4xl overflow-hidden flex flex-col h-[80vh]">
          <!-- Header -->
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h3 class="font-bold text-gray-800 text-base">Thêm từ ngân hàng câu hỏi</h3>
              <p class="text-xs text-gray-400 mt-0.5">Môn học: <strong class="text-[#d63015]">{{ assignment?.subject?.name }}</strong></p>
            </div>
            <button @click="bankModal.show = false" class="p-2 rounded-xl hover:bg-gray-100 text-gray-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Filters inside modal -->
          <div class="px-6 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-3">
            <div class="relative flex-1">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              <input v-model="bankModal.search" @input="debounceBankFetch" type="text" placeholder="Tìm câu hỏi..." class="w-full pl-9 pr-4 py-1.5 text-xs rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] bg-white"/>
            </div>
            <select v-model="bankModal.difficulty" @change="fetchBankQuestions" class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 bg-white font-semibold">
              <option value="">Độ khó</option>
              <option value="easy">Dễ</option>
              <option value="medium">Trung bình</option>
              <option value="hard">Khó</option>
            </select>
          </div>

          <!-- Questions Bank list -->
          <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
            <div v-if="bankModal.loading" class="py-12 text-center text-gray-400">
              <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>
              Đang tải câu hỏi ngân hàng...
            </div>
            <div v-else-if="bankModal.questions.length === 0" class="py-16 text-center text-gray-400 text-sm">
              Không tìm thấy câu hỏi nào có môn học trùng với bài tập này trong ngân hàng.
            </div>
            <template v-else>
              <div v-for="q in bankModal.questions" :key="q.id" class="px-6 py-4 hover:bg-gray-50/50 transition-colors flex items-start gap-4 justify-between">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold" :class="qTypeClass(q.type)">{{ qTypeLabel(q.type) }}</span>
                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold" :class="diffClass(q.difficulty)">{{ diffLabel(q.difficulty) }}</span>
                    <span v-if="q.chapter_tag" class="px-2 py-0.5 rounded-lg text-[9px] bg-gray-100 text-gray-500">{{ q.chapter_tag }}</span>
                  </div>
                  <div class="text-sm text-gray-800 line-clamp-3" v-html="renderMath(q.content)"></div>
                </div>

                <!-- Add/Remove Action -->
                <div class="shrink-0 ml-3">
                  <button v-if="isAlreadyAdded(q.id)" @click="toggleBankQuestion(q)"
                    class="p-2 rounded-xl bg-red-50 text-[#d63015] hover:bg-red-100 hover:text-red-700 transition-colors" title="Bỏ gán khỏi bài tập">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                  </button>
                  <button v-else @click="toggleBankQuestion(q)"
                    class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-[#d63015] transition-colors" title="Thêm vào bài tập">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                  </button>
                </div>
              </div>
            </template>
          </div>

          <!-- Footer pagination -->
          <div v-if="bankModal.meta.last_page > 1" class="px-6 py-3 border-t border-gray-100 flex items-center justify-between bg-gray-50">
            <span class="text-xs text-gray-500">{{ bankModal.meta.total }} câu hỏi</span>
            <div class="flex gap-1">
              <button v-for="p in bankModal.meta.last_page" :key="p" @click="goBankPage(p)"
                class="w-7 h-7 rounded-lg text-[10px] font-bold transition-all"
                :class="p === bankModal.meta.current_page ? 'bg-[#d63015] text-white' : 'bg-white hover:bg-gray-100 border text-gray-600'">
                {{ p }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal: Chấm điểm bài nộp của học sinh ─────────────────────────── -->
    <AppModal v-model="gradeModal.show" title="Chấm điểm bài tập" size="md">
      <form v-if="gradeModal.submission" class="space-y-4" @submit.prevent="submitGrade">
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Học sinh</label>
          <p class="text-sm font-semibold text-gray-800 bg-gray-50 px-3 py-2 rounded-xl">
            {{ gradeModal.submission.student?.name }} ({{ gradeModal.submission.student?.email }})
          </p>
        </div>

        <div v-if="gradeModal.submission.file_path" class="p-3 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-xl">📂</span>
            <div>
              <p class="text-xs font-bold text-blue-800">Tệp đính kèm học sinh nộp</p>
              <p class="text-[10px] text-blue-600 truncate max-w-xs">{{ gradeModal.submission.file_path.split('/').pop() }}</p>
            </div>
          </div>
          <a :href="'/storage/' + gradeModal.submission.file_path" target="_blank"
            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-[10px] transition-all">
            Tải file
          </a>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Điểm số <span class="text-red-500">*</span></label>
            <input v-model.number="gradeModal.score" type="number" step="0.1" min="0" :max="assignment?.max_score ?? 10" class="input" required />
            <p class="text-[10px] text-gray-400 mt-0.5">Tối đa: {{ assignment?.max_score ?? 10 }} điểm</p>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 mb-1">Trạng thái chấm</label>
            <div class="px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-xs font-semibold text-green-600">
              Chấm điểm tự động
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Nhận xét nhận xét giáo viên</label>
          <textarea v-model="gradeModal.feedback" rows="3" class="input" placeholder="Nhập lời phê, nhận xét cho học sinh..."></textarea>
        </div>

        <div v-if="gradeModal.error" class="text-xs text-red-500 bg-red-50 p-3 rounded-xl">{{ gradeModal.error }}</div>
      </form>
      <template #footer>
        <button @click="gradeModal.show = false" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">Hủy</button>
        <button @click="submitGrade" :disabled="gradeModal.saving"
          class="px-4 py-2 text-sm rounded-xl bg-gray-900 hover:bg-black text-white font-semibold disabled:opacity-60">
          {{ gradeModal.saving ? 'Đang chấm...' : 'Xác nhận chấm' }}
        </button>
      </template>
    </AppModal>

    <!-- Question form modal -->
    <QuestionFormModal
      v-model="formModal"
      :edit-item="editQuestion"
      :subject-code="assignment?.subject?.code"
      :saving="saving"
      :error="formError"
      @save="saveQuestion"
    />

    <!-- Delete confirm -->
    <AppModal v-model="deleteModal" title="Xác nhận xóa" size="sm">
      <p class="text-sm text-gray-600 leading-relaxed">Bạn có chắc muốn xóa câu hỏi này khỏi bài tập?</p>
      <template #footer>
        <button @click="deleteModal = false" class="px-4 py-2 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">Hủy</button>
        <button @click="doDelete" :disabled="deleting"
          class="px-4 py-2 text-sm rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold disabled:opacity-60">
          {{ deleting ? 'Đang xóa...' : 'Xóa' }}
        </button>
      </template>
    </AppModal>
  </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@api/axios'
import { renderMath } from '@/utils/math'
import { useToastStore } from '@stores/toast'
import { useConfirmStore } from '@stores/confirm'
import Pagination from '@components/common/Pagination.vue'
import AppModal from '@components/common/AppModal.vue'
import QuestionFormModal from '@components/common/QuestionFormModal.vue'

const route = useRoute()
const assignmentId = route.params.id

const toast = useToastStore()
const confirmStore = useConfirmStore()

const assignment = ref(null)
const activeTab = ref('questions')
const tabs = [
  { key: 'questions', label: 'Câu hỏi', icon: '📝' },
  { key: 'preview',   label: 'Xem trước', icon: '👁️' },
  { key: 'submissions', label: 'Bài nộp học sinh', icon: '🏆' },
]

// ── Questions state ───────────────────────────────────────────────────────
const questions = ref([])
const qLoading  = ref(true)
const formModal  = ref(false)
const editQuestion = ref(null)
const saving     = ref(false)
const formError  = ref('')
const deleteModal   = ref(false)
const deleteTarget  = ref(null)
const deleting   = ref(false)

const totalPoints = computed(() =>
  questions.value.reduce((s, q) => s + Number(q.points ?? 0), 0).toFixed(2).replace(/\.00$/, '')
)

// ── Submissions state ─────────────────────────────────────────────────────
const submissions = ref([])
const sLoading    = ref(false)
const search      = ref('')
const filterStatus = ref('')
const subMeta     = ref({ total: 0, current_page: 1, last_page: 1 })

const avgScore = computed(() => {
  const scored = submissions.value.filter(s => s.score != null)
  if (!scored.length) return null
  return (scored.reduce((sum, s) => sum + Number(s.score), 0) / scored.length).toFixed(1)
})

let debounceTimer = null
function debounceFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    if (activeTab.value === 'submissions') fetchSubmissions()
    else fetchAttempts()
  }, 400)
}

// ── Preview mock state ───────────────────────────────────────────────────
const currentPreIndex = ref(0)
const previewAnswers = ref({})
const activePreQuestion = computed(() => questions.value[currentPreIndex.value] || {})

function nextPreQuestion() {
  if (currentPreIndex.value < questions.value.length - 1) {
    currentPreIndex.value++
  }
}
function prevPreQuestion() {
  if (currentPreIndex.value > 0) {
    currentPreIndex.value--
  }
}
function selectPreviewOption(qId, val) {
  previewAnswers.value[qId] = val
}
function togglePreviewMultiOption(qId, val) {
  if (!previewAnswers.value[qId]) {
    previewAnswers.value[qId] = []
  }
  const arr = previewAnswers.value[qId]
  if (arr.includes(val)) {
    previewAnswers.value[qId] = arr.filter(v => v !== val)
  } else {
    arr.push(val)
  }
}
function resetPreAnswers() {
  previewAnswers.value = {}
  currentPreIndex.value = 0
  toast.success('Đã xóa lịch sử làm thử')
}
function submitPreMock() {
  let score = 0
  let correctCount = 0
  questions.value.forEach(q => {
    const studentAns = previewAnswers.value[q.id]
    if (q.type === 'multiple_choice' || q.type === 'true_false' || q.type === 'listening') {
      if (studentAns !== undefined && isCorrectOption(q, Number(studentAns))) {
        score += Number(q.points ?? 1)
        correctCount++
      }
    } else if (q.type === 'multiple_select') {
      const arrAns = Array.isArray(studentAns) ? studentAns : []
      let isMatch = false
      if (Array.isArray(q.options)) {
        isMatch = q.options.every((_, oi) => {
          const studentSelected = arrAns.includes(String(oi))
          const correct = isCorrectOption(q, oi)
          return studentSelected === correct
        })
      }
      if (isMatch) {
        score += Number(q.points ?? 1)
        correctCount++
      }
    } else {
      const correct = q.correct_answer
      if (studentAns && String(studentAns).trim().toLowerCase() === String(Array.isArray(correct) ? correct[0] : correct).trim().toLowerCase()) {
        score += Number(q.points ?? 1)
        correctCount++
      }
    }
  })
  confirmStore.ask(`Xem trước kết quả làm thử bài tập:\n- Số câu đúng: ${correctCount}/${questions.value.length}\n- Tổng điểm làm được: ${score.toFixed(1)} điểm.\n\nNhấp xác nhận để làm lại.`).then(() => {
    resetPreAnswers()
  })
}

// ── Bank question modal state ───────────────────────────────────────────
const bankModal = reactive({
  show: false,
  questions: [],
  loading: false,
  search: '',
  difficulty: '',
  page: 1,
  meta: { total: 0, current_page: 1, last_page: 1 }
})

function openBankModal() {
  bankModal.search = ''
  bankModal.difficulty = ''
  bankModal.page = 1
  bankModal.show = true
  fetchBankQuestions()
}

async function fetchBankQuestions() {
  bankModal.loading = true
  try {
    const { data } = await api.get('/teacher/question-bank', {
      params: {
        subject_id: assignment.value?.subject_id,
        search: bankModal.search,
        difficulty: bankModal.difficulty,
        page: bankModal.page
      }
    })
    const list = data.data
    if (list?.data) {
      bankModal.questions = list.data
      bankModal.meta = { current_page: list.current_page, last_page: list.last_page, total: list.total }
    } else {
      bankModal.questions = Array.isArray(list) ? list : []
    }
  } finally {
    bankModal.loading = false
  }
}

let bankDebounce = null
function debounceBankFetch() {
  clearTimeout(bankDebounce)
  bankDebounce = setTimeout(() => {
    bankModal.page = 1
    fetchBankQuestions()
  }, 400)
}

function goBankPage(p) {
  bankModal.page = p
  fetchBankQuestions()
}

function isAlreadyAdded(bankQuestionId) {
  return questions.value.some(q => q.question_bank_id === bankQuestionId)
}

async function toggleBankQuestion(q) {
  const isAdded = isAlreadyAdded(q.id)
  try {
    if (isAdded) {
      await api.post(`/teacher/assignments/${assignmentId}/remove-questions`, { question_ids: [q.id] })
      toast.success('Đã loại bỏ câu hỏi khỏi bài tập')
    } else {
      await api.post(`/teacher/assignments/${assignmentId}/import-questions`, { question_ids: [q.id] })
      toast.success('Đã thêm câu hỏi vào bài tập')
    }
    await fetchAssignment()
  } catch (err) {
    toast.error(err.response?.data?.message ?? 'Thao tác không thành công')
  }
}

// ── Grading Modal State ─────────────────────────────────────────────────
const gradeModal = reactive({
  show: false,
  submission: null,
  score: 0,
  feedback: '',
  saving: false,
  error: ''
})

function openGradeModal(s) {
  gradeModal.submission = s
  gradeModal.score = s.score ?? 0
  gradeModal.feedback = s.feedback ?? ''
  gradeModal.error = ''
  gradeModal.show = true
}

async function submitGrade() {
  gradeModal.saving = true
  gradeModal.error = ''
  try {
    await api.post(`/teacher/assignments/${assignmentId}/submissions/${gradeModal.submission.id}/grade`, {
      score: gradeModal.score,
      feedback: gradeModal.feedback
    })
    toast.success('Chấm điểm bài nộp học sinh thành công')
    gradeModal.show = false
    fetchSubmissions()
  } catch (err) {
    gradeModal.error = err.response?.data?.message ?? 'Không thể chấm điểm'
  } finally {
    gradeModal.saving = false
  }
}

// ── API calls ─────────────────────────────────────────────────────────────
async function fetchAssignment() {
  qLoading.value = true
  try {
    const { data } = await api.get(`/teacher/assignments/${assignmentId}`)
    assignment.value = data.data
    questions.value = data.data?.questions ?? []
  } catch (err) {
    console.error('Error fetching assignment:', err)
    toast.error(err.response?.data?.message ?? 'Không thể tải thông tin bài tập. Vui lòng thử lại.')
  } finally {
    qLoading.value = false
  }
}

async function fetchSubmissions(page = 1) {
  sLoading.value = true
  try {
    const { data } = await api.get(`/teacher/assignments/${assignmentId}/submissions`, {
      params: { page, status: filterStatus.value, search: search.value },
    })
    const paged = data.data
    if (Array.isArray(paged)) {
      submissions.value = paged
      subMeta.value = {
        total: data.meta?.total ?? paged.length,
        current_page: data.meta?.current_page ?? 1,
        last_page: data.meta?.last_page ?? 1
      }
    } else {
      submissions.value = paged?.data ?? []
      subMeta.value = {
        total: paged?.total ?? 0,
        current_page: paged?.current_page ?? 1,
        last_page: paged?.last_page ?? 1
      }
    }
  } catch (err) {
    console.error('Error fetching submissions:', err)
  } finally {
    sLoading.value = false
  }
}

function openCreate() {
  editQuestion.value = null
  formError.value = ''
  formModal.value = true
}
function openEdit(q) {
  editQuestion.value = q
  formError.value = ''
  formModal.value = true
}

async function saveQuestion(payload) {
  saving.value = true
  formError.value = ''
  try {
    if (editQuestion.value) {
      await api.put(`/teacher/assignments/${assignmentId}/questions/${editQuestion.value.id}`, payload)
      toast.success('Cập nhật câu hỏi bài tập thành công')
    } else {
      await api.post(`/teacher/assignments/${assignmentId}/questions`, payload)
      toast.success('Thêm câu hỏi mới thành công')
    }
    formModal.value = false
    fetchAssignment()
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) formError.value = Object.values(errors).flat().join(' ')
    else formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra'
  } finally { saving.value = false }
}

function confirmDelete(q) { deleteTarget.value = q; deleteModal.value = true }

async function doDelete() {
  deleting.value = true
  try {
    const q = deleteTarget.value
    if (q.question_bank_id) {
      await api.post(`/teacher/assignments/${assignmentId}/remove-questions`, { question_ids: [q.question_bank_id] })
    } else {
      await api.delete(`/teacher/assignments/${assignmentId}/questions/${q.id}`)
    }
    toast.success('Đã xóa câu hỏi khỏi bài tập')
    deleteModal.value = false
    fetchAssignment()
  } catch (err) {
    toast.error(err.response?.data?.message ?? 'Không thể xóa câu hỏi')
  } finally { deleting.value = false }
}

// ── Helpers ───────────────────────────────────────────────────────────────
const stripHtml  = (html) => html ? html.replace(/<[^>]*>/g, '').trim() : ''
const optionText = (opt)  => {
  if (opt === null || opt === undefined) return ''
  if (typeof opt === 'object') return stripHtml(String(opt.text ?? opt.label ?? opt.content ?? ''))
  return stripHtml(String(opt))
}

function formatDateTime(iso) {
  return iso ? new Date(iso).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) : '—'
}
function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('vi-VN', { dateStyle: 'short' }) : '—'
}
function isOverdue(d) { return d && new Date(d) < new Date() }
function isCorrectOption(q, idx) {
  if (!q.correct_answer) return false
  const ans = Array.isArray(q.correct_answer) ? q.correct_answer.map(String) : [String(q.correct_answer)]
  const opt = q.options?.[idx]
  const optId = (opt && typeof opt === 'object' && opt.id) ? String(opt.id) : null
  const letter = String.fromCharCode(65 + idx)
  return ans.includes(String(idx)) || ans.includes(letter) || (optId && ans.includes(optId))
}

const qTypeLabel = (t) => ({
  multiple_choice: 'Trắc nghiệm', multiple_select: 'Nhiều đáp án', true_false: 'Đúng/Sai',
  fill_blank: 'Điền chỗ trống', short_answer: 'Trả lời ngắn', essay: 'Tự luận',
  ordering: 'Sắp xếp', matching: 'Ghép đôi', listening: 'Nghe hiểu', reading: 'Đọc hiểu',
}[t] ?? t)
const qTypeClass = (t) => ({
  multiple_choice: 'bg-blue-50 text-blue-600 border border-blue-100', multiple_select: 'bg-red-50 text-[#d63015] border border-red-100',
  true_false: 'bg-teal-50 text-teal-600 border border-teal-100', fill_blank: 'bg-orange-50 text-orange-600 border border-orange-100',
  short_answer: 'bg-yellow-50 text-yellow-600 border border-yellow-100', essay: 'bg-gray-50 text-gray-600 border border-gray-100',
  ordering: 'bg-pink-50 text-pink-600 border border-pink-100', matching: 'bg-purple-50 text-purple-600 border border-purple-100',
  listening: 'bg-green-50 text-green-600 border border-green-100', reading: 'bg-cyan-50 text-cyan-600 border border-cyan-100',
}[t] ?? 'bg-gray-100 text-gray-500')
const diffLabel = (d) => ({ easy: 'Dễ', medium: 'TB', hard: 'Khó' }[d] ?? d)
const diffClass = (d) => ({ easy: 'bg-green-50 text-green-600', medium: 'bg-amber-50 text-amber-600', hard: 'bg-red-50 text-red-600' }[d] ?? 'bg-gray-50 text-gray-500')

onMounted(() => {
  fetchAssignment()
  fetchSubmissions()
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm bg-white; }
</style>
