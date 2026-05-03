<template>
  <div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center gap-3">
      <RouterLink to="/teacher/question-bank"
        class="p-2 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors text-gray-500 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-800 mb-2">Môn học <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
          <button v-for="s in subjects" :key="s.id" type="button" @click="form.subject_id = s.id"
            class="flex items-center gap-2.5 px-4 py-3 rounded-xl border text-sm font-medium transition-all"
            :class="form.subject_id === s.id ? 'border-[#d63015] bg-red-50 text-[#d63015]' : 'border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50'">
            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 text-white"
              :style="{ backgroundColor: s.color || '#6366f1' }">{{ s.icon || s.name?.[0] }}</span>
            {{ s.name }}
          </button>
        </div>
        <p v-if="!form.subject_id && submitted" class="text-xs text-red-500 mt-2">Vui lòng chọn môn học</p>
      </div>

      <!-- 2. Loại + Độ khó + Điểm -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1.5">Loại câu hỏi <span class="text-red-500">*</span></label>
            <select v-model="form.type" class="input">
              <optgroup label="Trắc nghiệm">
                <option value="multiple_choice">Chọn 1 đáp án</option>
                <option value="multiple_select">Chọn nhiều đáp án</option>
                <option value="true_false">Đúng / Sai</option>
              </optgroup>
              <optgroup label="Tự luận">
                <option value="fill_blank">Điền vào chỗ trống</option>
                <option value="short_answer">Trả lời ngắn</option>
                <option value="essay">Tự luận</option>
                <option value="calculation">Tính toán (có sai số)</option>
              </optgroup>
              <optgroup label="Tương tác">
                <option value="ordering">Sắp xếp thứ tự</option>
                <option value="matching">Nối cặp</option>
                <option value="drag_drop">Kéo thả</option>
              </optgroup>
              <optgroup label="Kỹ năng ngôn ngữ">
                <option value="reading">Đọc hiểu</option>
                <option value="listening">Nghe hiểu</option>
                <option value="speaking">Nói</option>
                <option value="writing">Viết</option>
              </optgroup>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1.5">Độ khó</label>
            <div class="grid grid-cols-3 gap-2">
              <button v-for="d in difficulties" :key="d.value" type="button" @click="form.difficulty = d.value"
                class="py-2 rounded-xl border text-xs font-semibold transition-all"
                :class="form.difficulty === d.value ? d.active : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                {{ d.label }}
              </button>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-5">
          <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600 shrink-0">Điểm:</label>
            <input v-model.number="form.default_points" type="number" min="0.25" max="100" step="0.25"
              class="w-20 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-center focus:outline-none focus:ring-2 focus:ring-[#d63015]" />
          </div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.is_public" class="rounded text-[#d63015]" />
            <span class="text-sm text-gray-600">Chia sẻ công khai</span>
          </label>
        </div>
      </div>

      <!-- 3. Nội dung -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <!-- listening: audio URL -->
        <div v-if="form.type === 'listening'">
          <label class="block text-sm font-semibold text-gray-800 mb-1.5">URL âm thanh <span class="text-red-500">*</span></label>
          <input v-model="listeningAudio" class="input" placeholder="https://..." />
          <p class="text-xs text-gray-400 mt-1">Link file MP3/WAV hoặc embed từ dịch vụ lưu trữ</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-800 mb-1.5">
            {{ form.type === 'reading' ? 'Đoạn văn & câu hỏi' : 'Nội dung câu hỏi' }}
            <span class="text-red-500">*</span>
          </label>
          <p v-if="form.type === 'fill_blank'" class="text-xs text-gray-400 mb-1.5">
            Dùng <code class="bg-gray-100 px-1 rounded">___</code> để đánh dấu chỗ trống. Ví dụ: "Thủ đô Việt Nam là ___."
          </p>
          <p v-if="form.type === 'reading'" class="text-xs text-gray-400 mb-1.5">Nhập đoạn văn và câu hỏi. Học sinh đọc toàn bộ nội dung này.</p>
          <textarea v-model="form.content" class="input resize-none" :rows="form.type === 'reading' ? 8 : 4"
            placeholder="Nhập nội dung câu hỏi..."></textarea>
        </div>
      </div>

      <!-- 4. Đáp án — theo từng loại -->

      <!-- multiple_choice / multiple_select / reading / listening: danh sách đáp án A/B/C/D -->
      <div v-if="['multiple_choice','multiple_select','reading','listening'].includes(form.type)"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-800 mb-3">Các đáp án <span class="text-red-500">*</span></label>
          <div class="space-y-2.5">
            <div v-for="(opt, idx) in formOptions" :key="idx" class="flex items-center gap-3">
              <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 text-sm font-bold flex items-center justify-center shrink-0">{{ opt.id }}</span>
              <input v-model="opt.text" class="input flex-1" :placeholder="`Nhập đáp án ${opt.id}...`" />
              <button v-if="formOptions.length > 2" type="button" @click="formOptions.splice(idx, 1)"
                class="p-2 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <button v-if="formOptions.length < 8" type="button" @click="addOption"
              class="text-sm text-[#d63015] hover:underline font-medium">+ Thêm đáp án</button>
          </div>
        </div>

        <!-- Đáp án đúng: chọn 1 -->
        <div v-if="['multiple_choice','reading','listening'].includes(form.type)" class="pt-3 border-t border-gray-100">
          <label class="block text-sm font-semibold text-gray-800 mb-1.5">Đáp án đúng <span class="text-red-500">*</span></label>
          <select v-model="form.correct_answer" class="input">
            <option value="">— Chọn đáp án đúng —</option>
            <option v-for="opt in formOptions" :key="opt.id" :value="opt.id">{{ opt.id }}: {{ opt.text }}</option>
          </select>
        </div>

        <!-- Đáp án đúng: chọn nhiều -->
        <div v-else class="pt-3 border-t border-gray-100">
          <label class="block text-sm font-semibold text-gray-800 mb-2">
            Đáp án đúng <span class="text-red-500">*</span>
            <span class="text-xs font-normal text-gray-400 ml-1">(chọn tất cả đáp án đúng)</span>
          </label>
          <div class="flex flex-wrap gap-3">
            <label v-for="opt in formOptions" :key="opt.id"
              class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border transition-colors"
              :class="multiSelectAnswer.includes(opt.id) ? 'border-[#d63015] bg-red-50' : 'border-gray-200 bg-white'">
              <input type="checkbox" :value="opt.id" v-model="multiSelectAnswer" class="rounded text-[#d63015]" />
              <span class="text-sm font-medium text-gray-700">{{ opt.id }}: {{ opt.text }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- true_false -->
      <div v-else-if="form.type === 'true_false'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-800 mb-3">Đáp án đúng <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-2 gap-3">
          <button type="button" @click="form.correct_answer = 'T'"
            class="flex items-center justify-center gap-2 p-4 rounded-xl border-2 font-medium text-sm transition-all"
            :class="form.correct_answer === 'T' ? 'border-green-400 bg-green-50 text-green-700' : 'border-gray-200 text-gray-600 hover:border-green-200'">
            <span class="w-8 h-8 rounded-full bg-green-500 text-white text-sm font-bold flex items-center justify-center">T</span>
            Đúng
          </button>
          <button type="button" @click="form.correct_answer = 'F'"
            class="flex items-center justify-center gap-2 p-4 rounded-xl border-2 font-medium text-sm transition-all"
            :class="form.correct_answer === 'F' ? 'border-red-400 bg-red-50 text-red-700' : 'border-gray-200 text-gray-600 hover:border-red-200'">
            <span class="w-8 h-8 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center">F</span>
            Sai
          </button>
        </div>
      </div>

      <!-- fill_blank -->
      <div v-else-if="form.type === 'fill_blank'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-800 mb-1">
          Đáp án cho từng chỗ trống <span class="text-red-500">*</span>
          <span class="text-xs font-normal text-gray-400 ml-1">({{ blankCount }} chỗ trống)</span>
        </label>
        <div v-if="blankCount === 0" class="mt-2 text-sm text-gray-400 italic">Thêm <code class="bg-gray-100 px-1 rounded">___</code> vào nội dung câu hỏi để tạo chỗ trống.</div>
        <div v-else class="mt-3 space-y-2.5">
          <div v-for="i in blankCount" :key="i" class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 text-xs font-bold flex items-center justify-center shrink-0">{{ i }}</span>
            <input v-model="fillBlankAnswers[i - 1]" class="input flex-1" :placeholder="`Đáp án chỗ trống ${i}`" />
          </div>
        </div>
      </div>

      <!-- short_answer -->
      <div v-else-if="form.type === 'short_answer'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-800 mb-1.5">Đáp án đúng <span class="text-red-500">*</span></label>
        <input v-model="form.correct_answer" class="input" placeholder="Nhập câu trả lời ngắn đúng..." />
      </div>

      <!-- calculation -->
      <div v-else-if="form.type === 'calculation'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-800 mb-3">Đáp án số <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Giá trị</label>
            <input v-model.number="calcAnswer.value" type="number" step="any" class="input" placeholder="78.5" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Sai số ±</label>
            <input v-model.number="calcAnswer.tolerance" type="number" step="any" min="0" class="input" placeholder="0.5" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Đơn vị</label>
            <input v-model="calcAnswer.unit" class="input" placeholder="cm²" />
          </div>
        </div>
      </div>

      <!-- ordering -->
      <div v-else-if="form.type === 'ordering'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-800 mb-1">Các mục cần sắp xếp <span class="text-red-500">*</span></label>
        <p class="text-xs text-gray-400 mb-3">Nhập theo thứ tự đúng — học sinh sẽ thấy bị xáo trộn ngẫu nhiên.</p>
        <div class="space-y-2.5">
          <div v-for="(item, idx) in orderingItems" :key="idx" class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-pink-100 text-pink-700 text-sm font-bold flex items-center justify-center shrink-0">{{ idx + 1 }}</span>
            <input v-model="item.text" class="input flex-1" :placeholder="`Mục thứ ${idx + 1}...`" />
            <button v-if="orderingItems.length > 2" type="button" @click="orderingItems.splice(idx, 1)"
              class="p-2 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <button type="button" @click="orderingItems.push({ text: '' })"
            class="text-sm text-[#d63015] hover:underline font-medium">+ Thêm mục</button>
        </div>
      </div>

      <!-- matching -->
      <div v-else-if="form.type === 'matching'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-800 mb-1">Các cặp nối <span class="text-red-500">*</span></label>
        <p class="text-xs text-gray-400 mb-3">Mỗi hàng là một cặp đúng — học sinh thấy cột phải bị xáo trộn.</p>
        <div class="space-y-2.5">
          <div class="grid grid-cols-2 gap-3 px-9 text-xs font-semibold text-gray-400">
            <span>Cột trái</span><span>Cột phải</span>
          </div>
          <div v-for="(pair, idx) in matchingPairs" :key="idx" class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-full bg-pink-100 text-pink-700 text-xs font-bold flex items-center justify-center shrink-0">{{ idx + 1 }}</span>
            <input v-model="pair.left" class="input flex-1" placeholder="Mục trái..." />
            <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            <input v-model="pair.right" class="input flex-1" placeholder="Mục phải..." />
            <button v-if="matchingPairs.length > 2" type="button" @click="matchingPairs.splice(idx, 1)"
              class="p-2 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <button type="button" @click="matchingPairs.push({ left: '', right: '' })"
            class="text-sm text-[#d63015] hover:underline font-medium">+ Thêm cặp</button>
        </div>
      </div>

      <!-- drag_drop -->
      <div v-else-if="form.type === 'drag_drop'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-800 mb-1">Vùng thả & mục <span class="text-red-500">*</span></label>
        <p class="text-xs text-gray-400 mb-3">Mỗi vùng có tên và các mục thuộc về nó — học sinh kéo mục vào vùng đúng.</p>
        <div class="space-y-3">
          <div v-for="(zone, zi) in dragDropZones" :key="zi" class="p-3 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-gray-500 shrink-0">Vùng {{ zi + 1 }}</span>
              <input v-model="zone.label" class="input flex-1 text-sm" placeholder="Tên vùng / danh mục..." />
              <button v-if="dragDropZones.length > 1" type="button" @click="dragDropZones.splice(zi, 1)"
                class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <div v-for="(item, ii) in zone.items" :key="ii" class="flex items-center gap-2 pl-4">
              <span class="w-5 h-5 rounded-full bg-pink-100 text-pink-600 text-xs font-bold flex items-center justify-center shrink-0">{{ ii + 1 }}</span>
              <input v-model="zone.items[ii]" class="input flex-1 text-sm" :placeholder="`Mục ${ii + 1}...`" />
              <button v-if="zone.items.length > 1" type="button" @click="zone.items.splice(ii, 1)"
                class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <button type="button" @click="zone.items.push('')"
              class="text-xs text-[#d63015] hover:underline font-medium pl-4">+ Thêm mục</button>
          </div>
          <button type="button" @click="dragDropZones.push({ label: '', items: [''] })"
            class="text-sm text-[#d63015] hover:underline font-medium">+ Thêm vùng</button>
        </div>
      </div>

      <!-- essay / speaking / writing: teacher grades -->
      <div v-else-if="['essay','speaking','writing'].includes(form.type)"
        class="bg-amber-50 rounded-2xl border border-amber-100 p-4 text-sm text-amber-700">
        Loại câu hỏi này do giáo viên chấm thủ công — không cần nhập đáp án mẫu.
      </div>

      <!-- 5. Giải thích -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-semibold text-gray-800 mb-1.5">Giải thích đáp án</label>
        <textarea v-model="form.explanation" class="input resize-none" rows="3"
          placeholder="Giải thích tại sao đây là đáp án đúng (hiển thị sau khi học sinh làm bài)..."></textarea>
      </div>

      <!-- 6. Thêm vào đề / bài tập -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <p class="text-sm font-semibold text-gray-800">Thêm vào đề / bài tập</p>
        <div class="grid grid-cols-3 gap-2">
          <button type="button" @click="targetType = ''"
            class="py-2.5 rounded-xl border text-sm font-medium transition-all"
            :class="targetType === '' ? 'border-gray-400 bg-gray-100 text-gray-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
            Chỉ lưu ngân hàng
          </button>
          <button type="button" @click="targetType = 'exam'; targetId = ''"
            class="py-2.5 rounded-xl border text-sm font-medium transition-all"
            :class="targetType === 'exam' ? 'border-[#d63015] bg-red-50 text-[#d63015]' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
            Bài kiểm tra
          </button>
          <button type="button" @click="targetType = 'assignment'; targetId = ''"
            class="py-2.5 rounded-xl border text-sm font-medium transition-all"
            :class="targetType === 'assignment' ? 'border-[#d63015] bg-red-50 text-[#d63015]' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
            Bài tập
          </button>
        </div>
        <div v-if="targetType === 'exam'">
          <select v-model="targetId" class="input">
            <option value="">— Chọn đề kiểm tra —</option>
            <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.title }}</option>
          </select>
        </div>
        <div v-if="targetType === 'assignment'">
          <select v-model="targetId" class="input">
            <option value="">— Chọn bài tập —</option>
            <option v-for="a in assignments" :key="a.id" :value="a.id">{{ a.title }}</option>
          </select>
        </div>
      </div>

      <div v-if="error" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl border border-red-100">{{ error }}</div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pb-6">
        <RouterLink to="/teacher/question-bank"
          class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
          Hủy
        </RouterLink>
        <button type="button" @click="showPreview = true"
          class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
          Xem trước
        </button>
        <button @click="save" :disabled="saving"
          class="px-6 py-2.5 rounded-xl bg-[#d63015] text-white text-sm font-semibold hover:bg-[#c02a10] disabled:opacity-60 transition-colors">
          {{ saving ? 'Đang lưu...' : (isEdit ? 'Cập nhật' : 'Thêm câu hỏi') }}
        </button>
      </div>
    </div>
  </div>

  <!-- Preview Modal -->
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="showPreview" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showPreview = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl">
            <span class="text-sm font-semibold text-gray-700">Xem trước câu hỏi</span>
            <button @click="showPreview = false" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="p-5 space-y-4">
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
              <span v-if="form.content">{{ form.content }}</span>
              <span v-else class="text-gray-300 italic">Chưa có nội dung câu hỏi</span>
            </div>

            <!-- MC / reading / listening -->
            <div v-if="['multiple_choice','multiple_select','reading','listening'].includes(form.type)" class="space-y-2">
              <div v-for="opt in formOptions" :key="opt.id"
                class="flex items-center gap-2.5 p-3 rounded-xl border"
                :class="isCorrectOption(opt.id) ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'">
                <span class="w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center shrink-0"
                  :class="isCorrectOption(opt.id) ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600'">{{ opt.id }}</span>
                <span class="text-sm flex-1" :class="opt.text ? 'text-gray-700' : 'text-gray-300 italic'">{{ opt.text || `Đáp án ${opt.id}` }}</span>
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

            <div v-if="form.explanation" class="p-3 bg-gray-50 rounded-xl border border-gray-100">
              <p class="text-xs font-semibold text-gray-500 mb-1">Giải thích</p>
              <p class="text-sm text-gray-600">{{ form.explanation }}</p>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import api from '@api/axios'

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
})
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
  { value: 'easy',   label: 'Dễ',  active: 'border-green-500 bg-green-50 text-green-700' },
  { value: 'medium', label: 'TB',  active: 'border-amber-500 bg-amber-50 text-amber-700' },
  { value: 'hard',   label: 'Khó', active: 'border-red-500 bg-red-50 text-red-700' },
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
    } else if (form.type === 'multiple_select') {
      payload.options = formOptions.value
      payload.correct_answer = multiSelectAnswer.value
    } else if (form.type === 'calculation') {
      payload.correct_answer = { ...calcAnswer }
    } else if (form.type === 'ordering') {
      payload.options = orderingItems.value.map(i => i.text)
      payload.correct_answer = orderingItems.value.map(i => i.text)
    } else if (form.type === 'matching') {
      payload.options = matchingPairs.value
      payload.correct_answer = matchingPairs.value
    } else if (form.type === 'drag_drop') {
      payload.options = dragDropZones.value
      payload.correct_answer = dragDropZones.value
    } else if (form.type === 'fill_blank') {
      payload.correct_answer = fillBlankAnswers.value.slice(0, blankCount.value)
    }

    if (form.type === 'listening') payload.audio_url = listeningAudio.value

    let questionId
    if (isEdit.value) {
      await api.put(`/teacher/question-bank/${route.params.id}`, payload)
      questionId = route.params.id
    } else {
      const { data } = await api.post('/teacher/question-bank', payload)
      questionId = data.data?.id ?? data.id
    }

    if (targetType.value && targetId.value && questionId) {
      const endpoint = targetType.value === 'exam'
        ? `/teacher/exams/${targetId.value}/import-questions`
        : `/teacher/assignments/${targetId.value}/import-questions`
      await api.post(endpoint, { question_ids: [questionId] }).catch(() => {})
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
    speaking: 'bg-indigo-100 text-indigo-700', writing: 'bg-orange-100 text-orange-700',
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
      })
      if (q.options && Array.isArray(q.options)) {
        if (q.type === 'ordering') orderingItems.value = q.options.map(t => ({ text: String(t) }))
        else if (q.type === 'matching') matchingPairs.value = q.options
        else if (q.type === 'drag_drop') dragDropZones.value = q.options
        else formOptions.value = q.options.map(o => ({ id: o.id, text: o.text }))
      }
      if (q.type === 'multiple_select' && Array.isArray(q.correct_answer)) multiSelectAnswer.value = q.correct_answer
      else if (q.type === 'calculation' && q.correct_answer?.value !== undefined) Object.assign(calcAnswer, q.correct_answer)
      else if (q.type === 'fill_blank' && Array.isArray(q.correct_answer)) fillBlankAnswers.value = q.correct_answer
      else if (!['ordering', 'matching', 'drag_drop'].includes(q.type))
        form.correct_answer = Array.isArray(q.correct_answer) ? q.correct_answer.join(',') : (q.correct_answer ?? '')
      if (q.type === 'listening' && q.audio_url) listeningAudio.value = q.audio_url
    } catch { router.push('/teacher/question-bank') }
  }
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
