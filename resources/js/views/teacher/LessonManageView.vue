<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
      <div class="flex gap-2 flex-wrap">
        <select v-model="filters.classroom_id" @change="fetch" class="input w-44">
          <option value="">Tất cả lớp</option>
          <option v-for="c in allTeacherClassrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <select v-model="filters.status" @change="fetch" class="input w-40">
          <option value="">Tất cả trạng thái</option>
          <option value="draft">Bản nháp</option>
          <option value="published">Đã đăng</option>
        </select>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-[#d63015] text-white rounded-xl text-sm font-medium hover:bg-[#c02a10] transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tạo bài học
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="py-12 text-center text-gray-400">
        <div class="animate-spin w-6 h-6 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-2"></div>
        Đang tải...
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bài học</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Lớp / Môn</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Ngày tạo</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-if="lessons.length === 0">
            <td colspan="5" class="py-14 text-center text-gray-400">
              <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              Chưa có bài học nào
            </td>
          </tr>
          <tr v-for="l in lessons" :key="l.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <!-- Thumbnail preview in table -->
                <div class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-100 overflow-hidden shrink-0 hidden sm:block">
                  <img v-if="l.thumbnail" :src="l.thumbnail.startsWith('http') ? l.thumbnail : '/storage/' + l.thumbnail" class="w-full h-full object-cover" />
                  <span v-else class="w-full h-full flex items-center justify-center text-xs text-gray-300">📖</span>
                </div>
                <div class="min-w-0">
                  <p class="font-medium text-gray-900 truncate max-w-xs sm:max-w-md">{{ l.title }}</p>
                  <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-[10px] px-1.5 py-0.5 rounded-md font-medium" :class="visibilityClass(l.visibility)">
                      {{ visibilityLabel(l.visibility) }}
                    </span>
                    <span v-if="l.materials?.length" class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded-md font-medium">
                      {{ l.materials.length }} tài liệu
                    </span>
                    <p v-if="l.description" class="text-xs text-gray-400 truncate max-w-xs">{{ l.description }}</p>
                  </div>
                </div>
              </div>
            </td>
            <td class="px-5 py-3 hidden md:table-cell">
              <p class="text-sm text-gray-700">{{ l.classroom?.name ?? '—' }}</p>
              <p class="text-xs text-gray-400">{{ l.subject?.name ?? '—' }}</p>
            </td>
            <td class="px-5 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium"
                :class="l.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                {{ l.status === 'published' ? 'Đã đăng' : 'Bản nháp' }}
              </span>
            </td>
            <td class="px-5 py-3 text-gray-400 text-xs hidden lg:table-cell">{{ formatDate(l.created_at) }}</td>
            <td class="px-5 py-3 text-right">
              <div class="flex justify-end gap-1">
                <button v-if="l.status === 'draft'" @click="publishLesson(l)"
                  class="p-1.5 rounded-lg hover:bg-green-50 text-gray-400 hover:text-green-600 transition-colors" title="Đăng bài">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </button>
                <button @click="openShare(l)" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-500 transition-colors" title="Chia sẻ">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                  </svg>
                </button>
                <button @click="openEdit(l)" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors" title="Chỉnh sửa">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button @click="deleteLesson(l)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Xóa">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="pagination.last_page > 1" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ pagination.total }} bài học</p>
        <div class="flex gap-1">
          <button v-for="p in pagination.last_page" :key="p" @click="goPage(p)"
            class="w-8 h-8 rounded-lg text-xs font-medium transition-colors"
            :class="p === pagination.current_page ? 'bg-[#d63015] text-white' : 'hover:bg-gray-100 text-gray-600'">
            {{ p }}
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <AppModal v-model="modal" :title="editItem ? 'Chỉnh sửa bài học' : 'Tạo bài học mới'" size="xl">
      <!-- Tabs (Only displayed when editing) -->
      <div v-if="editItem" class="flex border-b border-gray-150 mb-5">
        <button
          type="button"
          @click="activeTab = 'basic'"
          class="px-4 py-2 border-b-2 font-semibold text-sm transition-colors cursor-pointer"
          :class="activeTab === 'basic' ? 'border-[#d63015] text-[#d63015]' : 'border-transparent text-gray-500 hover:text-gray-700'"
        >
          Thông tin chung
        </button>
        <button
          type="button"
          @click="activeTab = 'content'"
          class="px-4 py-2 border-b-2 font-semibold text-sm transition-colors cursor-pointer"
          :class="activeTab === 'content' ? 'border-[#d63015] text-[#d63015]' : 'border-transparent text-gray-500 hover:text-gray-700'"
        >
          Nội dung & Trợ lý
        </button>
        <button
          type="button"
          @click="activeTab = 'materials'"
          class="px-4 py-2 border-b-2 font-semibold text-sm transition-colors cursor-pointer"
          :class="activeTab === 'materials' ? 'border-[#d63015] text-[#d63015]' : 'border-transparent text-gray-500 hover:text-gray-700'"
        >
          Tài liệu học tập
        </button>
        <button
          type="button"
          @click="activeTab = 'questions'"
          class="px-4 py-2 border-b-2 font-semibold text-sm transition-colors cursor-pointer"
          :class="activeTab === 'questions' ? 'border-[#d63015] text-[#d63015]' : 'border-transparent text-gray-500 hover:text-gray-700'"
        >
          Câu hỏi luyện tập
        </button>
      </div>

      <form class="space-y-4" @submit.prevent>
        <!-- TAB 1: BASIC INFORMATION -->
        <div v-if="!editItem || activeTab === 'basic'" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tiêu đề <span class="text-red-500">*</span></label>
            <input v-model="form.title" class="input" placeholder="Tên bài học" required />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Mô tả ngắn</label>
            <textarea v-model="form.description" class="input resize-none" rows="2" placeholder="Mô tả ngắn về bài học"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Môn học <span class="text-red-500">*</span></label>
              <select v-model="form.subject_id" class="input" required @change="onSubjectChange">
                <option value="">Chọn môn</option>
                <option v-for="s in assignedSubjects" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Lớp học <span class="text-red-500">*</span></label>
              <select v-model="form.classroom_id" class="input" required>
                <option value="">Chọn lớp</option>
                <option v-for="c in filteredClassrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Trạng thái</label>
              <select v-model="form.status" class="input">
                <option value="draft">Bản nháp</option>
                <option value="published">Đăng ngay</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Hiển thị</label>
              <select v-model="form.visibility" class="input">
                <option value="private">Riêng tư</option>
                <option value="class">Cho lớp</option>
                <option value="public">Công khai</option>
              </select>
            </div>
          </div>

          <!-- Thumbnail Upload (Only edit mode) -->
          <div v-if="editItem" class="pt-4 border-t border-gray-100">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Ảnh bìa bài giảng</label>
            <div class="flex items-center gap-4">
              <div class="w-24 h-16 rounded-xl border border-gray-250 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                <img v-if="editItem.thumbnail" :src="editItem.thumbnail.startsWith('http') ? editItem.thumbnail : '/storage/' + editItem.thumbnail" class="w-full h-full object-cover" />
                <span v-else class="text-xs text-gray-400">Không có ảnh</span>
              </div>
              <div class="flex-1">
                <input type="file" accept="image/*" @change="onThumbnailSelect" class="hidden" ref="thumbnailFileInput" />
                <button type="button" @click="$refs.thumbnailFileInput.click()" :disabled="thumbnailUploading" class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold hover:bg-gray-50 transition-colors disabled:opacity-60 cursor-pointer">
                  {{ thumbnailUploading ? 'Đang tải...' : 'Tải ảnh bìa mới' }}
                </button>
                <p class="text-[10px] text-gray-400 mt-1">Hỗ trợ JPG, PNG, WEBP tối đa 5MB</p>
                <p v-if="thumbnailUploadError" class="text-xs text-red-500 mt-1">{{ thumbnailUploadError }}</p>
              </div>
            </div>
          </div>
          <div v-else class="p-3 bg-blue-50 text-blue-700 rounded-xl text-xs font-medium">
            💡 Sau khi lưu bài học lần đầu, bạn có thể tải lên ảnh bìa và đính kèm tài liệu học tập.
          </div>
        </div>

        <!-- TAB 2: CONTENT & SUBJECT-SPECIFIC ASSISTANT -->
        <div v-if="editItem && activeTab === 'content'" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Đường dẫn Video bài giảng (URL hoặc tên file)</label>
            <input v-model="form.video_path" class="input" placeholder="VD: https://www.w3schools.com/html/mov_bbb.mp4" />
          </div>

          <!-- Dynamic Subject-Specific Toolkit (Hộp công cụ trợ lý môn học) -->
          <div v-if="currentSubjectCode" class="border rounded-2xl p-4 bg-gray-50/50 shadow-sm transition-all duration-200" :class="subjectPanelClass">
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
              
              <div class="pt-2 border-t border-dashed border-emerald-200/60">
                <label class="block text-[11px] font-bold text-gray-700 mb-1">🧪 Bộ chuyển đổi nhanh phương trình Hóa học:</label>
                <div class="flex gap-2">
                  <input
                    v-model="chemFormulaInput"
                    type="text"
                    placeholder="Nhập nhanh (VD: Fe + CuSO4 -> FeSO4 + Cu)..."
                    class="flex-1 px-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white"
                    @keyup.enter="insertChemFormula"
                  />
                  <button
                    type="button"
                    @click="insertChemFormula"
                    class="px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs shadow-sm transition-colors cursor-pointer"
                  >
                    Chèn
                  </button>
                </div>
                <p v-if="chemFormulaInput" class="text-[10px] text-gray-400 mt-1">
                  Xem trước LaTeX: <span class="font-mono text-emerald-700">{{ formattedChemLatex }}</span>
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
                  class="px-3 py-1 rounded-xl text-xs font-semibold bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 transition-all cursor-pointer shadow-sm uppercase"
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
              <p class="text-xs text-cyan-700 font-medium">💡 Gợi ý: Tải lên các bản đồ học tập hoặc biểu đồ bằng Tab **Tài liệu học tập** để học sinh dễ theo dõi.</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nội dung bài học</label>
            <textarea ref="contentTextareaRef" v-model="form.content" class="input font-mono resize-none" rows="10" placeholder="Nhập nội dung bài học (hỗ trợ văn bản HTML hoặc công thức LaTeX)..."></textarea>
          </div>
        </div>

        <!-- TAB 3: MATERIALS (ATTACHMENTS) -->
        <div v-if="editItem && activeTab === 'materials'" class="space-y-4">
          <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center hover:border-gray-300 transition-colors">
            <input type="file" @change="onMaterialSelect" class="hidden" ref="materialFileInput" />
            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <button type="button" @click="$refs.materialFileInput.click()" :disabled="materialUploading" class="px-4 py-2 rounded-xl bg-gray-50 border border-gray-200 text-xs font-semibold hover:bg-gray-100 transition-colors disabled:opacity-60 cursor-pointer">
              {{ materialUploading ? 'Đang tải lên...' : 'Chọn tài liệu từ thiết bị' }}
            </button>
            <p class="text-[10px] text-gray-400 mt-1.5">PDF, DOC, DOCX, PPT, PPTX, MP4, MP3, ZIP... tối đa 50MB</p>
            <p v-if="materialUploadError" class="text-xs text-red-500 mt-1">{{ materialUploadError }}</p>
          </div>

          <div class="space-y-2">
            <h4 class="text-xs font-bold text-gray-700">Tài liệu học tập đính kèm ({{ materials.length }})</h4>
            <div v-if="materials.length === 0" class="text-xs text-gray-400 py-3 text-center bg-gray-50 rounded-xl">Chưa có tài liệu đính kèm nào</div>
            <div v-for="m in materials" :key="m.id" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-gray-50/50 transition-colors">
              <div class="flex items-center gap-2.5 min-w-0">
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 uppercase">{{ m.file_type }}</span>
                <span class="text-xs font-semibold text-gray-700 truncate max-w-sm">{{ m.file_name }}</span>
                <span class="text-[10px] text-gray-400 font-medium">({{ (m.file_size / 1024 / 1024).toFixed(2) }} MB)</span>
              </div>
              <button type="button" @click="removeMaterial(m)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors cursor-pointer" title="Xóa tài liệu">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- TAB 4: PRACTICE QUESTIONS -->
        <div v-if="editItem && activeTab === 'questions'" class="space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-gray-150">
            <h4 class="text-sm font-bold text-gray-700">Câu hỏi luyện tập của bài học ({{ lessonQuestions.length }})</h4>
            <button type="button" @click="openCreateQuestion" class="flex items-center gap-1.5 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-xs font-semibold transition-colors cursor-pointer shadow-sm">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Tạo câu hỏi mới
            </button>
          </div>

          <div v-if="lessonQuestions.length === 0" class="text-xs text-gray-400 py-6 text-center bg-gray-50 rounded-xl">
            Chưa có câu hỏi luyện tập nào cho bài học này. Hãy tạo một câu hỏi để học sinh ôn tập!
          </div>

          <div v-else class="space-y-3 max-h-[350px] overflow-y-auto pr-1">
            <div v-for="(q, idx) in lessonQuestions" :key="q.id" class="p-3.5 rounded-xl border border-gray-150 bg-white hover:shadow-sm transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
              <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 uppercase">{{ q.type }}</span>
                  <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 uppercase">{{ q.difficulty === 'easy' ? 'Dễ' : q.difficulty === 'hard' ? 'Khó' : 'TB' }}</span>
                  <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">{{ q.points }}đ</span>
                </div>
                <p class="text-xs text-gray-700 line-clamp-2 leading-relaxed" v-html="renderMath(q.content)"></p>
              </div>
              <div class="flex items-center gap-1 shrink-0 self-end sm:self-center">
                <button type="button" @click="openEditQuestion(q)" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors cursor-pointer" title="Sửa">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button type="button" @click="deleteQuestion(q)" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors cursor-pointer" title="Xóa">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="formError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ formError }}</div>
      </form>
      <template #footer>
        <button @click="modal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
        <button @click="save" :disabled="saving" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] disabled:opacity-60 font-semibold cursor-pointer">
          {{ saving ? 'Đang lưu...' : 'Lưu bài học' }}
        </button>
      </template>
    </AppModal>

    <!-- Question Form Modal for Lesson Practice Questions -->
    <QuestionFormModal
      v-model="questionModalOpen"
      :edit-item="editQuestionItem"
      :subject-code="currentSubjectCode"
      :saving="savingQuestion"
      :error="questionError"
      @save="savePracticeQuestion"
    />

    <!-- Share Modal -->
    <AppModal v-model="shareModal" title="Chia sẻ bài học" size="sm">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Chế độ chia sẻ</label>
          <div class="space-y-2">
            <label v-for="opt in visibilityOptions" :key="opt.value"
              class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
              :class="shareForm.visibility === opt.value ? 'border-[#d63015] bg-red-50' : 'border-gray-200 hover:border-gray-300'">
              <input type="radio" :value="opt.value" v-model="shareForm.visibility" class="mt-0.5 text-[#d63015]" />
              <div>
                <p class="text-sm font-medium text-gray-800">{{ opt.label }}</p>
                <p class="text-xs text-gray-400">{{ opt.desc }}</p>
              </div>
            </label>
          </div>
        </div>
        <div v-if="shareForm.visibility === 'class'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Chọn lớp</label>
          <select v-model="shareForm.classroom_id" class="input">
            <option value="">Chọn lớp</option>
            <option v-for="c in allTeacherClassrooms" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div v-if="shareForm.visibility === 'selected'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Mã học sinh (cách nhau bởi dấu phẩy)</label>
          <input v-model="shareForm.student_codes" class="input" placeholder="VD: HS001, HS002" />
        </div>
        <div v-if="shareError" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ shareError }}</div>
      </div>
      <template #footer>
        <button @click="shareModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-gray-50">Hủy</button>
        <button @click="doShare" :disabled="sharing" class="px-4 py-2 rounded-xl bg-[#d63015] text-white text-sm hover:bg-[#c02a10] disabled:opacity-60 font-medium">
          {{ sharing ? 'Đang lưu...' : 'Lưu chia sẻ' }}
        </button>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'
import { useToastStore }   from '@stores/toast'
import { useConfirmStore } from '@stores/confirm'
import QuestionFormModal from '@components/common/QuestionFormModal.vue'
import { renderMath } from '@/utils/math'

const toast        = useToastStore()
const confirmStore = useConfirmStore()

const lessons = ref([])
const loading = ref(true)
const modal = ref(false)
const shareModal = ref(false)
const editItem = ref(null)
const shareTarget = ref(null)
const saving = ref(false)
const sharing = ref(false)
const formError = ref('')
const shareError = ref('')
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const filters = reactive({ classroom_id: '', status: '', page: 1 })
const form = reactive({ title: '', description: '', classroom_id: '', subject_id: '', status: 'draft', visibility: 'private', content: '', video_path: '' })
const shareForm = reactive({ visibility: 'class', classroom_id: '', student_codes: '' })

// State for Tabs & Files Uploads
const activeTab = ref('basic') // 'basic' | 'content' | 'materials' | 'questions'
const materials = ref([])
const thumbnailUploading = ref(false)
const thumbnailUploadError = ref('')
const materialUploading = ref(false)
const materialUploadError = ref('')

// Practice Questions states
const lessonQuestions = ref([])
const questionModalOpen = ref(false)
const editQuestionItem = ref(null)
const savingQuestion = ref(false)
const questionError = ref('')

const thumbnailFileInput = ref(null)
const materialFileInput = ref(null)
const contentTextareaRef = ref(null)

// Teacher Assignments & Subject Dynamic Filtering
const teacherAssignments = ref([])

const assignedSubjects = computed(() => {
  return teacherAssignments.value.map(a => a.subject).filter(Boolean)
})

const filteredClassrooms = computed(() => {
  if (!form.subject_id) return []
  const assignment = teacherAssignments.value.find(a => a.subject?.id === form.subject_id)
  return assignment ? (assignment.classrooms ?? []) : []
})

const allTeacherClassrooms = computed(() => {
  const map = new Map()
  teacherAssignments.value.forEach(a => {
    if (a.classrooms) {
      a.classrooms.forEach(c => {
        map.set(c.id, c)
      })
    }
  })
  return Array.from(map.values())
})

function onSubjectChange() {
  const availableClassrooms = filteredClassrooms.value
  if (!availableClassrooms || !availableClassrooms.some(c => c.id === form.classroom_id)) {
    form.classroom_id = ''
  }
}

// Subject-Specific Toolkit Computeds
const currentSubjectCode = computed(() => {
  const sub = assignedSubjects.value.find(s => s?.id === form.subject_id)
  return sub ? sub.code : ''
})

const subjectIcon = computed(() => {
  const code = currentSubjectCode.value
  if (code === 'MATH') return '📐'
  if (code === 'PHYSICS') return '⚡'
  if (code === 'CHEMISTRY') return '🧪'
  if (code === 'ENGLISH') return '🇬🇧'
  if (code === 'LITERATURE') return '📝'
  if (code === 'INFORMATICS') return '💻'
  if (code === 'HISTORY') return '⏳'
  if (code === 'GEOGRAPHY') return '🗺️'
  return '📚'
})

const subjectTitle = computed(() => {
  const code = currentSubjectCode.value
  if (code === 'MATH') return 'Toán học'
  if (code === 'PHYSICS') return 'Vật lý'
  if (code === 'CHEMISTRY') return 'Hóa học'
  if (code === 'ENGLISH') return 'Tiếng Anh'
  if (code === 'LITERATURE') return 'Ngữ văn'
  if (code === 'INFORMATICS') return 'Tin học'
  if (code === 'HISTORY') return 'Lịch sử'
  if (code === 'GEOGRAPHY') return 'Địa lý'
  return 'Chung'
})

const subjectPanelClass = computed(() => {
  const code = currentSubjectCode.value
  if (code === 'MATH' || code === 'PHYSICS') return 'border-amber-250 bg-amber-50/20'
  if (code === 'CHEMISTRY') return 'border-emerald-250 bg-emerald-50/20'
  if (code === 'ENGLISH') return 'border-blue-250 bg-blue-50/20'
  if (code === 'LITERATURE') return 'border-rose-250 bg-rose-50/20'
  if (code === 'INFORMATICS') return 'border-indigo-250 bg-indigo-50/20'
  if (code === 'HISTORY' || code === 'GEOGRAPHY') return 'border-orange-250 bg-orange-50/20'
  return 'border-gray-250 bg-gray-50/40'
})

// Symbol Toolkits Lists & Methods
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
  const formatted = `\n<blockquote class="pl-4 border-l-4 border-red-400 my-3 italic text-gray-600 whitespace-pre-line">\n${literatureQuoteInput.value.trim()}\n</blockquote>\n`
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

const visibilityOptions = [
  { value: 'private',  label: 'Riêng tư',     desc: 'Chỉ bạn mới thấy' },
  { value: 'class',    label: 'Cho lớp',       desc: 'Chia sẻ cho một lớp học cụ thể' },
  { value: 'public',   label: 'Công khai',     desc: 'Tất cả học sinh đều có thể tìm thấy' },
  { value: 'selected', label: 'Chọn học sinh', desc: 'Chia sẻ cho từng học sinh theo mã' },
]

async function fetch() {
  loading.value = true
  try {
    const { data } = await api.get('/teacher/lessons', { params: filters })
    lessons.value = data.data ?? []
    pagination.value = { 
      current_page: data.meta?.current_page ?? 1, 
      last_page: data.meta?.last_page ?? 1, 
      total: data.meta?.total ?? 0 
    }
  } finally { loading.value = false }
}

function goPage(p) { filters.page = p; fetch() }

function openCreate() {
  editItem.value = null
  activeTab.value = 'basic'
  Object.assign(form, { title: '', description: '', classroom_id: '', subject_id: '', status: 'draft', visibility: 'private', content: '', video_path: '' })
  formError.value = ''
  materials.value = []
  modal.value = true
}

async function openEdit(l) {
  editItem.value = l
  activeTab.value = 'basic'
  Object.assign(form, { 
    title: l.title, 
    description: l.description ?? '', 
    classroom_id: l.classroom?.id ?? '', 
    subject_id: l.subject?.id ?? '', 
    status: l.status, 
    visibility: l.visibility ?? 'private',
    content: l.content ?? '',
    video_path: l.video_path ?? ''
  })
  formError.value = ''
  materials.value = []
  lessonQuestions.value = []
  modal.value = true

  // Fetch full details of the lesson (includes attachments / materials)
  try {
    const { data } = await api.get(`/teacher/lessons/${l.id}`)
    const full = data.data
    form.content = full.content ?? ''
    form.video_path = full.video_path ?? ''
    materials.value = full.materials ?? []
    // Sync thumbnail
    editItem.value.thumbnail = full.thumbnail

    // Fetch practice questions
    await fetchLessonQuestions()
  } catch (e) {
    toast.error('Không thể tải chi tiết tài liệu học tập')
  }
}

function openShare(l) {
  shareTarget.value = l
  Object.assign(shareForm, { visibility: l.visibility ?? 'class', classroom_id: l.classroom?.id ?? '', student_codes: '' })
  shareError.value = ''
  shareModal.value = true
}

async function save() {
  saving.value = true; formError.value = ''
  try {
    const payload = { ...form }
    if (!payload.classroom_id) delete payload.classroom_id
    if (editItem.value) await api.put(`/teacher/lessons/${editItem.value.id}`, payload)
    else await api.post('/teacher/lessons', payload)
    modal.value = false; fetch()
    toast.success(editItem.value ? 'Cập nhật bài học thành công' : 'Tạo bài học thành công')
  } catch (e) { formError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { saving.value = false }
}

async function doShare() {
  sharing.value = true; shareError.value = ''
  try {
    const payload = { visibility: shareForm.visibility }
    if (shareForm.visibility === 'class') payload.classroom_id = shareForm.classroom_id
    if (shareForm.visibility === 'selected') payload.student_codes = shareForm.student_codes.split(',').map(s => s.trim()).filter(Boolean)
    await api.post(`/teacher/lessons/${shareTarget.value.id}/share`, payload)
    shareModal.value = false; fetch()
    toast.success('Chia sẻ bài học thành công')
  } catch (e) { shareError.value = e.response?.data?.message ?? 'Có lỗi xảy ra' }
  finally { sharing.value = false }
}

async function publishLesson(l) {
  try { 
    await api.post(`/teacher/lessons/${l.id}/publish`)
    fetch() 
    toast.success('Đã đăng bài học thành công')
  }
  catch (e) { toast.error(e.response?.data?.message ?? 'Không thể đăng bài') }
}

async function deleteLesson(l) {
  if (!await confirmStore.ask(`Xóa bài học "${l.title}"?`)) return
  try { 
    await api.delete(`/teacher/lessons/${l.id}`)
    fetch() 
    toast.success('Đã xóa bài học thành công')
  }
  catch (e) { toast.error(e.response?.data?.message ?? 'Không thể xóa') }
}

// Media upload methods
async function onThumbnailSelect(e) {
  const file = e.target.files[0]
  if (!file) return
  if (!editItem.value) {
    toast.error('Vui lòng lưu bài học trước khi tải lên ảnh bìa.')
    return
  }
  thumbnailUploading.value = true
  thumbnailUploadError.value = ''
  try {
    const formData = new FormData()
    formData.append('thumbnail', file)
    const { data } = await api.post(`/teacher/lessons/${editItem.value.id}/thumbnail`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    editItem.value.thumbnail = data.thumbnail_url
    toast.success('Cập nhật ảnh bìa thành công')
  } catch (err) {
    thumbnailUploadError.value = err.response?.data?.message ?? 'Lỗi tải ảnh'
    toast.error(thumbnailUploadError.value)
  } finally {
    thumbnailUploading.value = false
  }
}

async function onMaterialSelect(e) {
  const file = e.target.files[0]
  if (!file) return
  if (!editItem.value) {
    toast.error('Vui lòng lưu bài học trước khi tải lên tài liệu.')
    return
  }
  materialUploading.value = true
  materialUploadError.value = ''
  try {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('file_name', file.name)
    const { data } = await api.post(`/teacher/lessons/${editItem.value.id}/materials`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    materials.value.push(data.data ?? data)
    toast.success('Tải tài liệu lên thành công')
  } catch (err) {
    materialUploadError.value = err.response?.data?.message ?? 'Lỗi tải tài liệu'
    toast.error(materialUploadError.value)
  } finally {
    materialUploading.value = false
    e.target.value = '' // Reset file input
  }
}

async function removeMaterial(m) {
  if (!confirm(`Bạn có chắc muốn xóa tài liệu "${m.file_name}"?`)) return
  try {
    await api.delete(`/teacher/lessons/${editItem.value.id}/materials/${m.id}`)
    materials.value = materials.value.filter(item => item.id !== m.id)
    toast.success('Đã xóa tài liệu')
  } catch (err) {
    toast.error(err.response?.data?.message ?? 'Lỗi khi xóa tài liệu')
  }
}

async function fetchLessonQuestions() {
  if (!editItem.value) return
  try {
    const { data } = await api.get('/teacher/question-bank', {
      params: { lesson_id: editItem.value.id }
    })
    lessonQuestions.value = data.data?.data ?? []
  } catch (err) {
    toast.error(err.response?.data?.message ?? 'Lỗi khi tải câu hỏi')
  }
}

function openCreateQuestion() {
  editQuestionItem.value = null
  questionModalOpen.value = true
  questionError.value = ''
}

function openEditQuestion(q) {
  editQuestionItem.value = q
  questionModalOpen.value = true
  questionError.value = ''
}

async function savePracticeQuestion(payload) {
  savingQuestion.value = true
  questionError.value = ''
  try {
    payload.lesson_id = editItem.value.id
    payload.subject_id = editItem.value.subject?.id || form.subject_id
    payload.default_points = payload.points

    if (editQuestionItem.value) {
      await api.put(`/teacher/question-bank/${editQuestionItem.value.id}`, payload)
      toast.success('Cập nhật câu hỏi thành công')
    } else {
      await api.post('/teacher/question-bank', payload)
      toast.success('Tạo câu hỏi thành công')
    }
    questionModalOpen.value = false
    await fetchLessonQuestions()
  } catch (err) {
    questionError.value = err.response?.data?.message ?? 'Đã xảy ra lỗi khi lưu câu hỏi'
    toast.error(questionError.value)
  } finally {
    savingQuestion.value = false
  }
}

async function deleteQuestion(q) {
  if (!await confirmStore.ask(`Bạn có chắc chắn muốn xóa câu hỏi này khỏi bài học không?`)) return
  try {
    await api.delete(`/teacher/question-bank/${q.id}`)
    toast.success('Xóa câu hỏi thành công')
    await fetchLessonQuestions()
  } catch (err) {
    toast.error(err.response?.data?.message ?? 'Lỗi khi xóa câu hỏi')
  }
}

function visibilityLabel(v) {
  return { public: 'Công khai', private: 'Riêng tư', class: 'Lớp học', selected: 'Được chọn' }[v] ?? v
}
function visibilityClass(v) {
  return { public: 'bg-green-50 text-green-600', private: 'bg-gray-100 text-gray-500', class: 'bg-blue-50 text-blue-600', selected: 'bg-red-50 text-[#d63015]' }[v] ?? ''
}
function formatDate(iso) { return iso ? new Date(iso).toLocaleDateString('vi-VN') : '' }

onMounted(async () => {
  try {
    const { data } = await api.get('/teacher/my-subjects')
    teacherAssignments.value = data.data ?? []
  } catch {
    teacherAssignments.value = []
  }
  fetch()
})
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
