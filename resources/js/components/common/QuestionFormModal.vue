<template>
  <AppModal v-model="show" :title="editItem ? 'Chỉnh sửa câu hỏi' : 'Thêm câu hỏi mới'" size="xl">
    <form class="space-y-5" @submit.prevent>
      <!-- Type + Difficulty + Points -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="label">Loại câu hỏi <span class="text-red-500">*</span></label>
          <select v-model="form.type" @change="onTypeChange" class="input" required>
            <optgroup v-for="group in groupedQuestionTypes" :key="group.label" :label="group.label">
              <option v-for="opt in group.options" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </optgroup>
          </select>
        </div>
        <div>
          <label class="label">Độ khó</label>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="d in difficultiesList"
              :key="d.value"
              type="button"
              @click="form.difficulty = d.value"
              class="py-2.5 rounded-xl border text-xs font-semibold transition-all text-center flex items-center justify-center cursor-pointer shadow-sm"
              :class="form.difficulty === d.value ? d.active : 'border-gray-200 text-gray-600 hover:border-gray-300 bg-white'"
            >
              {{ d.label }}
            </button>
          </div>
        </div>
        <div>
          <label class="label">Điểm</label>
          <input v-model.number="form.points" type="number" step="0.25" min="0.25" max="100" class="input" />
        </div>
      </div>

      <!-- ── Audio upload (listening only) ──────────────────────────────── -->
      <div v-if="form.type === 'listening'" class="space-y-1.5">
        <label class="label">File âm thanh <span class="text-red-500">*</span></label>

        <!-- Uploaded: show player + remove -->
        <div v-if="audioUrl" class="rounded-2xl border border-gray-200 bg-gray-50 p-4 space-y-3 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 3v10.55A4 4 0 1014 17V7h4V3h-6z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-700 truncate">{{ audioFileName }}</p>
              <p class="text-xs text-gray-400">Âm thanh đã tải lên</p>
            </div>
            <button type="button" @click="removeAudio"
              class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
              <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <!-- Native audio player -->
          <audio :src="audioUrl" controls class="w-full h-10 rounded-xl" />
        </div>

        <!-- Not uploaded yet: drop zone -->
        <div v-else
          class="relative rounded-2xl border-2 border-dashed transition-all cursor-pointer shadow-sm"
          :class="isDragging ? 'border-[#d63015] bg-red-50/50' : 'border-gray-200 hover:border-[#d63015] hover:bg-red-50/10'"
          @dragover.prevent="isDragging = true"
          @dragleave="isDragging = false"
          @drop.prevent="onDrop"
          @click="fileInputRef?.click()">

          <input ref="fileInputRef" type="file" accept="audio/*,.mp3,.wav,.ogg,.m4a,.aac"
            class="hidden" @change="onFileSelect" />

          <div v-if="audioUploading" class="py-8 text-center">
            <div class="animate-spin w-7 h-7 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2" />
            <p class="text-sm text-gray-500 font-medium">Đang tải lên...</p>
          </div>
          <div v-else class="py-8 text-center">
            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3 border border-gray-100">
              <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
              </svg>
            </div>
            <p class="text-sm font-semibold text-gray-500">Kéo thả hoặc <span class="text-[#d63015] hover:underline">chọn file âm thanh</span></p>
            <p class="text-xs text-gray-400 mt-1">MP3, WAV, OGG, M4A – tối đa 50 MB</p>
          </div>
        </div>

        <p v-if="audioUploadError" class="text-xs text-red-500 mt-1">{{ audioUploadError }}</p>
      </div>

      <!-- Content -->
      <div class="space-y-2">
        <label class="label mb-0">
          {{ form.type === 'listening' ? 'Câu hỏi cho đoạn nghe' : 'Nội dung câu hỏi' }}
          <span class="text-red-500">*</span>
        </label>

        <!-- Dynamic Subject-Specific Toolkit (Hộp công cụ trợ lý môn học) -->
        <div v-if="form.type !== 'listening' && currentSubjectCode" class="border rounded-2xl p-4 bg-gray-50/50 shadow-sm transition-all duration-200" :class="subjectPanelClass">
          <div class="flex items-center gap-2 mb-3">
            <span class="text-base">{{ subjectIcon }}</span>
            <span class="font-bold text-xs text-gray-700">Trợ lý soạn thảo môn {{ subjectTitle }}</span>
            <span class="text-[10px] font-semibold text-gray-400 bg-white border border-gray-150 px-2 py-0.5 rounded-lg ml-auto">Tiện ích thông minh</span>
          </div>
          
          <!-- 1. MATH / PHYSICS -->
          <div v-if="currentSubjectCode === 'MATH' || currentSubjectCode === 'PHYSICS'" class="space-y-2">
            <p class="text-[11px] text-gray-500 font-medium">Click chọn ký hiệu để chèn nhanh vào vị trí con trỏ:</p>
            <div class="flex flex-wrap gap-1">
              <button
                v-for="sym in mathSymbolsList"
                :key="sym.label"
                type="button"
                @click="insertSymbol(sym.value)"
                class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-white hover:bg-gray-100 text-gray-650 border border-gray-200 transition-all cursor-pointer shadow-sm"
                :title="sym.title"
              >
                {{ sym.label }}
              </button>
            </div>
            <p class="text-[10px] text-blue-600 font-medium">💡 Gợi ý: Bọc công thức trong dấu $ để hiển thị đẹp. Ví dụ: $\Delta = b^2 - 4ac$.</p>
          </div>

          <!-- 2. CHEMISTRY -->
          <div v-else-if="currentSubjectCode === 'CHEMISTRY'" class="space-y-3">
            <div>
              <p class="text-[11px] text-gray-500 font-medium mb-1.5">Ký hiệu phản ứng & trạng thái hóa học:</p>
              <div class="flex flex-wrap gap-1">
                <button
                  v-for="sym in chemSymbolsList"
                  :key="sym.label"
                  type="button"
                  @click="insertSymbol(sym.value)"
                  class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-white hover:bg-gray-100 text-gray-650 border border-gray-200 transition-all cursor-pointer shadow-sm"
                  :title="sym.title"
                >
                  {{ sym.label }}
                </button>
              </div>
            </div>
            
            <div class="pt-2 border-t border-dashed border-amber-200/60">
              <label class="block text-[11px] font-bold text-gray-700 mb-1">🧪 Bộ chuyển đổi nhanh phương trình Hóa học:</label>
              <div class="flex gap-2">
                <input
                  v-model="chemFormulaInput"
                  type="text"
                  placeholder="Nhập nhanh (VD: Fe + CuSO4 -> FeSO4 + Cu)..."
                  class="flex-1 px-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white"
                  @keyup.enter="insertChemFormula"
                />
                <button
                  type="button"
                  @click="insertChemFormula"
                  class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs shadow-sm transition-colors cursor-pointer"
                >
                  Chèn
                </button>
              </div>
              <p v-if="chemFormulaInput" class="text-[10px] text-gray-400 mt-1">
                Xem trước LaTeX: <span class="font-mono text-amber-700">{{ formattedChemLatex }}</span>
              </p>
            </div>
          </div>

          <!-- 3. ENGLISH -->
          <div v-else-if="currentSubjectCode === 'ENGLISH'" class="space-y-2">
            <p class="text-[11px] text-gray-500 font-medium">Bàn phím phiên âm quốc tế IPA (dành cho phát âm/trọng âm):</p>
            <div class="space-y-1.5">
              <div class="flex flex-wrap gap-1">
                <span class="text-[9px] font-bold text-gray-400 self-center mr-1">Nguyên âm:</span>
                <button
                  v-for="ipa in ipaVowels"
                  :key="ipa"
                  type="button"
                  @click="insertSymbol(ipa)"
                  class="w-6 h-6 rounded bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 font-medium text-xs flex items-center justify-center cursor-pointer shadow-sm"
                >
                  {{ ipa }}
                </button>
              </div>
              <div class="flex flex-wrap gap-1">
                <span class="text-[9px] font-bold text-gray-400 self-center mr-1">Phụ âm:</span>
                <button
                  v-for="ipa in ipaConsonants"
                  :key="ipa"
                  type="button"
                  @click="insertSymbol(ipa)"
                  class="w-6 h-6 rounded bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 font-medium text-xs flex items-center justify-center cursor-pointer shadow-sm"
                >
                  {{ ipa }}
                </button>
              </div>
            </div>
          </div>

          <!-- 4. LITERATURE -->
          <div v-else-if="currentSubjectCode === 'LITERATURE'" class="space-y-3">
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">📝 Bộ định dạng trích dẫn thơ, văn bản ngắn:</label>
              <textarea
                v-model="literatureQuoteInput"
                rows="2"
                placeholder="Nhập đoạn thơ hoặc văn bản muốn trích dẫn..."
                class="w-full px-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 bg-white resize-none"
              ></textarea>
              <div class="flex justify-end mt-1.5">
                <button
                  type="button"
                  @click="insertLiteratureQuote"
                  class="px-3 py-1.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-semibold text-xs shadow-sm transition-colors cursor-pointer"
                >
                  Chèn dạng Trích dẫn
                </button>
              </div>
            </div>
          </div>

          <!-- 5. INFORMATICS -->
          <div v-else-if="currentSubjectCode === 'INFORMATICS'" class="space-y-2">
            <p class="text-[11px] text-gray-500 font-medium">Định dạng mã nguồn (Code Block):</p>
            <div class="flex flex-wrap gap-1">
              <button
                v-for="lang in ['python', 'cpp', 'java', 'javascript', 'html']"
                :key="lang"
                type="button"
                @click="insertCodeBlock(lang)"
                class="px-3 py-1 rounded-xl text-xs font-semibold bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 transition-all cursor-pointer shadow-sm uppercase animate-fade-in"
              >
                {{ lang === 'cpp' ? 'C++' : lang === 'javascript' ? 'JS' : lang }}
              </button>
            </div>
          </div>

          <!-- 6. HISTORY -->
          <div v-else-if="currentSubjectCode === 'HISTORY'" class="space-y-2">
            <label class="block text-[11px] font-bold text-gray-700">⏳ Tạo mốc sự kiện lịch sử (Timeline Event):</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <input
                v-model="historyYearInput"
                type="text"
                placeholder="Năm/Mốc..."
                class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white"
              />
              <input
                v-model="historyEventInput"
                type="text"
                placeholder="Sự kiện lịch sử..."
                class="sm:col-span-2 px-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white"
              />
            </div>
            <div class="flex justify-end mt-1.5">
              <button
                type="button"
                @click="insertHistoryTimeline"
                class="px-3 py-1.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm transition-colors cursor-pointer"
              >
                Chèn mốc thời gian
              </button>
            </div>
          </div>

          <!-- 7. GEOGRAPHY -->
          <div v-else-if="currentSubjectCode === 'GEOGRAPHY'" class="space-y-2">
            <p class="text-[11px] text-gray-500 font-medium">🗺️ Trợ lý Địa lý:</p>
            <p class="text-xs text-cyan-700 font-medium">💡 Gợi ý: Hãy tải lên bản đồ, biểu đồ nhiệt độ/lượng mưa bằng khung **Tải ảnh minh họa** phía dưới.</p>
          </div>
        </div>

        <textarea ref="contentTextareaRef" v-model="form.content" class="input resize-none" rows="3"
          :placeholder="contentPlaceholder"
          required />

        <!-- Image Uploader Card -->
        <div v-if="form.type !== 'listening'" class="mt-2.5">
          <!-- Uploaded image preview -->
          <div v-if="imageUrl" class="relative rounded-2xl border border-gray-200 bg-gray-50 p-3 max-w-md shadow-sm">
            <img :src="imageUrl" class="max-h-48 object-contain rounded-xl mx-auto" />
            <button type="button" @click="removeImage"
              class="absolute top-2 right-2 p-1.5 rounded-xl bg-white/90 hover:bg-red-50 text-gray-500 hover:text-red-500 transition-all border border-gray-100 shadow-sm cursor-pointer">
              <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          
          <!-- Image upload button -->
          <div v-else
            @click="imageInputRef?.click()"
            class="flex items-center gap-3 px-4 py-3 rounded-2xl border-2 border-dashed border-gray-200 hover:border-[#d63015] hover:bg-red-50/5 transition-all cursor-pointer shadow-sm bg-white"
          >
            <input ref="imageInputRef" type="file" accept="image/*" class="hidden" @change="onImageSelect" />
            <div class="w-10 h-10 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 shrink-0">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-gray-700">Đính kèm hình ảnh minh họa (dành cho Toán hình, sơ đồ, bản đồ...)</p>
              <p class="text-[10px] text-gray-400 font-medium">Hỗ trợ JPG, PNG, WEBP, SVG – tối đa 10 MB</p>
            </div>
            <div v-if="imageUploading" class="animate-spin w-5 h-5 border-2 border-[#d63015] border-t-transparent rounded-full shrink-0" />
          </div>
          <p v-if="imageUploadError" class="text-xs text-red-500 mt-1">{{ imageUploadError }}</p>
        </div>
      </div>

      <!-- ── Options: MC / MS / TF / ordering / listening ────────────────── -->
      <div v-if="needsOptions" class="space-y-3">
        <div class="flex items-center justify-between">
          <label class="label mb-0">Đáp án lựa chọn</label>
          <button v-if="form.type !== 'true_false'" type="button" @click="addOption"
            class="text-xs text-[#d63015] hover:underline font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm đáp án
          </button>
        </div>

        <!-- True/False layout (2 big cards) -->
        <div v-if="form.type === 'true_false'" class="grid grid-cols-2 gap-4">
          <button
            type="button"
            @click="singleAnswer = '0'"
            class="flex items-center justify-center gap-3 p-4 rounded-2xl border-2 font-semibold text-sm transition-all cursor-pointer shadow-sm"
            :class="singleAnswer === '0' ? 'border-green-400 bg-green-50 text-green-700 ring-4 ring-green-50' : 'border-gray-200 text-gray-600 hover:border-green-300 bg-white'"
          >
            <span class="w-8 h-8 rounded-full bg-green-500 text-white text-sm font-bold flex items-center justify-center">T</span>
            Đúng
          </button>
          <button
            type="button"
            @click="singleAnswer = '1'"
            class="flex items-center justify-center gap-3 p-4 rounded-2xl border-2 font-semibold text-sm transition-all cursor-pointer shadow-sm"
            :class="singleAnswer === '1' ? 'border-red-400 bg-red-50 text-red-700 ring-4 ring-red-50' : 'border-gray-200 text-gray-600 hover:border-red-300 bg-white'"
          >
            <span class="w-8 h-8 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center">F</span>
            Sai
          </button>
        </div>

        <!-- Other option cards (MC, MS, Ordering, Listening) -->
        <div v-else class="space-y-2.5">
          <div
            v-for="(opt, i) in form.options"
            :key="i"
            class="flex items-center gap-3 p-3 rounded-2xl border transition-all"
            :class="isOptionCorrect(i) ? 'border-green-400 bg-green-50/30 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300'"
          >
            <!-- Checkbox / Radio / Number indicator -->
            <div class="flex items-center justify-center shrink-0">
              <input
                v-if="form.type === 'multiple_choice' || form.type === 'listening'"
                type="radio"
                :value="String(i)"
                v-model="singleAnswer"
                class="w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300 accent-green-600 cursor-pointer"
                title="Chọn đây là đáp án đúng"
              />
              <input
                v-else-if="form.type === 'multiple_select'"
                type="checkbox"
                :value="String(i)"
                v-model="multiAnswer"
                class="w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300 rounded accent-green-600 cursor-pointer"
              />
              <span v-else-if="form.type === 'ordering'" class="w-6 h-6 rounded-lg bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500">
                {{ i + 1 }}
              </span>
            </div>

            <!-- Character indicator A, B, C... -->
            <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0 transition-colors"
              :class="isOptionCorrect(i) ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-500'">
              {{ String.fromCharCode(65 + i) }}
            </span>

            <!-- Option Text -->
            <input v-model="form.options[i]" class="flex-1 min-w-0 bg-transparent border-0 p-0 focus:ring-0 focus:outline-none text-sm text-gray-700 placeholder-gray-400"
              :placeholder="`Đáp án ${String.fromCharCode(65 + i)}`"/>

            <!-- Delete option -->
            <button type="button" @click="removeOption(i)"
              class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
              <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <p class="text-xs text-gray-500 mt-1.5 font-medium">
          <template v-if="form.type === 'listening'">Nhấn vào nút chọn hình tròn bên trái để đánh dấu đáp án đúng.</template>
          <template v-else-if="form.type === 'multiple_choice'">Nhấn vào nút chọn hình tròn bên trái để đánh dấu đáp án đúng.</template>
          <template v-else-if="form.type === 'multiple_select'">Nhấn tích vào các ô vuông bên trái để đánh dấu đáp án đúng (có thể chọn nhiều).</template>
          <template v-else-if="form.type === 'ordering'">Thứ tự hiển thị ở trên là thứ tự <strong>đúng</strong>. Khi làm bài, hệ thống sẽ trộn ngẫu nhiên.</template>
        </p>
      </div>

      <!-- ── Matching pairs ── -->
      <div v-if="form.type === 'matching'" class="space-y-3">
        <div class="flex items-center justify-between mb-2">
          <label class="label mb-0">Cặp ghép đôi <span class="text-red-500">*</span></label>
          <button type="button" @click="addPair" class="text-xs text-[#d63015] hover:underline font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm cặp
          </button>
        </div>
        <div class="space-y-3">
          <div v-for="(pair, i) in matchingPairs" :key="i" class="flex items-center gap-3 p-3 rounded-2xl border border-gray-200 bg-gray-50/50">
            <span class="w-7 h-7 rounded-full bg-pink-100 text-pink-600 text-xs font-bold flex items-center justify-center shrink-0">
              {{ i + 1 }}
            </span>
            <input v-model="pair.left" class="input flex-1 py-2" :placeholder="`Vế trái ${i + 1}`" />
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0 text-gray-400 shadow-sm border border-gray-200">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
              </svg>
            </div>
            <input v-model="pair.right" class="input flex-1 py-2" :placeholder="`Vế phải ${i + 1}`" />
            <button type="button" @click="removePair(i)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-550 hover:bg-red-50 transition-colors shrink-0">
              <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-1.5 font-medium">Vế phải sẽ được trộn ngẫu nhiên khi thi.</p>
      </div>

      <!-- ── Correct answer: fill_blank ── -->
      <div v-if="form.type === 'fill_blank'" class="space-y-3">
        <div class="flex items-center justify-between mb-2">
          <label class="label mb-0">Đáp án theo thứ tự chỗ trống <span class="text-red-500">*</span></label>
          <button type="button" @click="addFillAnswer" class="text-xs text-[#d63015] hover:underline font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm chỗ trống
          </button>
        </div>
        <div class="space-y-2.5">
          <div v-for="(_, i) in fillAnswers" :key="i" class="flex items-center gap-3 p-3 rounded-2xl border border-gray-200 bg-gray-50/50">
            <span class="shrink-0 w-24 text-xs font-bold text-amber-700 bg-amber-100/80 rounded-xl px-3 py-2 text-center shadow-sm">
              Chỗ [{{ i + 1 }}]
            </span>
            <input v-model="fillAnswers[i]" class="input flex-1 py-2"
              :placeholder="`Nhập đáp án cho chỗ trống thứ ${i + 1}...`" />
            <button v-if="fillAnswers.length > 1" type="button" @click="removeFillAnswer(i)"
              class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
              <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-1.5 font-medium">
          Trong nội dung câu hỏi, dùng <code class="bg-gray-100 px-1 rounded font-semibold text-gray-700">[1]</code>, <code class="bg-gray-100 px-1 rounded font-semibold text-gray-700">[2]</code>... để đánh dấu vị trí chỗ trống tương ứng.
        </p>
      </div>

      <!-- ── Correct answer: short_answer ── -->
      <div v-if="form.type === 'short_answer'" class="space-y-1.5">
        <label class="label">Đáp án mẫu</label>
        <input v-model="textAnswer" class="input" placeholder="Đáp án mẫu..." />
        <p class="text-xs text-gray-500 mt-1 font-medium">Dùng để tham khảo khi chấm, không tự động chấm điểm.</p>
      </div>

      <!-- Explanation -->
      <div>
        <label class="label">Giải thích đáp án <span class="text-gray-400 font-normal">(tuỳ chọn)</span></label>
        <textarea v-model="form.explanation" class="input resize-none" rows="2"
          placeholder="Giải thích tại sao đây là đáp án đúng..." />
      </div>

      <div v-if="error" class="text-sm text-red-600 bg-red-55/10 p-3 rounded-2xl border border-red-100 shadow-sm">{{ error }}</div>
    </form>

    <template #footer>
      <button @click="show = false" class="px-5 py-2.5 text-sm rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors font-medium">
        Hủy
      </button>
      <button @click="submit" :disabled="saving || audioUploading"
        class="px-5 py-2.5 text-sm rounded-xl bg-[#7c3aed] hover:bg-[#6d28d9] text-white font-semibold disabled:opacity-60 transition-colors shadow-sm cursor-pointer">
        {{ saving ? 'Đang lưu...' : (editItem ? 'Cập nhật' : 'Thêm câu hỏi') }}
      </button>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import AppModal from '@components/common/AppModal.vue'
import api from '@api/axios'
import { useAuthStore } from '@stores/auth'

const auth = useAuthStore()

function stripHtml(html) {
  return html ? html.replace(/<[^>]*>/g, '').trim() : ''
}

const props = defineProps({
  modelValue: Boolean,
  editItem: { type: Object, default: null },
  saving: Boolean,
  error: { type: String, default: '' },
  subjectId: { type: [Number, String], default: null },
  subjectCode: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue', 'save'])

const show = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

// ── Types that show options list ──────────────────────────────────────────
const NEEDS_OPTIONS_TYPES = ['multiple_choice', 'multiple_select', 'true_false', 'ordering', 'listening']

// ── Basic refs / reactive state ───────────────────────────────────────────
const form = reactive({
  type: 'multiple_choice',
  content: '',
  difficulty: 'medium',
  chapter_tag: '',
  points: 1,
  options: ['', '', '', ''],
  explanation: '',
  media_type: null,
  media_path: null,
})

const singleAnswer  = ref('0')
const multiAnswer   = ref([])
const textAnswer    = ref('')       // short_answer only
const fillAnswers   = ref([''])     // fill_blank: one entry per blank
const matchingPairs = ref([{ left: '', right: '' }, { left: '', right: '' }])

// ── Audio state ───────────────────────────────────────────────────────────
const fileInputRef    = ref(null)
const audioPath       = ref('')   // stored path (goes to audio_path field)
const audioUrl        = ref('')   // preview URL
const audioFileName   = ref('')
const audioUploading  = ref(false)
const audioUploadError = ref('')
const isDragging      = ref(false)

// ── Image state ───────────────────────────────────────────────────────────
const imageInputRef   = ref(null)
const imageUrl        = ref('')
const imageUploading  = ref(false)
const imageUploadError = ref('')

// ── Other refs / inputs ───────────────────────────────────────────────────
const contentTextareaRef = ref(null)
const chemFormulaInput = ref('')
const literatureQuoteInput = ref('')
const historyYearInput = ref('')
const historyEventInput = ref('')

// ── Subject groups configuration ──────────────────────────────────────────
const subjectTypeGroups = {
  MATH: [
    {
      label: 'Cơ bản',
      options: [
        { value: 'multiple_choice', label: 'Trắc nghiệm chọn 1' },
        { value: 'multiple_select', label: 'Trắc nghiệm chọn nhiều' },
        { value: 'true_false', label: 'Câu hỏi Đúng / Sai' }
      ]
    },
    {
      label: 'Tự luận & Tính toán',
      options: [
        { value: 'fill_blank', label: 'Điền vào chỗ trống' },
        { value: 'short_answer', label: 'Trả lời ngắn' },
        { value: 'calculation', label: 'Tính toán sai số' },
        { value: 'multi_step', label: 'Bài toán nhiều bước' },
        { value: 'essay', label: 'Tự luận giải chi tiết' }
      ]
    }
  ],
  PHYSICS: [
    {
      label: 'Trắc nghiệm',
      options: [
        { value: 'multiple_choice', label: 'Trắc nghiệm chọn 1' },
        { value: 'multiple_select', label: 'Trắc nghiệm chọn nhiều' },
        { value: 'true_false', label: 'Câu hỏi Đúng / Sai' }
      ]
    },
    {
      label: 'Tự luận & Ứng dụng',
      options: [
        { value: 'fill_blank', label: 'Điền vào chỗ trống' },
        { value: 'calculation', label: 'Tính toán sai số' },
        { value: 'experiment', label: 'Câu hỏi thí nghiệm Vật lý' },
        { value: 'multi_step', label: 'Bài tập nhiều bước' },
        { value: 'essay', label: 'Tự luận' }
      ]
    }
  ],
  CHEMISTRY: [
    {
      label: 'Trắc nghiệm & Tương tác',
      options: [
        { value: 'multiple_choice', label: 'Trắc nghiệm chọn 1' },
        { value: 'multiple_select', label: 'Trắc nghiệm chọn nhiều' },
        { value: 'true_false', label: 'Câu hỏi Đúng / Sai' },
        { value: 'matching', label: 'Ghép đôi (Nối chất/phản ứng)' }
      ]
    },
    {
      label: 'Tự luận & Thực hành',
      options: [
        { value: 'fill_blank', label: 'Điền vào chỗ trống' },
        { value: 'calculation', label: 'Tính toán hóa số' },
        { value: 'experiment', label: 'Thí nghiệm Hóa học' },
        { value: 'essay', label: 'Tự luận' }
      ]
    }
  ],
  ENGLISH: [
    {
      label: 'Trắc nghiệm & Tương tác',
      options: [
        { value: 'multiple_choice', label: 'Chọn 1 đáp án đúng' },
        { value: 'multiple_select', label: 'Chọn nhiều đáp án đúng' },
        { value: 'true_false', label: 'Đúng / Sai (True / False)' },
        { value: 'ordering', label: 'Sắp xếp câu/sắp xếp từ' },
        { value: 'matching', label: 'Nối từ / nối cặp nghĩa' }
      ]
    },
    {
      label: 'Kỹ năng ngôn ngữ',
      options: [
        { value: 'reading', label: 'Reading (Đọc hiểu)' },
        { value: 'listening', label: 'Listening (Nghe hiểu)' },
        { value: 'speaking', label: 'Speaking (Nói/Ghi âm)' },
        { value: 'writing', label: 'Writing (Viết luận/Viết câu)' },
        { value: 'fill_blank', label: 'Điền từ vào chỗ trống' },
        { value: 'short_answer', label: 'Trả lời ngắn' }
      ]
    }
  ],
  LITERATURE: [
    {
      label: 'Đọc hiểu & Viết',
      options: [
        { value: 'reading', label: 'Đọc hiểu tác phẩm' },
        { value: 'writing', label: 'Viết đoạn văn / bài luận' },
        { value: 'essay', label: 'Bài làm tự luận Văn học' }
      ]
    },
    {
      label: 'Trắc nghiệm & Tương tác',
      options: [
        { value: 'multiple_choice', label: 'Trắc nghiệm chọn 1' },
        { value: 'true_false', label: 'Đúng / Sai' },
        { value: 'fill_blank', label: 'Điền khuyết (từ còn thiếu)' },
        { value: 'ordering', label: 'Sắp xếp diễn biến sự kiện' }
      ]
    }
  ],
  HISTORY: [
    {
      label: 'Trắc nghiệm & Tương tác',
      options: [
        { value: 'multiple_choice', label: 'Trắc nghiệm chọn 1' },
        { value: 'multiple_select', label: 'Trắc nghiệm chọn nhiều' },
        { value: 'true_false', label: 'Đúng / Sai' },
        { value: 'ordering', label: 'Sắp xếp thứ tự thời gian' },
        { value: 'matching', label: 'Nối mốc lịch sử & Sự kiện' }
      ]
    },
    {
      label: 'Phân tích & Tự luận',
      options: [
        { value: 'case_study', label: 'Tình huống lịch sử' },
        { value: 'fill_blank', label: 'Điền khuyết sự kiện' },
        { value: 'essay', label: 'Tự luận lịch sử' }
      ]
    }
  ],
  GEOGRAPHY: [
    {
      label: 'Khai thác Bản đồ & Biểu đồ',
      options: [
        { value: 'map_analysis', label: 'Đọc & Phân tích bản đồ' },
        { value: 'chart_analysis', label: 'Phân tích biểu đồ địa lý' },
        { value: 'drag_drop', label: 'Kéo thả phân loại vùng miền' }
      ]
    },
    {
      label: 'Trắc nghiệm & Tự luận',
      options: [
        { value: 'multiple_choice', label: 'Trắc nghiệm chọn 1' },
        { value: 'true_false', label: 'Đúng / Sai' },
        { value: 'fill_blank', label: 'Điền khuyết Địa lý' },
        { value: 'essay', label: 'Tự luận phân tích' }
      ]
    }
  ],
  BIOLOGY: [
    {
      label: 'Trắc nghiệm & Nhận biết',
      options: [
        { value: 'multiple_choice', label: 'Trắc nghiệm chọn 1' },
        { value: 'multiple_select', label: 'Trắc nghiệm chọn nhiều' },
        { value: 'true_false', label: 'Đúng / Sai' },
        { value: 'drag_drop', label: 'Kéo thả chú thích sơ đồ tế bào' }
      ]
    },
    {
      label: 'Thực hành & Tự luận',
      options: [
        { value: 'experiment', label: 'Thí nghiệm Sinh học' },
        { value: 'fill_blank', label: 'Điền khuyết chú thích' },
        { value: 'essay', label: 'Tự luận' }
      ]
    }
  ],
  INFORMATICS: [
    {
      label: 'Lập trình & Thuật toán',
      options: [
        { value: 'code_runner', label: 'Viết code chạy test case' },
        { value: 'code_fill', label: 'Điền code vào chỗ trống' },
        { value: 'code_debug', label: 'Tìm và sửa lỗi code' },
        { value: 'code_output', label: 'Đọc hiểu & dự đoán Output' }
      ]
    },
    {
      label: 'Kiến thức lý thuyết',
      options: [
        { value: 'multiple_choice', label: 'Trắc nghiệm chọn 1' },
        { value: 'multiple_select', label: 'Trắc nghiệm chọn nhiều' },
        { value: 'true_false', label: 'Đúng / Sai' },
        { value: 'fill_blank', label: 'Điền khuyết thuật ngữ' },
        { value: 'essay', label: 'Tự luận thuật toán' }
      ]
    }
  ],
  DEFAULT: [
    {
      label: 'Trắc nghiệm',
      options: [
        { value: 'multiple_choice', label: 'Trắc nghiệm chọn 1' },
        { value: 'multiple_select', label: 'Trắc nghiệm chọn nhiều' },
        { value: 'true_false', label: 'Đúng / Sai' }
      ]
    },
    {
      label: 'Tự luận & Tương tác',
      options: [
        { value: 'fill_blank', label: 'Điền vào chỗ trống' },
        { value: 'short_answer', label: 'Trả lời ngắn' },
        { value: 'essay', label: 'Tự luận' },
        { value: 'ordering', label: 'Sắp xếp thứ tự' },
        { value: 'matching', label: 'Ghép đôi' }
      ]
    }
  ]
}

const MODAL_SUPPORTED_TYPES = [
  'multiple_choice', 'multiple_select', 'true_false',
  'essay', 'short_answer', 'fill_blank',
  'ordering', 'matching', 'listening', 'reading'
]

// ── Computed properties ───────────────────────────────────────────────────
const currentSubjectCode = computed(() => {
  if (props.subjectCode) return props.subjectCode.toUpperCase()
  if (props.editItem?.subject?.code) return props.editItem.subject.code.toUpperCase()
  return ''
})

const subjectIcon = computed(() => {
  const code = currentSubjectCode.value
  if (code === 'MATH') return '📐'
  if (code === 'PHYSICS') return '⚡'
  if (code === 'CHEMISTRY') return '🧪'
  if (code === 'ENGLISH') return '🇬🇧'
  if (code === 'LITERATURE') return '📝'
  if (code === 'HISTORY') return '⏳'
  if (code === 'GEOGRAPHY') return '🗺️'
  if (code === 'INFORMATICS') return '💻'
  return '💡'
})

const subjectTitle = computed(() => {
  const code = currentSubjectCode.value
  if (code === 'MATH') return 'Toán học'
  if (code === 'PHYSICS') return 'Vật lý'
  if (code === 'CHEMISTRY') return 'Hóa học'
  if (code === 'ENGLISH') return 'Tiếng Anh'
  if (code === 'LITERATURE') return 'Ngữ văn'
  if (code === 'HISTORY') return 'Lịch sử'
  if (code === 'GEOGRAPHY') return 'Địa lý'
  if (code === 'INFORMATICS') return 'Tin học'
  return 'Công cụ hỗ trợ'
})

const subjectPanelClass = computed(() => {
  const code = currentSubjectCode.value
  if (code === 'MATH') return 'border-blue-200 bg-blue-50/20'
  if (code === 'PHYSICS') return 'border-purple-200 bg-purple-50/20'
  if (code === 'CHEMISTRY') return 'border-amber-200 bg-amber-50/20'
  if (code === 'ENGLISH') return 'border-emerald-200 bg-emerald-50/20'
  if (code === 'LITERATURE') return 'border-rose-200 bg-rose-50/20'
  if (code === 'HISTORY') return 'border-orange-200 bg-orange-50/20'
  if (code === 'GEOGRAPHY') return 'border-cyan-200 bg-cyan-50/20'
  if (code === 'INFORMATICS') return 'border-indigo-200 bg-indigo-50/20'
  return 'border-gray-200 bg-gray-50/40'
})

const groupedQuestionTypes = computed(() => {
  const code = currentSubjectCode.value
  const rawGroups = subjectTypeGroups[code] || subjectTypeGroups.DEFAULT
  
  return rawGroups.map(group => {
    return {
      label: group.label,
      options: group.options.filter(opt => MODAL_SUPPORTED_TYPES.includes(opt.value))
    }
  }).filter(group => group.options.length > 0)
})

const needsOptions = computed(() => NEEDS_OPTIONS_TYPES.includes(form.type))

const formattedChemLatex = computed(() => {
  if (!chemFormulaInput.value) return ''
  let res = chemFormulaInput.value
    .replace(/-->|->/g, '\\rightarrow')
    .replace(/<->|<=>/g, '\\rightleftharpoons')
    .replace(/\+/g, ' + ')
    .replace(/\s+/g, ' ')
    .replace(/([a-zA-Z\)])(\d+)/g, '$1_$2')
  return `$${res.trim()}$`
})

const contentPlaceholder = computed(() => {
  if (form.type === 'listening')  return 'VD: Người nói đang làm gì trong đoạn hội thoại?'
  if (form.type === 'fill_blank') return 'VD: Hà Nội là thủ đô của [1], có diện tích [2] km²'
  return 'Nhập nội dung câu hỏi...'
})

// Difficulties Grid configuration
const difficultiesList = [
  { value: 'easy',   label: 'Dễ',  active: 'border-green-500 bg-green-50 text-green-700 font-bold ring-4 ring-green-50/50' },
  { value: 'medium', label: 'Trung bình',  active: 'border-amber-500 bg-amber-50 text-amber-700 font-bold ring-4 ring-amber-50/50' },
  { value: 'hard',   label: 'Khó', active: 'border-red-500 bg-red-50 text-red-700 font-bold ring-4 ring-red-50/50' },
]

// ── Symbol Toolkits Lists ─────────────────────────────────────────────────
const mathSymbolsList = [
  { label: 'a/b', value: '$\\frac{a}{b}$', title: 'Phân số' },
  { label: '√x', value: '$\\sqrt{x}$', title: 'Căn bậc hai' },
  { label: 'x^y', value: '$x^y$', title: 'Lũy thừa' },
  { label: 'x_i', value: '$x_i$', title: 'Chỉ số dưới' },
  { label: 'π', value: '$\\pi$', title: 'Số Pi' },
  { label: 'Δ', value: '$\\Delta$', title: 'Delta' },
  { label: '∞', value: '$\\infty$', title: 'Vô cùng' },
  { label: '±', value: '$\\pm$', title: 'Cộng trừ' },
  { label: '°C', value: '$^\\circ\\text{C}$', title: 'Nhiệt độ' },
  { label: 'sin(x)', value: '$\\sin(x)$', title: 'Hàm sin' },
  { label: 'cos(x)', value: '$\\cos(x)$', title: 'Hàm cos' },
  { label: 'tan(x)', value: '$\\tan(x)$', title: 'Hàm tan' },
]

const chemSymbolsList = [
  { label: '→', value: '$\\rightarrow$', title: 'Phản ứng 1 chiều' },
  { label: '⇌', value: '$\\rightleftharpoons$', title: 'Phản ứng thuận nghịch' },
  { label: '↑', value: '$\\uparrow$', title: 'Chất khí sinh ra' },
  { label: '↓', value: '$\\downarrow$', title: 'Chất kết tủa' },
  { label: 't°', value: '$t^\\circ$', title: 'Nhiệt độ' },
  { label: '+', value: ' $+$ ', title: 'Dấu cộng phản ứng' },
  { label: 'H2O', value: '$H_2O$', title: 'Nước' },
  { label: 'CO2', value: '$CO_2$', title: 'Cacbonic' },
  { label: 'O2', value: '$O_2$', title: 'Oxi' },
  { label: 'H2SO4', value: '$H_2SO_4$', title: 'Axit sunfuric' },
]

const ipaVowels = ['æ', 'ɑ:', 'ɒ', 'ɔ:', 'ə', 'ɜ:', 'ɪ', 'i:', 'ʌ', 'u:', 'ʊ', 'eɪ', 'aɪ', 'ɔɪ', 'aʊ', 'oʊ', 'ɪə', 'eə', 'ʊə']
const ipaConsonants = ['θ', 'ð', 'ʃ', 'ʒ', 'tʃ', 'dʒ', 'ŋ']

// ── Watchers ──────────────────────────────────────────────────────────────
watch(groupedQuestionTypes, (newGroups) => {
  const allAvailableTypes = newGroups.flatMap(g => g.options.map(o => o.value))
  if (allAvailableTypes.length && !allAvailableTypes.includes(form.type)) {
    form.type = allAvailableTypes[0]
    onTypeChange()
  }
})

watch(() => props.editItem, (item) => {
  if (!item) { resetForm(); return }

  form.type        = item.type ?? 'multiple_choice'
  form.content     = stripHtml(item.content ?? '')
  form.difficulty  = item.difficulty ?? 'medium'
  form.chapter_tag = item.chapter_tag ?? ''
  form.points      = item.points ?? 1
  form.explanation = item.explanation ?? ''

  // Reset audio state first
  audioPath.value  = ''
  audioUrl.value   = ''
  audioFileName.value = ''

  // Reset image state first
  imageUrl.value  = ''
  imageUploadError.value = ''
  form.media_type = null
  form.media_path = null

  if (item.media_type === 'image' && item.media_path) {
    form.media_type = 'image'
    form.media_path = item.media_path
    imageUrl.value  = '/storage/' + item.media_path
  }

  const type = item.type

  if (NEEDS_OPTIONS_TYPES.includes(type)) {
    form.options = Array.isArray(item.options) ? [...item.options] : ['', '', '', '']
    if (type === 'multiple_choice' || type === 'true_false' || type === 'listening') {
      const ans = item.correct_answer
      singleAnswer.value = Array.isArray(ans) ? (ans[0] ?? '0') : (ans ?? '0')
    } else if (type === 'multiple_select') {
      multiAnswer.value = Array.isArray(item.correct_answer) ? [...item.correct_answer] : []
    }

    // Restore audio when editing a listening question
    if (type === 'listening' && item.audio_path) {
      audioPath.value    = item.audio_path
      audioUrl.value     = '/storage/' + item.audio_path
      audioFileName.value = item.audio_path.split('/').pop()
    }
  } else if (type === 'matching') {
    const left = item.options?.left ?? []
    const right = item.options?.right ?? []
    const ans   = item.correct_answer ?? {}
    matchingPairs.value = left.map((l, i) => ({
      left: l,
      right: right[ans[String(i)] ?? i] ?? right[i] ?? '',
    }))
    if (!matchingPairs.value.length) matchingPairs.value = [{ left: '', right: '' }]
  } else if (type === 'fill_blank') {
    const ans = item.correct_answer
    fillAnswers.value = Array.isArray(ans) && ans.length ? [...ans] : ['']
  } else if (type === 'short_answer') {
    textAnswer.value = Array.isArray(item.correct_answer)
      ? (item.correct_answer[0] ?? '')
      : (item.correct_answer ?? '')
  }
}, { immediate: true })

watch(() => props.modelValue, (v) => {
  if (v && !props.editItem) resetForm()
})

// ── Helper functions / Methods ────────────────────────────────────────────
function isOptionCorrect(i) {
  const indexStr = String(i)
  if (form.type === 'multiple_choice' || form.type === 'true_false' || form.type === 'listening') {
    return singleAnswer.value === indexStr
  } else if (form.type === 'multiple_select') {
    return multiAnswer.value.includes(indexStr)
  }
  return false
}

function insertChemFormula() {
  if (!formattedChemLatex.value) return
  insertSymbol(formattedChemLatex.value)
  chemFormulaInput.value = ''
}

function insertLiteratureQuote() {
  if (!literatureQuoteInput.value) return
  const formatted = `\n<blockquote class="pl-4 border-l-4 border-red-400 my-3 italic text-gray-655 whitespace-pre-line">\n${literatureQuoteInput.value.trim()}\n</blockquote>\n`
  insertSymbol(formatted)
  literatureQuoteInput.value = ''
}

function insertCodeBlock(lang) {
  const textarea = contentTextareaRef.value
  const selectLabel = lang === 'cpp' ? 'C++' : lang === 'javascript' ? 'JS' : lang
  const codeTemplate = `\n\`\`\`${lang}\n// Viết mã nguồn ${selectLabel} ở đây\n\n\`\`\`\n`
  if (!textarea) {
    form.content += codeTemplate
    return
  }
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const text = form.content
  const selection = text.substring(start, end)
  const val = `\n\`\`\`${lang}\n${selection || `// Viết mã nguồn ${selectLabel} ở đây`}\n\`\`\`\n`
  form.content = text.substring(0, start) + val + text.substring(end)
  setTimeout(() => {
    textarea.focus()
    textarea.setSelectionRange(start + val.length, start + val.length)
  }, 0)
}

function insertHistoryTimeline() {
  if (!historyYearInput.value || !historyEventInput.value) return
  const formatted = `\n<div class="flex gap-4 my-2.5 p-3 rounded-2xl bg-orange-50/50 border border-orange-100 shadow-sm"><div class="font-extrabold text-orange-600 shrink-0 text-sm">${historyYearInput.value.trim()}</div><div class="text-sm text-gray-700 font-medium">${historyEventInput.value.trim()}</div></div>\n`
  insertSymbol(formatted)
  historyYearInput.value = ''
  historyEventInput.value = ''
}

function insertSymbol(val) {
  const textarea = contentTextareaRef.value
  if (!textarea) {
    form.content += val
    return
  }
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const text = form.content
  form.content = text.substring(0, start) + val + text.substring(end)
  setTimeout(() => {
    textarea.focus()
    textarea.setSelectionRange(start + val.length, start + val.length)
  }, 0)
}

function resetForm() {
  form.type        = 'multiple_choice'
  form.content     = ''
  form.difficulty  = 'medium'
  form.chapter_tag = ''
  form.points      = 1
  form.options     = ['', '', '', '']
  form.explanation = ''
  singleAnswer.value   = '0'
  multiAnswer.value    = []
  textAnswer.value     = ''
  fillAnswers.value    = ['']
  matchingPairs.value  = [{ left: '', right: '' }, { left: '', right: '' }]
  audioPath.value      = ''
  audioUrl.value       = ''
  audioFileName.value  = ''
  audioUploadError.value = ''
  imageUrl.value       = ''
  imageUploadError.value = ''
  form.media_type      = null
  form.media_path      = null
}

function onTypeChange() {
  if (form.type === 'true_false') {
    form.options = ['Đúng', 'Sai']
    singleAnswer.value = '0'
  } else if (form.type === 'listening') {
    form.options = ['', '', '', '']
    singleAnswer.value = '0'
  } else if (NEEDS_OPTIONS_TYPES.includes(form.type) && form.options.length < 2) {
    form.options = ['', '', '', '']
  }
  multiAnswer.value    = []
  textAnswer.value     = ''
  fillAnswers.value    = ['']
  matchingPairs.value  = [{ left: '', right: '' }, { left: '', right: '' }]
  if (form.type !== 'listening') {
    audioPath.value = ''
    audioUrl.value  = ''
    audioFileName.value = ''
  }
}

// ── Audio upload helpers ──────────────────────────────────────────────────
async function uploadFile(file) {
  if (!file) return
  audioUploadError.value = ''
  audioUploading.value   = true

  // Local preview immediately
  audioUrl.value      = URL.createObjectURL(file)
  audioFileName.value = file.name

  const fd = new FormData()
  fd.append('file', file)

  const url = auth.role === 'teacher' ? '/teacher/media/audio' : '/admin/content/media/audio'
  try {
    const { data } = await api.post(url, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    audioPath.value = data.data.path
    audioUrl.value  = data.data.url  // replace blob with real URL
  } catch (e) {
    audioUploadError.value = e.response?.data?.message ?? 'Tải lên thất bại. Thử lại.'
    audioUrl.value      = ''
    audioFileName.value = ''
  } finally {
    audioUploading.value = false
  }
}

function onFileSelect(e) {
  const file = e.target.files?.[0]
  if (file) uploadFile(file)
  e.target.value = ''
}

function onDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files?.[0]
  if (file) uploadFile(file)
}

function removeAudio() {
  audioPath.value      = ''
  audioUrl.value       = ''
  audioFileName.value  = ''
  audioUploadError.value = ''
}

// ── Image upload helpers ──────────────────────────────────────────────────
async function uploadImageFile(file) {
  if (!file) return
  imageUploadError.value = ''
  imageUploading.value   = true

  imageUrl.value = URL.createObjectURL(file)

  const fd = new FormData()
  fd.append('file', file)

  const url = auth.role === 'teacher' ? '/teacher/media/image' : '/admin/content/media/image'
  try {
    const { data } = await api.post(url, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    form.media_type = 'image'
    form.media_path = data.data.path
    imageUrl.value  = data.data.url
  } catch (e) {
    imageUploadError.value = e.response?.data?.message ?? 'Tải ảnh lên thất bại. Thử lại.'
    imageUrl.value  = ''
    form.media_type = null
    form.media_path = null
  } finally {
    imageUploading.value = false
  }
}

function onImageSelect(e) {
  const file = e.target.files?.[0]
  if (file) uploadImageFile(file)
  e.target.value = ''
}

function removeImage() {
  imageUrl.value  = ''
  imageUploadError.value = ''
  form.media_type = null
  form.media_path = null
}

// ── Options helpers ───────────────────────────────────────────────────────
function addFillAnswer()    { fillAnswers.value.push('') }
function removeFillAnswer(i) { fillAnswers.value.splice(i, 1) }

// Restrict maximum options to 8 to match QuestionCreateView
function addOption() {
  if (form.options.length < 8) {
    form.options.push('')
  }
}
function removeOption(i) {
  form.options.splice(i, 1)
  singleAnswer.value = '0'
  multiAnswer.value  = multiAnswer.value
    .filter(v => Number(v) !== i)
    .map(v => Number(v) > i ? String(Number(v) - 1) : v)
}
function addPair()      { matchingPairs.value.push({ left: '', right: '' }) }
function removePair(i)  { matchingPairs.value.splice(i, 1) }

// ── Build payload ─────────────────────────────────────────────────────────
function buildPayload() {
  const base = {
    type:           form.type,
    content:        form.content,
    difficulty:     form.difficulty,
    chapter_tag:    form.chapter_tag || null,
    points:         form.points,
    explanation:    form.explanation || null,
    options:        null,
    correct_answer: null,
    audio_path:     null,
    media_type:     form.media_type || null,
    media_path:     form.media_path || null,
  }

  if (form.type === 'multiple_choice' || form.type === 'true_false') {
    base.options        = [...form.options]
    base.correct_answer = [singleAnswer.value]
  } else if (form.type === 'listening') {
    base.options        = [...form.options]
    base.correct_answer = [singleAnswer.value]
    base.audio_path     = audioPath.value || null
  } else if (form.type === 'multiple_select') {
    base.options        = [...form.options]
    base.correct_answer = [...multiAnswer.value]
  } else if (form.type === 'ordering') {
    base.options        = [...form.options]
    base.correct_answer = form.options.map((_, i) => String(i))
  } else if (form.type === 'matching') {
    const leftArr = matchingPairs.value.map(p => p.left)
    const rightArr = matchingPairs.value.map(p => p.right)
    base.options = { left: leftArr, right: rightArr }
    const ans = {}
    matchingPairs.value.forEach((_, i) => { ans[String(i)] = String(i) })
    base.correct_answer = ans
  } else if (form.type === 'fill_blank') {
    base.correct_answer = [...fillAnswers.value]
  } else if (form.type === 'short_answer') {
    base.correct_answer = [textAnswer.value]
  }

  return base
}

function submit() {
  emit('save', buildPayload())
}
</script>

<style scoped>
@reference "tailwindcss";
.input {
  @apply w-full px-4 py-2.5 rounded-2xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-[#d63015]/10 focus:border-[#d63015] text-sm bg-white transition-all shadow-sm;
}
.label {
  @apply block text-sm font-semibold text-gray-700 mb-1.5;
}
</style>
