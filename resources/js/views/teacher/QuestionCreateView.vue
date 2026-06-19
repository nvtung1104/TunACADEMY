<template>
  <div>
    <div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center gap-3">
      <RouterLink to="/teacher/question-bank"
        class="p-2 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors text-gray-500 shrink-0">
        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </RouterLink>
      <div>
        <h2 class="text-lg font-bold text-gray-900">{{ isEdit ? 'Chỉnh sửa câu hỏi' : 'Thêm câu hỏi mới' }}</h2>
        <p class="text-sm text-gray-400">{{ isEdit ? 'Cập nhật nội dung câu hỏi' : 'Tạo câu hỏi cho ngân hàng của bạn' }}</p>
      </div>
    </div>

    <div class="space-y-5">

      <!-- 1. Môn học -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <label class="block text-sm font-semibold text-gray-800">Môn học <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          <button v-for="s in subjects" :key="s.id" type="button" @click="form.subject_id = s.id"
            class="flex items-center gap-3 px-4 py-3.5 rounded-2xl border text-sm font-semibold transition-all duration-200 cursor-pointer hover:-translate-y-0.5 hover:shadow-sm"
            :class="form.subject_id === s.id ? 'border-[#d63015] bg-red-50/50 text-[#d63015] ring-4 ring-red-50/50' : 'border-gray-200 text-gray-600 bg-white hover:border-gray-300 hover:bg-gray-50/50'">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold shrink-0 text-white shadow-sm transition-transform"
              :class="form.subject_id === s.id ? 'scale-105' : ''"
              :style="{ backgroundColor: s.color || '#d63015' }">{{ s.icon || s.name?.[0] }}</span>
            {{ s.name }}
          </button>
        </div>
        <p v-if="!form.subject_id && submitted" class="text-xs text-red-500 mt-2">Vui lòng chọn môn học</p>
      </div>

      <!-- 2. Loại + Độ khó + Điểm -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <!-- Column 1: Loại câu hỏi -->
          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1.5">Loại câu hỏi <span class="text-red-500">*</span></label>
            <select v-model="form.type" class="input">
              <optgroup v-for="group in groupedQuestionTypes" :key="group.label" :label="group.label">
                <option v-for="opt in group.options" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </optgroup>
            </select>
          </div>

          <!-- Column 2: Độ khó -->
          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1.5">Độ khó</label>
            <div class="grid grid-cols-3 gap-2">
              <button v-for="d in difficulties" :key="d.value" type="button" @click="form.difficulty = d.value"
                class="py-2.5 rounded-xl border text-xs font-semibold transition-all text-center flex items-center justify-center cursor-pointer bg-white shadow-sm"
                :class="form.difficulty === d.value ? d.active : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                {{ d.label }}
              </button>
            </div>
          </div>

          <!-- Column 3: Điểm & Trạng thái chia sẻ -->
          <div class="flex flex-col gap-1.5">
            <label class="block text-sm font-semibold text-gray-800">Điểm & Chia sẻ</label>
            <div class="flex items-center gap-4 mt-1">
              <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">Điểm:</span>
                <input v-model.number="form.default_points" type="number" min="0.25" max="100" step="0.25"
                  class="w-20 px-3 py-2 rounded-xl border border-gray-200 text-sm text-center focus:outline-none focus:ring-2 focus:ring-[#d63015] bg-white shadow-sm" />
              </div>
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" v-model="form.is_public" class="w-4.5 h-4.5 rounded text-[#d63015] border-gray-300 focus:ring-[#d63015] accent-[#d63015]" />
                <span class="text-sm font-semibold text-gray-600">Chia sẻ công khai</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Nội dung -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <!-- listening: audio URL -->
        <div v-if="form.type === 'listening'" class="space-y-1.5">
          <label class="block text-sm font-semibold text-gray-800">URL âm thanh <span class="text-red-500">*</span></label>
          <input v-model="listeningAudio" class="input" placeholder="https://..." />
          <p class="text-xs text-gray-400 font-medium">Link file MP3/WAV hoặc embed từ dịch vụ lưu trữ</p>
        </div>
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-sm font-semibold text-gray-800 mb-0">
              {{ form.type === 'reading' ? 'Đoạn văn & câu hỏi' : 'Nội dung câu hỏi' }}
              <span class="text-red-500">*</span>
            </label>
          </div>

          <!-- Dynamic Subject-Specific Toolkit (Hộp công cụ trợ lý môn học) -->
          <div v-if="form.type !== 'listening' && currentSubjectCode" class="border rounded-2xl p-4 mb-4 bg-gray-50/50 shadow-sm transition-all duration-200" :class="subjectPanelClass">
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
          
          <p v-if="form.type === 'fill_blank'" class="text-xs text-gray-450 mb-1.5 font-medium">
            Dùng <code class="bg-gray-150 px-1 rounded font-bold text-gray-700">___</code> (3 dấu gạch dưới) để đánh dấu chỗ trống. Ví dụ: "Thủ đô Việt Nam là ___."
          </p>
          <p v-if="form.type === 'reading'" class="text-xs text-gray-450 mb-1.5 font-medium">Nhập đoạn văn và câu hỏi. Học sinh đọc toàn bộ nội dung này.</p>
          
          <textarea ref="contentTextareaRef" v-model="form.content" class="input resize-none" :rows="form.type === 'reading' ? 8 : 4"
            placeholder="Nhập nội dung câu hỏi..."></textarea>

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
      </div>

      <!-- 4. Đáp án — theo từng loại -->

      <!-- multiple_choice / multiple_select / reading / listening: danh sách đáp án A/B/C/D -->
      <div v-if="['multiple_choice','multiple_select','reading','listening'].includes(form.type)"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
        
        <div class="flex items-center justify-between mb-1">
          <label class="block text-sm font-semibold text-gray-800 mb-0">Các đáp án <span class="text-red-500">*</span></label>
          <button v-if="formOptions.length < 8" type="button" @click="addOption"
            class="text-xs text-[#d63015] hover:underline font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm đáp án
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="(opt, idx) in formOptions" :key="idx"
            class="flex items-center gap-3 p-3 rounded-2xl border transition-all"
            :class="isCorrectOption(opt.id) ? 'border-green-400 bg-green-50/30 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300'">
            
            <!-- Radio / Checkbox indicator inside option card -->
            <div class="flex items-center justify-center shrink-0">
              <input v-if="['multiple_choice','reading','listening'].includes(form.type)"
                type="radio"
                :value="opt.id"
                v-model="form.correct_answer"
                class="w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300 accent-green-600 cursor-pointer"
                title="Chọn đây là đáp án đúng" />
              <input v-else
                type="checkbox"
                :value="opt.id"
                v-model="multiSelectAnswer"
                class="w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300 rounded accent-green-600 cursor-pointer" />
            </div>

            <!-- ID Badge -->
            <span class="w-8 h-8 rounded-full text-sm font-bold flex items-center justify-center shrink-0 transition-colors"
              :class="isCorrectOption(opt.id) ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-500'">{{ opt.id }}</span>
            
            <!-- Option Input text -->
            <input v-model="opt.text" class="flex-1 min-w-0 bg-transparent border-0 p-0 focus:ring-0 focus:outline-none text-sm text-gray-700 placeholder-gray-400"
              :placeholder="`Nhập nội dung đáp án ${opt.id}...`" />

            <!-- Delete option -->
            <button v-if="formOptions.length > 2" type="button" @click="formOptions.splice(idx, 1)"
              class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
              <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>

        <p class="text-xs text-gray-500 font-medium">
          <template v-if="['multiple_choice','reading','listening'].includes(form.type)">
            Nhấn vào nút chọn hình tròn bên trái của phương án để đánh dấu đáp án đúng.
          </template>
          <template v-else>
            Nhấn tích vào các ô vuông bên trái của phương án để đánh dấu các đáp án đúng (chọn nhiều).
          </template>
        </p>
      </div>

      <!-- true_false -->
      <div v-else-if="form.type === 'true_false'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <label class="block text-sm font-semibold text-gray-800">Đáp án đúng <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-2 gap-4">
          <button type="button" @click="form.correct_answer = 'T'"
            class="flex items-center justify-center gap-3 p-4 rounded-2xl border-2 font-semibold text-sm transition-all cursor-pointer shadow-sm"
            :class="form.correct_answer === 'T' ? 'border-green-400 bg-green-50 text-green-700 ring-4 ring-green-50/50' : 'border-gray-200 text-gray-650 hover:border-green-300 bg-white'">
            <span class="w-8 h-8 rounded-full bg-green-500 text-white text-sm font-bold flex items-center justify-center shadow-sm">T</span>
            Đúng
          </button>
          <button type="button" @click="form.correct_answer = 'F'"
            class="flex items-center justify-center gap-3 p-4 rounded-2xl border-2 font-semibold text-sm transition-all cursor-pointer shadow-sm"
            :class="form.correct_answer === 'F' ? 'border-red-400 bg-red-50 text-red-700 ring-4 ring-red-50/50' : 'border-gray-200 text-gray-655 hover:border-red-300 bg-white'">
            <span class="w-8 h-8 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center shadow-sm">F</span>
            Sai
          </button>
        </div>
      </div>

      <!-- fill_blank -->
      <div v-else-if="form.type === 'fill_blank'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <label class="block text-sm font-semibold text-gray-800">
          Đáp án cho từng chỗ trống <span class="text-red-500">*</span>
          <span class="text-xs font-normal text-gray-400 ml-1">({{ blankCount }} chỗ trống)</span>
        </label>
        <div v-if="blankCount === 0" class="text-sm text-gray-400 italic bg-gray-50 p-4 rounded-2xl border border-dashed border-gray-200 text-center">
          Thêm <code class="bg-gray-100 px-1 rounded font-semibold text-gray-700">___</code> vào nội dung câu hỏi phía trên để tạo chỗ trống.
        </div>
        <div v-else class="space-y-3">
          <div v-for="i in blankCount" :key="i" class="flex items-center gap-3 p-3 rounded-2xl border border-gray-200 bg-gray-50/50">
            <span class="shrink-0 w-24 text-xs font-bold text-amber-700 bg-amber-100/80 rounded-xl px-3 py-2 text-center shadow-sm">Chỗ [{{ i }}]</span>
            <input v-model="fillBlankAnswers[i - 1]" class="input flex-1 py-2" :placeholder="`Nhập đáp án cho chỗ trống thứ ${i}...`" />
          </div>
        </div>
      </div>

      <!-- short_answer -->
      <div v-else-if="form.type === 'short_answer'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-1.5">
        <label class="block text-sm font-semibold text-gray-800">Đáp án đúng <span class="text-red-500">*</span></label>
        <input v-model="form.correct_answer" class="input" placeholder="Nhập câu trả lời ngắn đúng..." />
      </div>

      <!-- calculation -->
      <div v-else-if="form.type === 'calculation'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <label class="block text-sm font-semibold text-gray-800">Đáp án số <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs text-gray-550 mb-1 font-medium">Giá trị</label>
            <input v-model.number="calcAnswer.value" type="number" step="any" class="input" placeholder="Ví dụ: 78.5" />
          </div>
          <div>
            <label class="block text-xs text-gray-550 mb-1 font-medium">Sai số cho phép ±</label>
            <input v-model.number="calcAnswer.tolerance" type="number" step="any" min="0" class="input" placeholder="Ví dụ: 0.5" />
          </div>
          <div>
            <label class="block text-xs text-gray-550 mb-1 font-medium">Đơn vị đo</label>
            <input v-model="calcAnswer.unit" class="input" placeholder="Ví dụ: cm²" />
          </div>
        </div>
      </div>

      <!-- ordering -->
      <div v-else-if="form.type === 'ordering'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <div class="flex items-center justify-between mb-1">
          <label class="block text-sm font-semibold text-gray-800 mb-0">Các mục cần sắp xếp <span class="text-red-500">*</span></label>
          <button type="button" @click="orderingItems.push({ text: '' })"
            class="text-xs text-[#d63015] hover:underline font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm mục
          </button>
        </div>
        <p class="text-xs text-gray-500 font-medium">Nhập theo thứ tự đúng — học sinh sẽ thấy bị xáo trộn ngẫu nhiên.</p>
        <div class="space-y-2.5">
          <div v-for="(item, idx) in orderingItems" :key="idx" class="flex items-center gap-3 p-3 rounded-2xl border border-gray-200 bg-gray-50/50">
            <span class="w-8 h-8 rounded-full bg-pink-100 text-pink-700 text-sm font-bold flex items-center justify-center shrink-0">{{ idx + 1 }}</span>
            <input v-model="item.text" class="input flex-1 py-2" :placeholder="`Mục thứ ${idx + 1}...`" />
            <button v-if="orderingItems.length > 2" type="button" @click="orderingItems.splice(idx, 1)"
              class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
              <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>
      </div>

      <!-- matching -->
      <div v-else-if="form.type === 'matching'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <div class="flex items-center justify-between mb-1">
          <label class="block text-sm font-semibold text-gray-800 mb-0">Các cặp nối <span class="text-red-500">*</span></label>
          <button type="button" @click="matchingPairs.push({ left: '', right: '' })"
            class="text-xs text-[#d63015] hover:underline font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm cặp
          </button>
        </div>
        <p class="text-xs text-gray-550 font-medium">Mỗi hàng là một cặp đúng — học sinh thấy cột phải bị xáo trộn.</p>
        <div class="space-y-3">
          <div v-for="(pair, idx) in matchingPairs" :key="idx" class="flex items-center gap-3 p-3 rounded-2xl border border-gray-200 bg-gray-50/50">
            <span class="w-7 h-7 rounded-full bg-pink-100 text-pink-700 text-xs font-bold flex items-center justify-center shrink-0">{{ idx + 1 }}</span>
            <input v-model="pair.left" class="input flex-1 py-2" placeholder="Mục vế trái..." />
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0 text-gray-400 shadow-sm border border-gray-200">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </div>
            <input v-model="pair.right" class="input flex-1 py-2" placeholder="Mục vế phải..." />
            <button v-if="matchingPairs.length > 2" type="button" @click="matchingPairs.splice(idx, 1)"
              class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
              <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>
      </div>

      <!-- drag_drop -->
      <div v-else-if="form.type === 'drag_drop'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <div class="flex items-center justify-between mb-1">
          <label class="block text-sm font-semibold text-gray-800 mb-0">Vùng thả & mục <span class="text-red-500">*</span></label>
          <button type="button" @click="dragDropZones.push({ label: '', items: [''] })"
            class="text-xs text-[#d63015] hover:underline font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm vùng
          </button>
        </div>
        <p class="text-xs text-gray-550 font-medium">Mỗi vùng có tên và các mục thuộc về nó — học sinh kéo mục vào vùng đúng.</p>
        <div class="space-y-4">
          <div v-for="(zone, zi) in dragDropZones" :key="zi" class="p-4 rounded-2xl bg-gray-50 border border-gray-150 space-y-3 shadow-sm">
            <div class="flex items-center gap-3">
              <span class="text-xs font-bold text-gray-500 shrink-0 bg-white px-2 py-1 rounded-lg border">Vùng {{ zi + 1 }}</span>
              <input v-model="zone.label" class="input flex-1 text-sm bg-white" placeholder="Nhập tên vùng / danh mục phân loại..." />
              <button v-if="dragDropZones.length > 1" type="button" @click="dragDropZones.splice(zi, 1)"
                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <div class="space-y-2">
              <div v-for="(item, ii) in zone.items" :key="ii" class="flex items-center gap-2 pl-4">
                <span class="w-5.5 h-5.5 rounded-full bg-pink-50 text-pink-600 text-xs font-bold flex items-center justify-center shrink-0 border border-pink-100">{{ ii + 1 }}</span>
                <input v-model="zone.items[ii]" class="input flex-1 text-sm bg-white" :placeholder="`Mục thuộc nhóm ${ii + 1}...`" />
                <button v-if="zone.items.length > 1" type="button" @click="zone.items.splice(ii, 1)"
                  class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>
              <button type="button" @click="zone.items.push('')"
                class="text-xs text-[#d63015] hover:underline font-semibold pl-4 flex items-center gap-1 mt-1">
                + Thêm mục
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- essay / speaking / writing: teacher grades -->
      <div v-else-if="['essay','speaking','writing'].includes(form.type)"
        class="bg-amber-50 rounded-2xl border border-amber-200 p-4 text-sm text-amber-800 font-semibold shadow-sm">
        Loại câu hỏi này do giáo viên chấm thủ công sau khi học sinh làm bài — không cần nhập đáp án mẫu.
      </div>

      <!-- 5. Giải thích -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-1.5">
        <label class="block text-sm font-semibold text-gray-800">Giải thích đáp án</label>
        <textarea v-model="form.explanation" class="input resize-none" rows="3"
          placeholder="Giải thích tại sao đây là đáp án đúng (hiển thị sau khi học sinh làm xong bài)..."></textarea>
      </div>

      <!-- 6. Thêm vào đề / bài tập -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
        <p class="text-sm font-bold text-gray-800">Liên kết câu hỏi với Đề thi / Bài tập</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <button type="button" @click="targetType = ''"
            class="py-3 rounded-2xl border text-sm font-semibold transition-all cursor-pointer text-center shadow-sm"
            :class="targetType === '' ? 'border-gray-400 bg-gray-100 text-gray-700' : 'border-gray-200 text-gray-600 hover:border-gray-300 bg-white'">
            Chỉ lưu ngân hàng
          </button>
          <button type="button" @click="targetType = 'exam'; targetId = ''"
            class="py-3 rounded-2xl border text-sm font-semibold transition-all cursor-pointer text-center shadow-sm"
            :class="targetType === 'exam' ? 'border-[#d63015] bg-red-50 text-[#d63015] ring-4 ring-red-50/50' : 'border-gray-200 text-gray-600 hover:border-gray-300 bg-white'">
            Đề kiểm tra
          </button>
          <button type="button" @click="targetType = 'assignment'; targetId = ''"
            class="py-3 rounded-2xl border text-sm font-semibold transition-all cursor-pointer text-center shadow-sm"
            :class="targetType === 'assignment' ? 'border-[#d63015] bg-red-50 text-[#d63015] ring-4 ring-red-50/50' : 'border-gray-200 text-gray-600 hover:border-gray-300 bg-white'">
            Bài tập lớp học
          </button>
        </div>
        <div v-if="targetType === 'exam'" class="animate-fade-in">
          <select v-model="targetId" class="input">
            <option value="">— Chọn đề kiểm tra nhận câu hỏi này —</option>
            <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.title }}</option>
          </select>
        </div>
        <div v-if="targetType === 'assignment'" class="animate-fade-in">
          <select v-model="targetId" class="input">
            <option value="">— Chọn bài tập nhận câu hỏi này —</option>
            <option v-for="a in assignments" :key="a.id" :value="a.id">{{ a.title }}</option>
          </select>
        </div>
      </div>

      <div v-if="error" class="text-sm text-red-650 bg-red-50 p-3 rounded-2xl border border-red-100 shadow-sm">{{ error }}</div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pb-6">
        <RouterLink to="/teacher/question-bank"
          class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
          Hủy
        </RouterLink>
        <button type="button" @click="showPreview = true"
          class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
          Xem trước
        </button>
        <button @click="save" :disabled="saving"
          class="px-6 py-2.5 rounded-xl bg-[#d63015] text-white text-sm font-semibold hover:bg-[#c02a10] disabled:opacity-60 transition-colors cursor-pointer shadow-sm">
          {{ saving ? 'Đang lưu...' : (isEdit ? 'Cập nhật' : 'Thêm câu hỏi') }}
        </button>
      </div>
    </div>
  </div>

  <!-- Preview Modal -->
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="showPreview" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showPreview = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl">
            <span class="text-sm font-bold text-gray-800">Xem trước câu hỏi</span>
            <button @click="showPreview = false" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="p-6 space-y-4">
            <div class="flex flex-wrap gap-2">
              <span class="px-2.5 py-1 rounded-lg text-xs font-semibold" :class="typeClass(form.type)">{{ typeLabel(form.type) }}</span>
              <span class="px-2.5 py-1 rounded-lg text-xs font-semibold" :class="difficultyClass(form.difficulty)">{{ difficultyLabel(form.difficulty) }}</span>
              <span v-if="previewSubject" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-600">{{ previewSubject.name }}</span>
              <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-500">{{ form.default_points }}đ</span>
            </div>

            <div v-if="form.type === 'listening' && listeningAudio" class="p-3 bg-sky-50 rounded-xl text-sm text-sky-700 flex items-center gap-2">
              <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
              {{ listeningAudio }}
            </div>

            <div class="text-sm text-gray-800 leading-relaxed min-h-[40px]">
              <span v-if="form.content" v-html="renderMath(form.content)"></span>
              <span v-else class="text-gray-300 italic">Chưa có nội dung câu hỏi</span>
            </div>

            <!-- Image preview in modal -->
            <div v-if="imageUrl" class="rounded-2xl border border-gray-200 bg-gray-50 p-3 max-w-md shadow-sm mx-auto my-3">
              <img :src="imageUrl" class="max-h-48 object-contain rounded-xl mx-auto" />
            </div>

            <!-- MC / reading / listening -->
            <div v-if="['multiple_choice','multiple_select','reading','listening'].includes(form.type)" class="space-y-2">
              <div v-for="opt in formOptions" :key="opt.id"
                class="flex items-center gap-2.5 p-3 rounded-xl border"
                :class="isCorrectOption(opt.id) ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'">
                <span class="w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center shrink-0"
                  :class="isCorrectOption(opt.id) ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600'">{{ opt.id }}</span>
                <span class="text-sm flex-1" :class="opt.text ? 'text-gray-700' : 'text-gray-300 italic'"
                  v-html="opt.text ? renderMath(opt.text) : `Đáp án ${opt.id}`"></span>
                <svg v-if="isCorrectOption(opt.id)" class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              </div>
            </div>

            <!-- true_false -->
            <div v-else-if="form.type === 'true_false'" class="grid grid-cols-2 gap-3">
              <div class="flex items-center gap-2 p-3 rounded-xl border-2 border-green-200 bg-green-50">
                <span class="w-7 h-7 rounded-full bg-green-500 text-white text-sm font-bold flex items-center justify-center">T</span>
                <span class="text-sm font-medium text-green-700">Đúng</span>
                <svg v-if="form.correct_answer === 'T'" class="w-4 h-4 text-green-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              </div>
              <div class="flex items-center gap-2 p-3 rounded-xl border-2 border-red-200 bg-red-50">
                <span class="w-7 h-7 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center">F</span>
                <span class="text-sm font-medium text-red-700">Sai</span>
                <svg v-if="form.correct_answer === 'F'" class="w-4 h-4 text-red-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              </div>
            </div>

            <!-- fill_blank -->
            <div v-else-if="form.type === 'fill_blank' && blankCount > 0" class="space-y-2">
              <div v-for="i in blankCount" :key="i" class="flex items-center gap-2 p-2.5 rounded-xl bg-amber-50 border border-amber-200">
                <span class="w-6 h-6 rounded-full bg-amber-400 text-white text-xs font-bold flex items-center justify-center shrink-0">{{ i }}</span>
                <span class="text-sm text-amber-800 font-medium">{{ fillBlankAnswers[i - 1] || '...' }}</span>
              </div>
            </div>

            <!-- ordering -->
            <div v-else-if="form.type === 'ordering'" class="space-y-2">
              <div v-for="(item, i) in orderingItems" :key="i" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-pink-50 border border-pink-200">
                <span class="w-6 h-6 rounded-full bg-pink-400 text-white text-xs font-bold flex items-center justify-center shrink-0">{{ i + 1 }}</span>
                <span class="text-sm text-pink-800">{{ item.text || `Mục ${i + 1}` }}</span>
              </div>
            </div>

            <!-- matching -->
            <div v-else-if="form.type === 'matching'" class="space-y-2">
              <div v-for="(pair, i) in matchingPairs" :key="i" class="flex items-center gap-3 p-2.5 rounded-xl bg-pink-50 border border-pink-200">
                <span class="text-sm font-medium text-pink-800 flex-1">{{ pair.left || `Trái ${i+1}` }}</span>
                <svg class="w-4 h-4 text-pink-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                <span class="text-sm font-medium text-pink-800 flex-1 text-right">{{ pair.right || `Phải ${i+1}` }}</span>
              </div>
            </div>

            <!-- drag_drop -->
            <div v-else-if="form.type === 'drag_drop'" class="space-y-2">
              <div v-for="(zone, zi) in dragDropZones" :key="zi" class="p-3 rounded-xl bg-pink-50 border border-pink-200">
                <p class="text-xs font-bold text-pink-700 mb-1.5">{{ zone.label || `Vùng ${zi+1}` }}</p>
                <div class="flex flex-wrap gap-1.5">
                  <span v-for="(item, ii) in zone.items" :key="ii" class="px-2 py-1 bg-white rounded-lg border border-pink-200 text-xs text-pink-700">{{ item || `Mục ${ii+1}` }}</span>
                </div>
              </div>
            </div>

            <!-- essay / speaking / writing -->
            <div v-else-if="['essay','speaking','writing'].includes(form.type)" class="p-3 bg-amber-50 rounded-xl text-sm text-amber-600">
              Học sinh nhập câu trả lời — giáo viên chấm thủ công.
            </div>

            <!-- calculation -->
            <div v-else-if="form.type === 'calculation'" class="p-3 bg-blue-50 rounded-xl text-sm text-blue-700">
              Đáp án: <strong>{{ calcAnswer.value ?? '...' }}</strong>
              <span v-if="calcAnswer.tolerance"> ± {{ calcAnswer.tolerance }}</span>
              <span v-if="calcAnswer.unit"> {{ calcAnswer.unit }}</span>
            </div>

            <!-- short_answer -->
            <div v-else-if="form.type === 'short_answer' && form.correct_answer" class="p-3 bg-green-50 rounded-xl text-sm text-green-700">
              Đáp án: <strong>{{ form.correct_answer }}</strong>
            </div>

            <div v-if="form.explanation" class="p-3 bg-gray-50 rounded-xl border border-gray-150">
              <p class="text-xs font-semibold text-gray-500 mb-1">Giải thích</p>
              <p class="text-sm text-gray-600" v-html="renderMath(form.explanation)"></p>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import api from '@api/axios'
import { renderMath } from '@/utils/math'

const router = useRouter()
const route = useRoute()

const isEdit = computed(() => !!route.params.id)
const subjects = ref([])
const exams = ref([])
const assignments = ref([])
const saving = ref(false)
const error = ref('')
const submitted = ref(false)
const showPreview = ref(false)
const targetType = ref('')
const targetId = ref('')

const form = reactive({
  type: 'multiple_choice', difficulty: 'medium', subject_id: '',
  content: '', correct_answer: '', explanation: '',
  default_points: 1, is_public: false,
  media_type: null, media_path: null,
})

const imageInputRef   = ref(null)
const imageUrl        = ref('')
const imageUploading  = ref(false)
const imageUploadError = ref('')
const contentTextareaRef = ref(null)

const currentSubjectCode = computed(() => {
  const sub = subjects.value.find(s => s.id === form.subject_id)
  return sub ? sub.code.toUpperCase() : ''
})

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

const groupedQuestionTypes = computed(() => {
  const code = currentSubjectCode.value
  return subjectTypeGroups[code] || subjectTypeGroups.DEFAULT
})

watch(groupedQuestionTypes, (newGroups) => {
  const allAvailableTypes = newGroups.flatMap(g => g.options.map(o => o.value))
  if (allAvailableTypes.length && !allAvailableTypes.includes(form.type)) {
    form.type = allAvailableTypes[0]
  }
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

const chemFormulaInput = ref('')
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

function insertChemFormula() {
  if (!formattedChemLatex.value) return
  insertSymbol(formattedChemLatex.value)
  chemFormulaInput.value = ''
}

const ipaVowels = ['æ', 'ɑ:', 'ɒ', 'ɔ:', 'ə', 'ɜ:', 'ɪ', 'i:', 'ʌ', 'u:', 'ʊ', 'eɪ', 'aɪ', 'ɔɪ', 'aʊ', 'oʊ', 'ɪə', 'eə', 'ʊə']
const ipaConsonants = ['θ', 'ð', 'ʃ', 'ʒ', 'tʃ', 'dʒ', 'ŋ']

const literatureQuoteInput = ref('')
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

const historyYearInput = ref('')
const historyEventInput = ref('')
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

async function uploadImageFile(file) {
  if (!file) return
  imageUploadError.value = ''
  imageUploading.value   = true

  imageUrl.value = URL.createObjectURL(file)

  const fd = new FormData()
  fd.append('file', file)

  try {
    const { data } = await api.post('/teacher/media/image', fd, {
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
const formOptions = ref([
  { id: 'A', text: '' }, { id: 'B', text: '' },
  { id: 'C', text: '' }, { id: 'D', text: '' },
])
const multiSelectAnswer = ref([])
const calcAnswer = reactive({ value: null, tolerance: 0, unit: '' })
const orderingItems = ref([{ text: '' }, { text: '' }, { text: '' }])
const matchingPairs = ref([{ left: '', right: '' }, { left: '', right: '' }])
const dragDropZones = ref([{ label: '', items: ['', ''] }, { label: '', items: ['', ''] }])
const listeningAudio = ref('')
const fillBlankAnswers = ref([''])

const difficulties = [
  { value: 'easy',   label: 'Dễ',  active: 'border-green-500 bg-green-50 text-green-700 font-bold ring-4 ring-green-50/50' },
  { value: 'medium', label: 'Trung bình',  active: 'border-amber-500 bg-amber-50 text-amber-700 font-bold ring-4 ring-amber-50/50' },
  { value: 'hard',   label: 'Khó', active: 'border-red-500 bg-red-50 text-red-700 font-bold ring-4 ring-red-50/50' },
]

const blankCount = computed(() => (form.content.match(/___/g) || []).length)
const previewSubject = computed(() => subjects.value.find(s => s.id === form.subject_id))

function isCorrectOption(id) {
  if (['multiple_choice', 'reading', 'listening'].includes(form.type)) return form.correct_answer === id
  if (form.type === 'multiple_select') return multiSelectAnswer.value.includes(id)
  return false
}

watch(blankCount, (n) => {
  while (fillBlankAnswers.value.length < n) fillBlankAnswers.value.push('')
})

watch(() => form.type, () => {
  form.correct_answer = ''
  multiSelectAnswer.value = []
  formOptions.value = [
    { id: 'A', text: '' }, { id: 'B', text: '' },
    { id: 'C', text: '' }, { id: 'D', text: '' },
  ]
})

watch(targetType, () => { targetId.value = '' })

function addOption() {
  const ids = 'ABCDEFGH'
  const nextId = ids[formOptions.value.length] ?? String(formOptions.value.length + 1)
  formOptions.value.push({ id: nextId, text: '' })
}

async function save() {
  submitted.value = true
  if (!form.subject_id) return
  saving.value = true; error.value = ''
  try {
    const payload = { ...form }

    if (['multiple_choice', 'reading', 'listening'].includes(form.type)) {
      payload.options = formOptions.value
      const idx = formOptions.value.findIndex(o => o.id === form.correct_answer)
      payload.correct_answer = idx >= 0 ? [String(idx)] : []
    } else if (form.type === 'true_false') {
      payload.options = ['Đúng', 'Sai']
      payload.correct_answer = [form.correct_answer === 'T' ? '0' : '1']
    } else if (form.type === 'multiple_select') {
      payload.options = formOptions.value
      payload.correct_answer = multiSelectAnswer.value
        .map(id => formOptions.value.findIndex(o => o.id === id))
        .filter(i => i >= 0)
        .map(i => String(i))
    } else if (form.type === 'calculation') {
      payload.correct_answer = { ...calcAnswer }
    } else if (form.type === 'ordering') {
      payload.options = orderingItems.value.map(i => i.text)
      payload.correct_answer = orderingItems.value.map((_, i) => String(i))
    } else if (form.type === 'matching') {
      const leftArr = matchingPairs.value.map(p => p.left)
      const rightArr = matchingPairs.value.map(p => p.right)
      payload.options = { left: leftArr, right: rightArr }
      const ans = {}
      matchingPairs.value.forEach((_, i) => { ans[String(i)] = String(i) })
      payload.correct_answer = ans
    } else if (form.type === 'drag_drop') {
      payload.options = dragDropZones.value
      payload.correct_answer = dragDropZones.value
    } else if (form.type === 'fill_blank') {
      payload.correct_answer = fillBlankAnswers.value.slice(0, blankCount.value)
    }

    if (form.type === 'listening') payload.audio_path = listeningAudio.value || null

    if (!isEdit.value && targetType.value && targetId.value) {
      payload.assign_to_type = targetType.value;
      payload.assign_to_id = parseInt(targetId.value);
    }

    let questionId
    if (isEdit.value) {
      await api.put(`/teacher/question-bank/${route.params.id}`, payload)
      questionId = route.params.id

      if (targetType.value && targetId.value && questionId) {
        const endpoint = targetType.value === 'exam'
          ? `/teacher/exams/${targetId.value}/import-questions`
          : `/teacher/assignments/${targetId.value}/import-questions`
        await api.post(endpoint, { question_ids: [questionId] }).catch(() => {})
      }
    } else {
      const { data } = await api.post('/teacher/question-bank', payload)
      questionId = data.data?.id ?? data.id
    }

    router.push('/teacher/question-bank')
  } catch (e) { error.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

function typeLabel(t) {
  const map = {
    multiple_choice: 'Chọn 1', multiple_select: 'Chọn nhiều', true_false: 'Đúng/Sai',
    fill_blank: 'Điền trống', short_answer: 'Trả lời ngắn', essay: 'Tự luận',
    calculation: 'Tính toán', ordering: 'Sắp xếp', matching: 'Nối cặp',
    drag_drop: 'Kéo thả', reading: 'Đọc hiểu', listening: 'Nghe hiểu',
    speaking: 'Nói', writing: 'Viết',
  }
  return map[t] ?? t
}
function typeClass(t) {
  const g = {
    multiple_choice: 'bg-blue-100 text-blue-700', multiple_select: 'bg-blue-100 text-blue-700',
    true_false: 'bg-green-100 text-green-700', fill_blank: 'bg-amber-100 text-amber-700',
    short_answer: 'bg-amber-100 text-amber-700', essay: 'bg-orange-100 text-orange-700',
    calculation: 'bg-purple-100 text-purple-700', ordering: 'bg-pink-100 text-pink-700',
    matching: 'bg-pink-100 text-pink-700', drag_drop: 'bg-pink-100 text-pink-700',
    reading: 'bg-teal-100 text-teal-700', listening: 'bg-sky-100 text-sky-700',
    speaking: 'bg-red-100 text-[#c02a10]', writing: 'bg-orange-100 text-orange-700',
  }
  return g[t] ?? 'bg-gray-100 text-gray-600'
}
function difficultyLabel(d) { return { easy: 'Dễ', medium: 'Trung bình', hard: 'Khó' }[d] ?? d }
function difficultyClass(d) {
  return { easy: 'bg-green-50 text-green-700', medium: 'bg-amber-50 text-amber-700', hard: 'bg-red-50 text-red-700' }[d] ?? ''
}

onMounted(async () => {
  const [subRes, examRes, assignRes] = await Promise.all([
    api.get('/public/subjects').catch(() => ({ data: { data: [] } })),
    api.get('/teacher/exams').catch(() => ({ data: { data: [] } })),
    api.get('/teacher/assignments').catch(() => ({ data: { data: [] } })),
  ])
  subjects.value = subRes.data.data ?? []
  exams.value = examRes.data.data?.data ?? examRes.data.data ?? []
  assignments.value = assignRes.data.data?.data ?? assignRes.data.data ?? []

  if (isEdit.value) {
    try {
      const { data: qData } = await api.get(`/teacher/question-bank/${route.params.id}`)
      const q = qData.data ?? qData
      Object.assign(form, {
        type: q.type, difficulty: q.difficulty ?? 'medium',
        subject_id: q.subject?.id ?? '',
        content: q.content, explanation: q.explanation ?? '',
        default_points: q.default_points ?? 1, is_public: q.is_public ?? false,
        media_type: q.media_type ?? null,
        media_path: q.media_path ?? null,
      })
      imageUrl.value  = ''
      imageUploadError.value = ''
      if (q.media_type === 'image' && q.media_path) {
        imageUrl.value  = '/storage/' + q.media_path
      }
      if (q.options) {
        if (q.type === 'ordering' && Array.isArray(q.options)) {
          orderingItems.value = q.options.map(t => ({ text: String(t) }))
        } else if (q.type === 'matching') {
          const left = q.options?.left ?? []
          const right = q.options?.right ?? []
          matchingPairs.value = left.map((l, i) => ({ left: l, right: right[i] ?? '' }))
        } else if (q.type === 'drag_drop' && Array.isArray(q.options)) {
          dragDropZones.value = q.options
        } else if (Array.isArray(q.options)) {
          formOptions.value = q.options.map((o, i) => {
            if (typeof o === 'object' && o !== null) {
              return { id: o.id ?? String.fromCharCode(65 + i), text: o.text ?? '' }
            }
            return { id: String.fromCharCode(65 + i), text: String(o) }
          })
        }
      }
      if (q.type === 'true_false') {
        const val = Array.isArray(q.correct_answer) ? q.correct_answer[0] : q.correct_answer
        form.correct_answer = String(val) === '0' ? 'T' : 'F'
      }
      if (q.type === 'multiple_select' && Array.isArray(q.correct_answer)) {
        multiSelectAnswer.value = q.correct_answer.map(v => {
          const n = Number(v)
          return Number.isNaN(n) ? String(v) : (formOptions.value[n]?.id ?? String(v))
        })
      }
      else if (q.type === 'calculation' && q.correct_answer?.value !== undefined) Object.assign(calcAnswer, q.correct_answer)
      else if (q.type === 'fill_blank' && Array.isArray(q.correct_answer)) fillBlankAnswers.value = q.correct_answer
      else if (!['ordering', 'matching', 'drag_drop'].includes(q.type))
        form.correct_answer = (() => {
          const val = Array.isArray(q.correct_answer) ? q.correct_answer[0] : q.correct_answer
          const n = Number(val)
          return Number.isNaN(n) ? (val ?? '') : (formOptions.value[n]?.id ?? '')
        })()
      if (q.type === 'listening' && q.audio_path) listeningAudio.value = q.audio_path
    } catch { router.push('/teacher/question-bank') }
  }
})
</script>

<style scoped>
@reference "tailwindcss";
.input {
  @apply w-full px-4 py-2.5 rounded-2xl border border-gray-200 focus:outline-none focus:ring-4 focus:ring-[#d63015]/10 focus:border-[#d63015] text-sm bg-white transition-all shadow-sm;
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
