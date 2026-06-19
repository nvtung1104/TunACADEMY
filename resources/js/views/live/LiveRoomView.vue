<template>
  <div class="h-screen bg-[#f8f7f4] flex flex-col overflow-hidden select-none font-sans text-gray-800">

    <!-- Top bar -->
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200/60 bg-white shrink-0 z-10 shadow-sm">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-[#7c3aed] flex items-center justify-center shadow-md shadow-[#7c3aed]/20">
          <svg class="w-4.5 h-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <p class="text-gray-900 font-extrabold text-sm leading-none">{{ session?.title }}</p>
          <p class="text-[11px] text-gray-500 mt-1.5 flex items-center gap-2">
            <span class="px-1.5 py-0.5 rounded bg-[#7c3aed]/10 border border-[#7c3aed]/20 text-[#7c3aed] font-bold">{{ session?.classroom?.name }}</span>
            <span class="text-gray-300">•</span>
            <span class="text-[#7c3aed] font-bold">{{ session?.subject?.name }}</span>
          </p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200/60 px-3 py-1.5 rounded-full">
          <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"/>
          {{ timerDisplay }}
        </div>
        <div class="text-xs text-gray-700 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-full font-mono font-semibold">{{ session?.room_code }}</div>
        <div class="text-xs font-bold text-gray-600 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-full flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          {{ participants.length }}
        </div>
      </div>
    </div>

    <!-- Main area -->
    <div class="flex-1 flex overflow-hidden min-h-0 relative">

      <!-- Presentation panel -->
      <div v-if="activePresentationType !== 'none' && !hidePresentation" class="flex-1 flex flex-col bg-white p-5 overflow-y-auto border-r border-gray-200/60 scrollbar-thin">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
          <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-[#7c3aed]/10 text-[#7c3aed] border border-[#7c3aed]/20 uppercase tracking-wider">
              {{ activePresentationType === 'lesson' ? 'Đang giảng bài' : 'Đang sửa bài tập' }}
            </span>
            <h2 class="text-gray-900 font-extrabold text-base">{{ activePresentationTitle }}</h2>
          </div>
          <button v-if="isHost" @click="stopPresenting" class="px-3.5 py-1.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition-all shadow-md active:scale-95 flex items-center gap-1.5">
            ❌ Dừng trình chiếu
          </button>
        </div>

        <!-- Presenter Details content -->
        <div class="text-gray-700 space-y-5">
          <!-- Lesson presentation -->
          <div v-if="activePresentationType === 'lesson' && activeLesson" class="space-y-5">
            <div v-if="activeLesson.thumbnail" class="w-full max-h-52 rounded-2xl overflow-hidden mb-4 border border-gray-100 shadow-sm">
              <img :src="activeLesson.thumbnail" class="w-full h-full object-cover" />
            </div>
            
            <h3 class="text-gray-950 font-extrabold text-xl border-l-4 border-[#7c3aed] pl-3 leading-tight">
               {{ activeLesson.title }}
            </h3>
            <p class="text-sm text-gray-600 italic bg-[#f8f7f4] p-3.5 rounded-xl border border-gray-100 leading-relaxed">{{ activeLesson.description }}</p>

            <!-- Attachments / Materials download -->
            <div v-if="activeLesson.materials && activeLesson.materials.length" class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
              <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <span>📂 Tài liệu học tập đính kèm:</span>
              </p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <a 
                  v-for="m in activeLesson.materials" 
                  :key="m.id" 
                  :href="m.file_path" 
                  download 
                  target="_blank"
                  class="flex items-center justify-between p-3 rounded-xl bg-white border border-gray-200 hover:bg-[#7c3aed]/5 hover:border-[#7c3aed] text-gray-800 transition-all text-xs font-bold group shadow-sm"
                >
                  <span class="truncate max-w-[80%]">📄 {{ m.title }}</span>
                  <span class="text-[10px] text-[#7c3aed] group-hover:underline transition-colors">Tải về 📥</span>
                </a>
              </div>
            </div>

            <!-- HTML Lesson Body Content -->
            <div class="prose max-w-none text-sm leading-relaxed bg-[#f8f7f4]/60 border border-gray-100 p-6 rounded-2xl text-gray-700" v-html="renderMath(activeLesson.content || 'Bài giảng chưa có nội dung văn bản.')" />
          </div>

          <!-- Assignment presentation -->
          <div v-if="activePresentationType === 'assignment' && activeAssignment" class="space-y-5">
            <h3 class="text-gray-950 font-extrabold text-xl border-l-4 border-[#7c3aed] pl-3 leading-tight">
              {{ activeAssignment.title }}
            </h3>
            <p class="text-sm text-gray-600 italic bg-[#f8f7f4] p-3.5 rounded-xl border border-gray-100 leading-relaxed">{{ activeAssignment.description }}</p>
            
            <!-- Questions lists -->
            <div class="space-y-4 pt-3">
              <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Danh sách câu hỏi luyện tập:</p>
              <div 
                v-for="(q, idx) in activeAssignment.questions" 
                :key="q.id" 
                class="p-5 rounded-2xl bg-white border border-gray-150 space-y-3.5 shadow-sm"
              >
                <div class="flex items-start gap-3">
                  <span class="w-6 h-6 rounded-lg bg-[#7c3aed] text-white flex items-center justify-center text-xs font-extrabold shrink-0 mt-0.5 shadow-md shadow-[#7c3aed]/10">
                    {{ idx + 1 }}
                  </span>
                  <div class="text-gray-900 text-sm font-semibold leading-relaxed" v-html="renderMath(q.question)" />
                </div>
                <!-- Options list -->
                <div v-if="q.options" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pl-9">
                  <div 
                    v-for="(opt, oIdx) in q.options" 
                    :key="oIdx" 
                    class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 text-xs text-gray-700 leading-snug"
                  >
                    <span class="font-extrabold text-[#7c3aed] uppercase mr-1.5">{{ ['A','B','C','D','E','F'][oIdx] }}.</span>
                    <span v-html="renderMath(opt)" />
                  </div>
                </div>
                <!-- Correct answers block -->
                <div class="bg-[#7c3aed]/5 border border-[#7c3aed]/10 p-4 rounded-xl text-xs space-y-1.5 pl-9">
                  <p class="font-bold text-[#7c3aed]">Đáp án chuẩn: {{ Array.isArray(q.answer) ? q.answer.join(', ') : q.answer }}</p>
                  <p class="text-gray-500 leading-relaxed" v-if="q.explanation">Giải thích: <span v-html="renderMath(q.explanation)" /></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Screen Share Presentation panel -->
      <div v-if="activePresentationType === 'none' && screenSharingParticipant && !hidePresentation" class="flex-1 flex flex-col bg-[#0d0e1c] p-5 overflow-hidden border-r border-gray-200/60 relative">
        <div class="absolute top-5 left-5 z-10 flex items-center gap-3 bg-black/60 px-3.5 py-2 rounded-xl backdrop-blur-sm shadow-md">
          <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"/>
          <span class="text-xs font-bold text-white uppercase tracking-wider">
            {{ screenSharingParticipant.name }} đang chia sẻ màn hình
          </span>
        </div>
        
        <div class="flex-1 flex items-center justify-center min-h-0 w-full h-full relative">
          <video
            ref="presentationVideo"
            autoplay
            playsinline
            class="w-full h-full object-contain"
          />
        </div>
      </div>

      <!-- Speaker View Presentation panel -->
      <div v-if="activePresentationType === 'none' && !screenSharingParticipant && mainSpeaker && !hidePresentation" class="flex-1 flex flex-col bg-[#0d0e1c] p-5 overflow-hidden border-r border-gray-200/60 relative">
        <div class="absolute top-5 left-5 z-10 flex items-center gap-3 bg-black/60 px-3.5 py-2 rounded-xl backdrop-blur-sm shadow-md">
          <span class="w-2 h-2 rounded-full bg-[#7c3aed] animate-pulse"/>
          <span class="text-xs font-bold text-white uppercase tracking-wider">
            {{ mainSpeaker.name }}{{ mainSpeaker.isMe ? ' (Bạn)' : '' }}
          </span>
        </div>
        
        <div class="flex-1 flex items-center justify-center min-h-0 w-full h-full relative">
          <video
            ref="speakerVideo"
            autoplay
            playsinline
            :muted="mainSpeaker.isMe"
            class="w-full h-full object-contain"
          />
        </div>
      </div>

      <!-- Video Grid panel -->
      <div 
        v-if="displayParticipants.length > 0 || hidePresentation"
        :class="(hasMainPresentation && !hidePresentation) ? 'w-64 p-3 flex flex-col gap-3 overflow-y-auto bg-[#fafafa] shrink-0 border-r border-gray-200/60 scrollbar-thin' : 'flex-1 overflow-hidden p-3 relative'"
      >
        <div 
          :class="(hasMainPresentation && !hidePresentation) ? 'flex flex-col gap-3 w-full' : 'h-full grid gap-2.5 ' + gridClass"
        >
          <div v-for="p in displayParticipants" :key="p.userId"
            class="relative rounded-2xl overflow-hidden bg-[#0d0e1c] transition-all duration-300 border border-gray-200 shrink-0"
            :class="[
              p.userId === speakingId ? 'ring-2 ring-green-400' : 'ring-1 ring-gray-200',
              (hasMainPresentation && !hidePresentation) ? 'h-36 w-full shadow-md' : 'h-full shadow-sm'
            ]"
          >
            <!-- High Definition Video Stream -->
            <video
              v-show="hidePresentation || (!p.sharing && p.userId !== mainSpeaker?.userId)"
              :ref="getVideoRefHandler(p.userId)"
              autoplay playsinline :muted="p.isMe"
              class="w-full h-full transition-all duration-200"
              :class="[
                p.camOn ? 'opacity-100' : 'opacity-0',
                (p.fitMode || (p.sharing ? 'contain' : 'cover')) === 'contain' ? 'object-contain' : 'object-cover'
              ]"
            />

            <!-- Avatar when cam off or sharing screen/speaker (and not hidden presentation) -->
            <div v-if="!p.camOn || (!hidePresentation && (p.sharing || p.userId === mainSpeaker?.userId))" class="absolute inset-0 flex items-center justify-center bg-[#0d0e1c]">
              <div 
                class="rounded-full bg-gradient-to-br from-[#7c3aed] to-indigo-400 flex items-center justify-center font-bold text-white shadow-inner shadow-black/20"
                :class="(hasMainPresentation && !hidePresentation) ? 'w-12 h-12 text-base' : 'w-16 h-16 text-2xl'"
              >
                {{ getInitials(p.name) }}
              </div>
            </div>

            <!-- Name overlay bar -->
            <div class="absolute bottom-0 left-0 right-0 flex items-center justify-between px-3 py-2 bg-gradient-to-t from-black/80 to-transparent">
              <span class="text-[10px] text-white font-semibold truncate max-w-[70%]">
                {{ p.name }}{{ p.isMe ? ' (Bạn)' : '' }}{{ p.isHost ? ' 👑' : '' }}
              </span>
              <div class="flex items-center gap-1.5 shrink-0 ml-1">
                <!-- Fit/Fill toggle button -->
                <button 
                  @click="toggleFitMode(p)" 
                  class="w-5 h-5 rounded bg-black/40 hover:bg-black/60 text-white flex items-center justify-center transition-colors"
                  :title="(p.fitMode || (p.sharing ? 'contain' : 'cover')) === 'contain' ? 'Đầy khung hình' : 'Vừa khung hình'"
                >
                  <svg v-if="(p.fitMode || (p.sharing ? 'contain' : 'cover')) === 'contain'" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4h4M20 8V4h-4M4 16v4h4m16-4v4h-4" />
                  </svg>
                  <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 4H4v6m14-6h6v6m-6 14h6v-6M10 20H4v-6" />
                  </svg>
                </button>
                <div v-if="!p.micOn" class="w-4.5 h-4.5 rounded-full bg-red-500 flex items-center justify-center shrink-0 shadow-sm">
                  <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5.586 15H4a1 1 0 01-1-1V9a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Connecting loader -->
            <div v-if="!p.isMe && p.status === 'connecting'" class="absolute inset-0 flex flex-col items-center justify-center bg-black/70">
              <div class="w-5 h-5 border-2 border-white/20 border-t-white rounded-full animate-spin mb-1.5"/>
              <p class="text-[10px] text-gray-400">Kết nối...</p>
            </div>
          </div>
        </div>

        <!-- Pagination controls for Gallery View -->
        <div v-if="hidePresentation && participants.length > participantsPerPage" class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-black/60 px-4 py-2 rounded-2xl backdrop-blur-md shadow-lg flex items-center gap-4 z-20">
          <button 
            @click="prevGridPage" 
            :disabled="gridPage === 0"
            class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 disabled:opacity-30 disabled:hover:bg-white/10 text-white flex items-center justify-center transition-all active:scale-95"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          
          <span class="text-xs font-bold text-white font-mono">
            Trang {{ gridPage + 1 }} / {{ Math.ceil(participants.length / participantsPerPage) }}
          </span>
          
          <button 
            @click="nextGridPage" 
            :disabled="gridPage >= Math.ceil(participants.length / participantsPerPage) - 1"
            class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 disabled:opacity-30 disabled:hover:bg-white/10 text-white flex items-center justify-center transition-all active:scale-95"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Chat sidebar -->
      <transition name="slide">
        <div v-if="showChat" class="w-80 flex flex-col border-l border-gray-200 bg-white shrink-0 z-10 shadow-lg">
          <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <p class="text-gray-900 font-extrabold text-sm">Trò chuyện</p>
            <button @click="showChat = false; unreadCount = 0" class="text-gray-500 hover:text-gray-900">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-4 min-h-0 scrollbar-thin">
            <div v-if="!messages.length" class="text-center text-gray-400 text-xs pt-8 font-medium">Chưa có tin nhắn nào</div>
            <div v-for="msg in messages" :key="msg.id" class="flex gap-2"
              :class="msg.userId === myUserId ? 'flex-row-reverse' : ''">
              <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#7c3aed] to-indigo-400 flex items-center justify-center text-[10px] font-bold text-white shrink-0 shadow-sm">
                {{ getInitials(msg.name) }}
              </div>
              <div class="max-w-[200px]">
                <p class="text-[10px] text-gray-400 mb-1" :class="msg.userId === myUserId ? 'text-right' : ''">{{ msg.name }}</p>
                <div class="px-3 py-2 rounded-2xl text-xs leading-relaxed font-medium shadow-sm"
                  :class="msg.userId === myUserId ? 'bg-[#7c3aed] rounded-tr-none text-white font-semibold' : 'bg-gray-100 rounded-tl-none text-gray-800'">
                  {{ msg.text }}
                </div>
              </div>
            </div>
          </div>
          <div class="p-3 border-t border-gray-100 shrink-0 bg-white">
            <div class="flex gap-2">
              <input v-model="chatInput" @keydown.enter="sendChat" type="text" placeholder="Nhắn tin..."
                class="flex-1 px-3 py-2 rounded-xl bg-gray-50 border border-gray-200 text-gray-800 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#7c3aed]/20 focus:border-[#7c3aed] transition-all"/>
              <button @click="sendChat" :disabled="!chatInput.trim()"
                class="w-9 h-9 rounded-xl bg-[#7c3aed] hover:bg-[#6d28d9] text-white flex items-center justify-center disabled:opacity-40 shrink-0 shadow-md shadow-[#7c3aed]/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Participants sidebar -->
      <transition name="slide">
        <div v-if="showParticipants" class="w-60 flex flex-col border-l border-gray-200 bg-white shrink-0 z-10 shadow-lg">
          <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <p class="text-gray-900 font-extrabold text-sm">Thành viên ({{ participants.length }})</p>
            <button @click="showParticipants = false" class="text-gray-500 hover:text-gray-900">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="flex-1 overflow-y-auto p-3 space-y-1 scrollbar-thin">
            <div v-for="p in participants" :key="p.userId"
              class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#7c3aed] to-indigo-400 flex items-center justify-center text-xs font-bold text-white shrink-0 shadow-sm">
                {{ getInitials(p.name) }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-gray-800 text-xs font-bold truncate">{{ p.name }}</p>
                <p class="text-gray-500 text-[9px] font-semibold">{{ p.isHost ? '👑 Giáo viên' : 'Học sinh' }}</p>
              </div>
              <div class="flex items-center gap-1 shrink-0">
                <div class="w-4.5 h-4.5 flex items-center justify-center rounded-full" :class="!p.micOn ? 'bg-red-500' : 'text-gray-400 hover:text-gray-600'">
                  <svg class="w-3 h-3" :class="p.micOn ? 'text-current' : 'text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                  </svg>
                </div>
                <div class="w-4.5 h-4.5 flex items-center justify-center rounded-full" :class="!p.camOn ? 'bg-red-500' : 'text-gray-400 hover:text-gray-600'">
                  <svg class="w-3 h-3" :class="p.camOn ? 'text-current' : 'text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>

    <!-- Bottom controls -->
    <div class="shrink-0 flex items-center justify-between px-5 py-4 border-t border-gray-200 bg-white z-10 shadow-md">
      <!-- Left controls -->
      <div class="flex items-center gap-2">
        <button @click="toggleMic" class="flex flex-col items-center gap-1.5 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-bold shadow-sm"
          :class="micOn ? 'bg-gray-100 hover:bg-gray-200 text-gray-700' : 'bg-red-500 hover:bg-red-600 text-white'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path v-if="micOn" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1V9a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
          </svg>
          {{ micOn ? 'Tắt mic' : 'Bật mic' }}
        </button>

        <button @click="toggleCam" class="flex flex-col items-center gap-1.5 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-bold shadow-sm"
          :class="camOn ? 'bg-gray-100 hover:bg-gray-200 text-gray-700' : 'bg-red-500 hover:bg-red-600 text-white'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
          {{ camOn ? 'Tắt cam' : 'Bật cam' }}
        </button>

        <button @click="toggleScreenShare" class="flex flex-col items-center gap-1.5 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-bold shadow-sm"
          :class="sharing ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          {{ sharing ? 'Dừng chia sẻ' : 'Chia sẻ màn hình' }}
        </button>

        <!-- Only show cams toggle -->
        <button 
          @click="hidePresentation = !hidePresentation" 
          class="flex flex-col items-center gap-1.5 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-bold shadow-sm"
          :class="hidePresentation ? 'bg-[#7c3aed] text-white shadow-md shadow-[#7c3aed]/20' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
          :title="hidePresentation ? 'Hiển thị lại bài học/chia sẻ' : 'Ẩn bài học để chỉ xem thành viên'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
          {{ hidePresentation ? (activePresentationType !== 'none' ? 'Xem bài học' : (screenSharingParticipant ? 'Xem chia sẻ' : 'Xem trình bày')) : 'Xem thành viên' }}
        </button>
      </div>

      <!-- Center controls -->
      <div class="flex items-center gap-2">
        <!-- Presentation selection (only teacher/host can load materials) -->
        <button 
          v-if="isHost" 
          @click="showPresentModal = true; loadPresentItems()" 
          class="flex flex-col items-center gap-1.5 px-5 py-2.5 rounded-2xl transition-all text-[11px] font-extrabold bg-[#7c3aed] hover:bg-[#6d28d9] text-white active:scale-95 shadow-md shadow-[#7c3aed]/25 border border-transparent"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8M12 7a5 5 0 11-5 5 5 5 0 015-5z" />
          </svg>
          Dạy học
        </button>

        <button @click="toggleChat" class="relative flex flex-col items-center gap-1.5 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-bold shadow-sm"
          :class="showChat ? 'bg-[#7c3aed] text-white shadow-[#7c3aed]/20' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          Chat
          <span v-if="unreadCount > 0 && !showChat"
            class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-[9px] font-extrabold flex items-center justify-center text-white border-2 border-white">
            {{ unreadCount > 9 ? '9+' : unreadCount }}
          </span>
        </button>

        <button @click="toggleParticipants" class="flex flex-col items-center gap-1.5 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-bold shadow-sm"
          :class="showParticipants ? 'bg-[#7c3aed] text-white shadow-[#7c3aed]/20' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Thành viên
        </button>
      </div>

      <!-- Right: End call -->
      <button @click="leaveRoom"
        class="flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm transition-all shadow-md active:scale-95">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/>
        </svg>
        Rời phòng
      </button>
    </div>

    <!-- Present Modal -->
    <transition name="fade">
      <div v-if="showPresentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white border border-gray-200 rounded-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden shadow-2xl">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-gray-900 font-extrabold text-base">Chọn tài liệu giảng dạy</h3>
            <button @click="showPresentModal = false" class="text-gray-500 hover:text-gray-900">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Tabs -->
          <div class="flex border-b border-gray-100 bg-gray-50/50">
            <button 
              @click="presentModalTab = 'lesson'"
              class="flex-1 py-3.5 text-xs font-extrabold text-center border-b-2 transition-all uppercase tracking-wider"
              :class="presentModalTab === 'lesson' ? 'border-[#7c3aed] text-[#7c3aed] bg-[#7c3aed]/5' : 'border-transparent text-gray-500 hover:text-gray-800'"
            >
              📚 Bài giảng (Của tôi & Công khai)
            </button>
            <button 
              @click="presentModalTab = 'assignment'"
              class="flex-1 py-3.5 text-xs font-extrabold text-center border-b-2 transition-all uppercase tracking-wider"
              :class="presentModalTab === 'assignment' ? 'border-[#7c3aed] text-[#7c3aed] bg-[#7c3aed]/5' : 'border-transparent text-gray-500 hover:text-gray-800'"
            >
              📝 Bài tập & Đề thi
            </button>
          </div>

          <!-- Search list content -->
          <div class="flex-1 overflow-y-auto p-6 space-y-3.5 min-h-0 scrollbar-thin">
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <input 
                v-model="presentSearchQuery" 
                type="text" 
                placeholder="Tìm kiếm tài liệu bài giảng, bài tập..." 
                class="w-full pl-9 pr-3 py-2.5 text-xs rounded-xl border border-gray-205 bg-white text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#7c3aed]/20 focus:border-[#7c3aed] transition-all"
              />
            </div>

            <!-- Loading Spinner -->
            <div v-if="presentModalLoading" class="flex flex-col items-center justify-center py-12 text-gray-400">
              <div class="w-8 h-8 border-2 border-gray-200 border-t-[#7c3aed] rounded-full animate-spin mb-3" />
              <span class="text-xs font-medium">Đang tải danh sách...</span>
            </div>

            <!-- Empty list -->
            <div v-else-if="filteredPresentItems.length === 0" class="text-center text-gray-500 text-xs py-12">
              Không tìm thấy tài liệu học tập phù hợp.
            </div>

            <!-- Present items list -->
            <div v-else class="space-y-2.5">
              <div 
                v-for="item in filteredPresentItems" 
                :key="item.id" 
                class="flex items-center justify-between p-3.5 rounded-xl border border-gray-100 bg-white hover:bg-gray-50 hover:border-gray-200 transition-all group shadow-sm"
              >
                <div>
                  <h4 class="text-gray-800 font-bold text-xs leading-snug">{{ item.title }}</h4>
                  <p class="text-[10px] text-gray-500 mt-1.5 flex items-center gap-2 font-semibold">
                    <span>Môn: {{ item.subject?.name }}</span>
                    <span class="text-gray-300">•</span>
                    <span v-if="item.is_public" class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[8px] font-extrabold uppercase border border-emerald-200">Công khai</span>
                    <span v-else class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 text-[8px] font-extrabold uppercase border border-blue-200">Tự tạo</span>
                  </p>
                </div>
                <button 
                  @click="startPresenting(item)"
                  class="px-3 py-1.5 rounded-lg bg-[#7c3aed] hover:bg-[#6d28d9] text-white text-xs font-bold shadow-md shadow-[#7c3aed]/10 transition-all active:scale-95 flex items-center gap-1"
                >
                  📺 Trình chiếu
                </button>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between shrink-0">
            <button 
              v-if="activePresentationType !== 'none'"
              @click="stopPresenting"
              class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition-all active:scale-95 shadow-sm"
            >
              Dừng trình chiếu hiện tại
            </button>
            <span v-else class="text-xs text-gray-400">Chưa có tài liệu nào đang trình chiếu</span>
            <button @click="showPresentModal = false" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition-all">
              Đóng
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, reactive, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@stores/auth'
import api from '@api/axios'
import { renderMath } from '@/utils/math'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()

const sessionId = route.params.id
const myUserId  = computed(() => auth.user?.id ? Number(auth.user.id) : null)

// ── State ─────────────────────────────────────────────────────────────────
const session      = ref(null)
const participants = ref([])         // [{ userId, name, isMe, isHost, camOn, micOn, status }]
const speakingId   = ref(null)

const micOn   = ref(true)
const camOn   = ref(true)
const sharing = ref(false)
const hidePresentation = ref(false)
const presentationVideo = ref(null)
const speakerVideo = ref(null)

const showChat         = ref(false)
const showParticipants = ref(false)
const messages         = ref([])
const chatInput        = ref('')
const unreadCount      = ref(0)
const chatBox          = ref(null)

const elapsed = ref(0)
const timerDisplay = computed(() => {
  const h = Math.floor(elapsed.value / 3600)
  const m = Math.floor((elapsed.value % 3600) / 60)
  const s = elapsed.value % 60
  return h > 0
    ? `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`
    : `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`
})

// ── Presentation Sharing State ─────────────────────────────────────────────
const showPresentModal    = ref(false)
const presentModalTab     = ref('lesson')
const presentSearchQuery  = ref('')
const presentModalLoading = ref(false)
const lessonsList         = ref([])
const assignmentsList     = ref([])

const activePresentationType = computed(() => session.value?.presentation_type ?? 'none')
const activeLesson           = computed(() => session.value?.active_lesson)
const activeAssignment       = computed(() => session.value?.active_assignment)

const screenSharingParticipant = computed(() => participants.value.find(p => p.sharing))
const hasMainPresentation = computed(() => participants.value.length > 0)

const mainSpeaker = computed(() => {
  if (screenSharingParticipant.value) return null
  if (activePresentationType.value !== 'none') return null
  const speaker = participants.value.find(p => p.userId === speakingId.value)
  if (speaker) return speaker
  const host = participants.value.find(p => p.isHost)
  if (host) return host
  return participants.value[0] || null
})

watch([screenSharingParticipant, mainSpeaker, hidePresentation, presentationVideo, speakerVideo], () => {
  nextTick(() => {
    if (presentationVideo.value && screenSharingParticipant.value) {
      const stream = streams.get(Number(screenSharingParticipant.value.userId))
      if (stream && presentationVideo.value.srcObject !== stream) {
        presentationVideo.value.srcObject = stream
      }
    }
    if (speakerVideo.value && mainSpeaker.value) {
      const stream = streams.get(Number(mainSpeaker.value.userId))
      if (stream && speakerVideo.value.srcObject !== stream) {
        speakerVideo.value.srcObject = stream
      }
    }
  })
}, { immediate: true })

const activePresentationTitle = computed(() => {
  if (activePresentationType.value === 'lesson') return activeLesson.value?.title ?? 'Đang trình bày...'
  if (activePresentationType.value === 'assignment') return activeAssignment.value?.title ?? 'Đang trình bày...'
  return ''
})

// WebRTC state (non-reactive intentionally)
let localStream  = null
let screenStream = null
let iceServers   = []
const peerConnections = new Map()  // userId → RTCPeerConnection
const videoRefs       = new Map()  // userId → HTMLVideoElement
const streams         = new Map()  // userId → MediaStream
let lastMessageId = 0

// Intervals
let timerInterval        = null
let signalPollInterval   = null
let msgPollInterval      = null
let participantPollInterval = null

// Grid / Sidebar Video Layout
const gridPage = ref(0)
const participantsPerPage = 16

const displayParticipants = computed(() => {
  if (hasMainPresentation.value && !hidePresentation.value) {
    if (mainSpeaker.value) {
      return participants.value.filter(p => p.userId !== mainSpeaker.value.userId)
    }
    return participants.value
  }
  const start = gridPage.value * participantsPerPage
  return participants.value.slice(start, start + participantsPerPage)
})

watch(() => participants.value.length, (newLength) => {
  const maxPage = Math.max(0, Math.ceil(newLength / participantsPerPage) - 1)
  if (gridPage.value > maxPage) {
    gridPage.value = maxPage
  }
})

function prevGridPage() {
  if (gridPage.value > 0) {
    gridPage.value--
  }
}

function nextGridPage() {
  if (gridPage.value < Math.ceil(participants.value.length / participantsPerPage) - 1) {
    gridPage.value++
  }
}

const gridClass = computed(() => {
  const n = displayParticipants.value.length
  if (n <= 1) return 'grid-cols-1 grid-rows-1'
  if (n === 2) return 'grid-cols-2 grid-rows-1'
  if (n <= 4) return 'grid-cols-2 grid-rows-2'
  if (n <= 6) return 'grid-cols-3 grid-rows-2'
  if (n <= 9) return 'grid-cols-3 grid-rows-3'
  if (n <= 12) return 'grid-cols-4 grid-rows-3'
  return 'grid-cols-4 grid-rows-4'
})

const isHost = computed(() => {
  if (!session.value || !myUserId.value) return false
  const teacherId = session.value.teacher?.id ? Number(session.value.teacher.id) : null
  const homeroomTeacherId = session.value.classroom?.homeroom_teacher?.id ? Number(session.value.classroom.homeroom_teacher.id) : null
  return teacherId === myUserId.value || homeroomTeacherId === myUserId.value
})

// Helper methods
function getInitials(name = '') {
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
}

const videoRefHandlers = {}
function getVideoRefHandler(userId) {
  const uId = Number(userId)
  if (!videoRefHandlers[uId]) {
    videoRefHandlers[uId] = (el) => {
      if (el) {
        videoRefs.set(uId, el)
        const stream = streams.get(uId)
        if (stream && el.srcObject !== stream) {
          el.srcObject = stream
        }
      } else {
        videoRefs.delete(uId)
      }
    }
  }
  return videoRefHandlers[uId]
}

function applyStream(userId, stream) {
  const uId = Number(userId)
  streams.set(uId, stream)
  const el = videoRefs.get(uId)
  if (el && el.srcObject !== stream) {
    el.srcObject = stream
  }
  const p = participants.value.find(x => Number(x.userId) === uId)
  if (p) p.camOn = true

  // Also bind to presentationVideo if this is the screen sharing participant
  if (screenSharingParticipant.value && Number(screenSharingParticipant.value.userId) === uId) {
    if (presentationVideo.value && presentationVideo.value.srcObject !== stream) {
      presentationVideo.value.srcObject = stream
    }
  }

  // Also bind to speakerVideo if this is the main speaker
  if (mainSpeaker.value && Number(mainSpeaker.value.userId) === uId) {
    if (speakerVideo.value && speakerVideo.value.srcObject !== stream) {
      speakerVideo.value.srcObject = stream
    }
  }
}

// ── Presentation Methods ──────────────────────────────────────────────────
async function loadPresentItems() {
  presentModalLoading.value = true
  try {
    if (presentModalTab.value === 'lesson') {
      const [res1, res2] = await Promise.all([
        api.get('/teacher/lessons').catch(() => ({ data: { data: [] } })),
        api.get('/lessons').catch(() => ({ data: { data: [] } })),
      ])
      const list1 = res1.data.data ?? []
      const list2 = res2.data.data ?? []
      const merged = [...list1]
      list2.forEach(item => {
        if (!merged.find(x => x.id === item.id)) {
          merged.push({ ...item, is_public: true })
        }
      })
      lessonsList.value = merged
    } else {
      const [res1, res2] = await Promise.all([
        api.get('/teacher/assignments').catch(() => ({ data: { data: [] } })),
        api.get('/teacher/exams').catch(() => ({ data: { data: [] } })),
      ])
      const list1 = res1.data.data ?? []
      const list2 = res2.data.data ?? []
      const merged = [...list1]
      list2.forEach(item => {
        if (!merged.find(x => x.id === item.id)) {
          merged.push(item)
        }
      })
      assignmentsList.value = merged
    }
  } catch {} finally {
    presentModalLoading.value = false
  }
}

watch(presentModalTab, loadPresentItems)

const filteredPresentItems = computed(() => {
  const list = presentModalTab.value === 'lesson' ? lessonsList.value : assignmentsList.value
  if (!presentSearchQuery.value) return list
  return list.filter(item => 
    item.title?.toLowerCase().includes(presentSearchQuery.value.toLowerCase()) ||
    item.subject?.name?.toLowerCase().includes(presentSearchQuery.value.toLowerCase())
  )
})

async function startPresenting(item) {
  try {
    const payload = {
      presentation_type: presentModalTab.value,
      active_lesson_id: presentModalTab.value === 'lesson' ? item.id : null,
      active_assignment_id: presentModalTab.value === 'assignment' ? item.id : null,
    }
    const { data } = await api.post(`/live/rooms/${sessionId}/presentation`, payload)
    session.value = data.data
    showPresentModal.value = false
  } catch {}
}

async function stopPresenting() {
  try {
    const payload = {
      presentation_type: 'none',
      active_lesson_id: null,
      active_assignment_id: null,
    }
    const { data } = await api.post(`/live/rooms/${sessionId}/presentation`, payload)
    session.value = data.data
    showPresentModal.value = false
  } catch {}
}

async function refreshSessionInfo() {
  try {
    const { data } = await api.get(`/live/rooms/${sessionId}/info`)
    session.value = data.data.session
  } catch {}
}

// ── WebRTC ────────────────────────────────────────────────────────────────
async function createPeerConnection(userId, initiator) {
  const uId = Number(userId)
  if (peerConnections.has(uId)) return peerConnections.get(uId)

  const pc = new RTCPeerConnection({ iceServers })
  peerConnections.set(uId, pc)

  updateStatus(uId, 'connecting')

  localStream?.getTracks().forEach(track => pc.addTrack(track, localStream))

  const remoteStream = new MediaStream()
  pc.ontrack = event => {
    event.streams[0]?.getTracks().forEach(t => remoteStream.addTrack(t))
    applyStream(uId, remoteStream)
    updateStatus(uId, 'connected')
  }

  pc.onicecandidate = async event => {
    if (!event.candidate) return
    try {
      await api.post('/live/signal', {
        live_session_id: sessionId,
        to_user_id: uId,
        type: 'ice-candidate',
        payload: { candidate: event.candidate.toJSON() },
      })
    } catch {}
  }

  pc.onconnectionstatechange = () => {
    if (pc.connectionState === 'connected') updateStatus(uId, 'connected')
    if (pc.connectionState === 'failed' || pc.connectionState === 'disconnected') {
      peerConnections.delete(uId)
      updateStatus(uId, 'disconnected')
    }
  }

  if (initiator) {
    try {
      const offer = await pc.createOffer()
      await pc.setLocalDescription(offer)
      await api.post('/live/signal', {
        live_session_id: sessionId,
        to_user_id: uId,
        type: 'offer',
        payload: { sdp: { type: offer.type, sdp: offer.sdp } },
      })
    } catch {}
  }

  return pc
}

function updateStatus(userId, status) {
  const uId = Number(userId)
  const p = participants.value.find(x => Number(x.userId) === uId)
  if (p) p.status = status
}

async function handleSignal(signal) {
  const { from_user_id, type, payload } = signal
  const fromUserId = Number(from_user_id)
  try {
    if (type === 'offer') {
      if (!participants.value.find(p => Number(p.userId) === fromUserId)) {
        participants.value.push({ userId: fromUserId, name: `User ${fromUserId}`, isMe: false, isHost: false, camOn: true, micOn: true, status: 'connecting' })
      }
      const pc = await createPeerConnection(fromUserId, false)
      await pc.setRemoteDescription(new RTCSessionDescription(payload.sdp))
      const answer = await pc.createAnswer()
      await pc.setLocalDescription(answer)
      await api.post('/live/signal', {
        live_session_id: sessionId,
        to_user_id: fromUserId,
        type: 'answer',
        payload: { sdp: { type: answer.type, sdp: answer.sdp } },
      })
    } else if (type === 'answer') {
      const pc = peerConnections.get(fromUserId)
      if (pc?.signalingState === 'have-local-offer') {
        await pc.setRemoteDescription(new RTCSessionDescription(payload.sdp))
      }
    } else if (type === 'ice-candidate') {
      const pc = peerConnections.get(fromUserId)
      if (pc?.remoteDescription) {
        try { await pc.addIceCandidate(new RTCIceCandidate(payload.candidate)) } catch {}
      }
    } else if (type === 'presentation-changed') {
      await refreshSessionInfo()
    } else if (type === 'screen-share-state') {
      const idx = participants.value.findIndex(x => Number(x.userId) === fromUserId)
      if (idx !== -1) {
        participants.value[idx] = { ...participants.value[idx], sharing: !!payload.sharing }
      }
    }
  } catch {}
}

async function pollSignals() {
  try {
    const { data } = await api.get(`/live/rooms/${sessionId}/signals`)
    for (const s of data.data ?? []) await handleSignal(s)
  } catch {}
}

async function pollMessages() {
  try {
    const { data } = await api.get(`/live/rooms/${sessionId}/messages`, { params: { last_id: lastMessageId } })
    const newMsgs = data.data ?? []
    if (!newMsgs.length) return
    newMsgs.forEach(m => {
      messages.value.push({ id: m.id, userId: Number(m.user_id), name: m.user?.name ?? `User ${m.user_id}`, text: m.message })
      lastMessageId = Math.max(lastMessageId, m.id)
    })
    if (!showChat.value) unreadCount.value += newMsgs.filter(m => Number(m.user_id) !== myUserId.value).length
    await nextTick()
    if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight
  } catch {}
}

async function refreshParticipants() {
  try {
    const { data } = await api.get(`/live/rooms/${sessionId}/info`)
    session.value = data.data.session // update session presentation values dynamically
    const active = data.data.participants ?? []
    const activeIds = new Set(active.map(p => Number(p.user_id)))
    activeIds.add(myUserId.value)

    for (const p of active) {
      const pUserId = Number(p.user_id)
      if (pUserId === myUserId.value) continue
      if (!participants.value.find(x => Number(x.userId) === pUserId)) {
        participants.value.push({ userId: pUserId, name: p.user?.name ?? `User ${pUserId}`, isMe: false, isHost: p.role === 'host', camOn: true, micOn: true, status: 'connecting', sharing: false })
        await createPeerConnection(pUserId, true)
        
        // Notify new participant of screen share if we are currently sharing
        if (sharing.value) {
          api.post('/live/signal', {
            live_session_id: sessionId,
            to_user_id: pUserId,
            type: 'screen-share-state',
            payload: { sharing: true },
          }).catch(() => {})
        }
      }
    }
    // Remove left participants
    participants.value = participants.value.filter(p => activeIds.has(Number(p.userId)))
  } catch {}
}

// ── Controls ──────────────────────────────────────────────────────────────
function toggleMic() {
  micOn.value = !micOn.value
  localStream?.getAudioTracks().forEach(t => { t.enabled = micOn.value })
  const me = participants.value.find(p => p.isMe)
  if (me) me.micOn = micOn.value
}

function toggleCam() {
  camOn.value = !camOn.value
  const me = participants.value.find(p => p.isMe)
  if (me) me.camOn = camOn.value
  
  // Set enabled state on track directly instead of stopping hardware to avoid webcam blinking
  localStream?.getVideoTracks().forEach(t => { t.enabled = camOn.value })
}

async function toggleScreenShare() {
  if (sharing.value) {
    screenStream?.getTracks().forEach(t => t.stop())
    screenStream = null
    sharing.value = false
    const idx = participants.value.findIndex(p => p.isMe)
    if (idx !== -1) {
      participants.value[idx] = { ...participants.value[idx], sharing: false }
    }
    const camTrack = localStream?.getVideoTracks()[0] ?? null
    peerConnections.forEach((pc, uId) => {
      const sender = pc.getSenders().find(s => s.track?.kind === 'video')
      if (sender) sender.replaceTrack(camTrack)
      
      api.post('/live/signal', {
        live_session_id: sessionId,
        to_user_id: uId,
        type: 'screen-share-state',
        payload: { sharing: false },
      }).catch(() => {})
    })
    applyStream(myUserId.value, localStream)
  } else {
    try {
      screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: false })
      sharing.value = true
      const idx = participants.value.findIndex(p => p.isMe)
      if (idx !== -1) {
        participants.value[idx] = { ...participants.value[idx], sharing: true }
      }
      const screenTrack = screenStream.getVideoTracks()[0]
      screenTrack.onended = () => {
        sharing.value = false
        screenStream = null
        const idx = participants.value.findIndex(p => p.isMe)
        if (idx !== -1) {
          participants.value[idx] = { ...participants.value[idx], sharing: false }
        }
        const camTrack = localStream?.getVideoTracks()[0] ?? null
        peerConnections.forEach((pc, uId) => {
          const sender = pc.getSenders().find(s => s.track?.kind === 'video')
          if (sender) sender.replaceTrack(camTrack)
          
          api.post('/live/signal', {
            live_session_id: sessionId,
            to_user_id: uId,
            type: 'screen-share-state',
            payload: { sharing: false },
          }).catch(() => {})
        })
        applyStream(myUserId.value, localStream)
      }
      peerConnections.forEach((pc, uId) => {
        const sender = pc.getSenders().find(s => s.track?.kind === 'video')
        if (sender) sender.replaceTrack(screenTrack)
        
        api.post('/live/signal', {
          live_session_id: sessionId,
          to_user_id: uId,
          type: 'screen-share-state',
          payload: { sharing: true },
        }).catch(() => {})
      })
      const combined = new MediaStream([screenTrack, ...(localStream?.getAudioTracks() ?? [])])
      applyStream(myUserId.value, combined)
    } catch {}
  }
}

function toggleFitMode(p) {
  const current = p.fitMode || (p.sharing ? 'contain' : 'cover')
  p.fitMode = current === 'contain' ? 'cover' : 'contain'
}

function toggleChat() {
  showChat.value = !showChat.value
  if (showChat.value) { showParticipants.value = false; unreadCount.value = 0 }
}

function toggleParticipants() {
  showParticipants.value = !showParticipants.value
  if (showParticipants.value) showChat.value = false
}

async function sendChat() {
  const text = chatInput.value.trim()
  if (!text) return
  chatInput.value = ''
  const temp = { id: Date.now(), userId: myUserId.value, name: auth.user?.name || 'Bạn', text }
  messages.value.push(temp)
  await nextTick()
  if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight
  try { await api.post(`/live/rooms/${sessionId}/message`, { message: text }) } catch {}
}

async function leaveRoom() {
  stopAll()
  try { await api.post(`/live/rooms/${sessionId}/leave`) } catch {}
  try { await api.post(`/student/live-sessions/${sessionId}/leave`) } catch {}
  router.push(`/live/${sessionId}/lobby`)
}

function stopAll() {
  clearInterval(timerInterval)
  clearInterval(signalPollInterval)
  clearInterval(msgPollInterval)
  clearInterval(participantPollInterval)
  peerConnections.forEach(pc => pc.close())
  peerConnections.clear()
  localStream?.getTracks().forEach(t => t.stop())
  screenStream?.getTracks().forEach(t => t.stop())
}

// ── Mount ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  // Load session info
  try {
    const { data } = await api.get(`/live/rooms/${sessionId}/info`)
    session.value = data.data.session
  } catch {}

  // Restore lobby settings
  const lobby = JSON.parse(sessionStorage.getItem(`live_lobby_${sessionId}`) || '{}')
  micOn.value = lobby.micOn ?? true
  camOn.value = lobby.camOn ?? true

  // Get local stream with high quality constraints to keep cams sharp
  try {
    localStream = await navigator.mediaDevices.getUserMedia({
      video: lobby.selectedCamera 
        ? { 
            deviceId: { exact: lobby.selectedCamera },
            width: { ideal: 1280 },
            height: { ideal: 720 },
            frameRate: { ideal: 30, max: 60 }
          } 
        : {
            width: { ideal: 1280 },
            height: { ideal: 720 },
            frameRate: { ideal: 30, max: 60 }
          },
      audio: lobby.selectedMic  ? { deviceId: { exact: lobby.selectedMic  } } : true,
    })
    if (!camOn.value) localStream.getVideoTracks().forEach(t => { t.enabled = false })
    if (!micOn.value) localStream.getAudioTracks().forEach(t => { t.enabled = false })
  } catch { localStream = null }

  // Add self tile
  const self = {
    userId: myUserId.value, name: auth.user?.name || 'Bạn',
    isMe: true, isHost: isHost.value,
    camOn: camOn.value, micOn: micOn.value, status: 'connected',
    sharing: false,
  }
  participants.value.push(self)
  if (localStream) {
    streams.set(myUserId.value, localStream)
    await nextTick()
    const el = videoRefs.get(myUserId.value)
    if (el) el.srcObject = localStream
  }

  // Join room — get ICE servers + existing participants
  try {
    const { data } = await api.post(`/live/rooms/${sessionId}/join`)
    iceServers = data.data.ice_servers ?? []

    for (const p of data.data.participants ?? []) {
      const pUserId = Number(p.user_id)
      if (pUserId === myUserId.value) continue
      participants.value.push({
        userId: pUserId, name: p.user?.name ?? `User ${pUserId}`,
        isMe: false, isHost: p.role === 'host',
        camOn: true, micOn: true, status: 'connecting',
        sharing: false,
      })
      await createPeerConnection(pUserId, true)
    }
  } catch {}

  // Also mark as joined in student API (ignores error if not a student)
  try { await api.post(`/student/live-sessions/${sessionId}/join`) } catch {}

  // Start polling
  timerInterval          = setInterval(() => elapsed.value++, 1000)
  signalPollInterval     = setInterval(pollSignals, 800)
  msgPollInterval        = setInterval(pollMessages, 2000)
  participantPollInterval = setInterval(refreshParticipants, 5000)
})

onUnmounted(stopAll)
</script>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: width 0.25s ease, opacity 0.25s ease; overflow: hidden; }
.slide-enter-from, .slide-leave-to { width: 0; opacity: 0; }
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.1); border-radius: 2px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(0, 0, 0, 0.2); }
</style>
