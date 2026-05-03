<template>
  <div class="min-h-screen bg-[#1a1b2e] flex flex-col">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-[#d63015] flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
        </div>
        <span class="text-white font-bold text-lg">TunAcademy Live</span>
      </div>
      <button @click="exitLobby" class="text-gray-400 hover:text-white text-sm flex items-center gap-1.5 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Thoát
      </button>
    </div>

    <div class="flex-1 flex items-center justify-center p-6">
      <div v-if="loading" class="text-gray-400 flex items-center gap-3">
        <div class="w-5 h-5 rounded-full border-2 border-gray-600 border-t-white animate-spin"/>
        Đang tải...
      </div>

      <div v-else class="w-full max-w-4xl">
        <div class="grid lg:grid-cols-[1fr_370px] gap-8 items-start">

          <!-- ── Camera column ── -->
          <div>
            <div class="relative aspect-video bg-[#0d0e1c] rounded-2xl overflow-hidden shadow-2xl">

              <!-- Video stream -->
              <video ref="localVideo" autoplay muted playsinline
                class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300"
                :class="camPerm === 'granted' && camOn ? 'opacity-100' : 'opacity-0'"/>

              <!-- Avatar (when cam off or not granted) -->
              <div v-if="camPerm !== 'granted' || !camOn"
                class="absolute inset-0 flex items-center justify-center">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-[#d63015] to-orange-400 flex items-center justify-center text-3xl font-bold text-white select-none">
                  {{ initials }}
                </div>
              </div>

              <!-- Mic off badge -->
              <div v-if="micPerm === 'granted' && !micOn"
                class="absolute top-3 left-3 flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-500/90 text-xs text-white font-medium">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1V9a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                </svg>
                Đang tắt tiếng
              </div>

              <!-- hint text when not yet enabled -->
              <div v-if="camPerm === 'prompt' || micPerm === 'prompt'"
                class="absolute top-3 right-3 text-xs text-gray-500 bg-black/40 px-2 py-1 rounded-lg">
                Bấm nút bên dưới để bật
              </div>

              <!-- ── Bottom control bar ── -->
              <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-end gap-3">

                <!-- MIC button -->
                <div class="flex flex-col items-center gap-1.5">
                  <button @click="micPerm === 'granted' ? toggleMic() : requestMicAccess()"
                    :disabled="requestingMic || micPerm === 'checking'"
                    class="w-12 h-12 rounded-full flex items-center justify-center transition-all backdrop-blur-sm disabled:opacity-50"
                    :class="micBtnClass"
                    :title="micBtnTitle">
                    <!-- spinner -->
                    <div v-if="requestingMic" class="w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin"/>
                    <!-- denied -->
                    <svg v-else-if="micPerm === 'denied'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    <!-- prompt / not-found / error: enable icon -->
                    <svg v-else-if="micPerm !== 'granted'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                    <!-- granted + on -->
                    <svg v-else-if="micOn" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                    <!-- granted + off -->
                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1V9a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                    </svg>
                  </button>
                  <span class="text-xs font-medium" :class="micPerm === 'granted' ? 'text-gray-300' : 'text-amber-400'">
                    {{ micBtnLabel }}
                  </span>
                </div>

                <!-- CAM button -->
                <div class="flex flex-col items-center gap-1.5">
                  <button @click="camPerm === 'granted' ? toggleCam() : requestCamAccess()"
                    :disabled="requestingCam || camPerm === 'checking'"
                    class="w-12 h-12 rounded-full flex items-center justify-center transition-all backdrop-blur-sm disabled:opacity-50"
                    :class="camBtnClass"
                    :title="camBtnTitle">
                    <!-- spinner -->
                    <div v-if="requestingCam" class="w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin"/>
                    <!-- denied -->
                    <svg v-else-if="camPerm === 'denied'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    <!-- prompt / not-found / error: enable icon -->
                    <svg v-else-if="camPerm !== 'granted'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <!-- granted + on -->
                    <svg v-else-if="camOn" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <!-- granted + off -->
                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2zM3 3l18 18"/>
                    </svg>
                  </button>
                  <span class="text-xs font-medium" :class="camPerm === 'granted' ? 'text-gray-300' : 'text-amber-400'">
                    {{ camBtnLabel }}
                  </span>
                </div>
              </div>
            </div>

            <!-- ── Device selectors ── -->
            <div class="mt-4 space-y-2.5">
              <!-- Camera selector -->
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <template v-if="camPerm === 'granted'">
                  <select v-model="selectedCamera" @change="switchCamera"
                    class="flex-1 px-3 py-2 rounded-xl text-white text-sm border border-white/10 focus:outline-none focus:ring-1 focus:ring-white/30 bg-[#232440]">
                    <option v-for="d in cameras" :key="d.deviceId" :value="d.deviceId" class="bg-gray-800">{{ d.label || 'Camera' }}</option>
                  </select>
                </template>
                <template v-else-if="camPerm === 'denied'">
                  <p class="text-xs text-red-400 flex-1">Camera bị chặn — bấm 🔒 trên thanh địa chỉ → Cho phép Camera</p>
                </template>
                <template v-else-if="camPerm === 'not-found'">
                  <p class="text-xs text-amber-400 flex-1">Không tìm thấy camera</p>
                </template>
                <template v-else-if="camPerm === 'in-use'">
                  <p class="text-xs text-orange-400 flex-1">Camera đang bận — đóng ứng dụng khác rồi thử lại</p>
                </template>
                <template v-else>
                  <p class="text-xs text-gray-500 flex-1">Camera chưa được bật</p>
                </template>
              </div>

              <!-- Mic selector -->
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                </svg>
                <template v-if="micPerm === 'granted'">
                  <select v-model="selectedMic"
                    class="flex-1 px-3 py-2 rounded-xl text-white text-sm border border-white/10 focus:outline-none focus:ring-1 focus:ring-white/30 bg-[#232440]">
                    <option v-for="d in microphones" :key="d.deviceId" :value="d.deviceId" class="bg-gray-800">{{ d.label || 'Microphone' }}</option>
                  </select>
                </template>
                <template v-else-if="micPerm === 'denied'">
                  <p class="text-xs text-red-400 flex-1">Mic bị chặn — bấm 🔒 trên thanh địa chỉ → Cho phép Microphone</p>
                </template>
                <template v-else-if="micPerm === 'not-found'">
                  <p class="text-xs text-amber-400 flex-1">Không tìm thấy microphone</p>
                </template>
                <template v-else-if="micPerm === 'in-use'">
                  <p class="text-xs text-orange-400 flex-1">Mic đang bận — đóng ứng dụng khác rồi thử lại</p>
                </template>
                <template v-else>
                  <p class="text-xs text-gray-500 flex-1">Microphone chưa được bật</p>
                </template>
              </div>
            </div>
          </div>

          <!-- ── Right panel ── -->
          <div class="space-y-5">
            <!-- Room info -->
            <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
              <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#d63015]/20 flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5 text-[#d63015]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div class="min-w-0">
                  <p class="font-bold text-white truncate">{{ session?.title }}</p>
                  <p class="text-sm text-gray-400 mt-0.5">{{ session?.classroom?.name }}</p>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2.5 text-xs">
                <div class="bg-white/5 rounded-xl p-2.5">
                  <p class="text-gray-500 mb-0.5">{{ session?.classroom ? 'GVCN' : 'Giáo viên' }}</p>
                  <p class="text-gray-200 font-medium truncate">
                    {{ session?.classroom?.homeroom_teacher?.name ?? session?.teacher?.name ?? '—' }}
                  </p>
                </div>
                <div class="bg-white/5 rounded-xl p-2.5">
                  <p class="text-gray-500 mb-0.5">Mã phòng</p>
                  <p class="text-gray-200 font-mono font-bold tracking-wider">{{ session?.room_code ?? '—' }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-2.5">
                  <p class="text-gray-500 mb-0.5">Trong phòng</p>
                  <p class="text-gray-200 font-medium">{{ participantCount }} người</p>
                </div>
                <div class="bg-white/5 rounded-xl p-2.5">
                  <p class="text-gray-500 mb-0.5">Trạng thái</p>
                  <p class="font-semibold" :class="session?.status === 'live' ? 'text-green-400' : 'text-amber-400'">
                    {{ session?.status === 'live' ? '● Đang mở' : '○ Chưa mở' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- User badge + device status -->
            <div class="bg-white/5 rounded-xl p-3.5 border border-white/10">
              <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#d63015] to-orange-400 flex items-center justify-center text-white font-bold shrink-0">
                  {{ initials }}
                </div>
                <div class="min-w-0">
                  <p class="text-white font-medium text-sm truncate">{{ auth.user?.name }}</p>
                  <p class="text-xs text-gray-400">{{ isAdmin ? 'Quản trị viên' : isHost ? 'Giáo viên · Chủ phòng' : 'Học sinh' }}</p>
                </div>
              </div>
              <!-- Device status row -->
              <div class="flex items-center gap-3 pt-3 border-t border-white/10">
                <div class="flex items-center gap-1.5 text-xs"
                  :class="camPerm === 'granted' ? 'text-green-400' : camPerm === 'denied' ? 'text-red-400' : 'text-gray-500'">
                  <div class="w-1.5 h-1.5 rounded-full"
                    :class="camPerm === 'granted' ? 'bg-green-400' : camPerm === 'denied' ? 'bg-red-400' : 'bg-gray-600'"/>
                  Camera
                </div>
                <div class="flex items-center gap-1.5 text-xs"
                  :class="micPerm === 'granted' ? 'text-green-400' : micPerm === 'denied' ? 'text-red-400' : 'text-gray-500'">
                  <div class="w-1.5 h-1.5 rounded-full"
                    :class="micPerm === 'granted' ? 'bg-green-400' : micPerm === 'denied' ? 'bg-red-400' : 'bg-gray-600'"/>
                  Microphone
                </div>
              </div>
            </div>

            <!-- Waiting notice -->
            <div v-if="session?.status !== 'live' && !isHost"
              class="text-xs text-amber-400 bg-amber-400/10 border border-amber-400/20 rounded-xl px-4 py-3 flex items-start gap-2">
              <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
              Phòng học chưa mở. Chờ giáo viên bắt đầu buổi học.
            </div>

            <p v-if="joinError" class="text-xs text-red-400 bg-red-400/10 border border-red-400/20 rounded-xl px-4 py-3">{{ joinError }}</p>

            <!-- Join button -->
            <button @click="joinRoom"
              :disabled="(session?.status !== 'live' && !isHost) || joining"
              class="w-full py-3.5 rounded-xl text-base font-bold transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              :class="(session?.status === 'live' || isHost) ? 'bg-[#d63015] hover:bg-[#c02a10] text-white shadow-lg shadow-[#d63015]/20' : 'bg-gray-700 text-gray-400'">
              <div v-if="joining" class="w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin"/>
              <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ joining ? 'Đang vào...' : (!isAdmin && isHost && session?.status !== 'live') ? 'Mở và vào phòng' : 'Vào phòng' }}
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@stores/auth'
import api from '@api/axios'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()

const sessionId        = route.params.id
const session          = ref(null)
const loading          = ref(true)
const joining          = ref(false)
const participantCount = ref(0)
const joinError        = ref('')

const localVideo = ref(null)
let videoStream  = null  // video-only stream
let audioStream  = null  // audio-only stream

// Separate permission states for camera and mic
// Values: 'checking' | 'prompt' | 'granted' | 'denied' | 'not-found' | 'in-use' | 'error'
const camPerm       = ref('checking')
const micPerm       = ref('checking')
const requestingCam = ref(false)
const requestingMic = ref(false)
const camOn         = ref(true)
const micOn         = ref(true)
const cameras       = ref([])
const microphones   = ref([])
const selectedCamera = ref('')
const selectedMic    = ref('')

let sessionPollInterval = null

// ── Computed ──────────────────────────────────────────────────────────────────
const initials = computed(() =>
  (auth.user?.name || 'U').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
)
const isAdmin = computed(() => auth.role === 'admin')
const isHost  = computed(() =>
  isAdmin.value ||
  session.value?.teacher?.id === auth.user?.id ||
  session.value?.classroom?.homeroom_teacher?.id === auth.user?.id
)

function exitLobby() {
  videoStream?.getTracks().forEach(t => t.stop())
  audioStream?.getTracks().forEach(t => t.stop())
  if (isAdmin.value) router.push('/admin/live')
  else if (isHost.value) router.push('/teacher/live')
  else router.push('/student/live')
}

const camBtnClass = computed(() => {
  if (camPerm.value === 'granted') return camOn.value ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-red-500 hover:bg-red-600 text-white'
  if (camPerm.value === 'denied') return 'bg-red-500/20 border border-red-500/50 text-red-400 cursor-not-allowed'
  return 'bg-white/10 hover:bg-white/20 text-amber-300 border border-amber-400/30'
})
const micBtnClass = computed(() => {
  if (micPerm.value === 'granted') return micOn.value ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-red-500 hover:bg-red-600 text-white'
  if (micPerm.value === 'denied') return 'bg-red-500/20 border border-red-500/50 text-red-400 cursor-not-allowed'
  return 'bg-white/10 hover:bg-white/20 text-amber-300 border border-amber-400/30'
})
const camBtnLabel = computed(() => {
  if (camPerm.value === 'checking') return 'Camera'
  if (camPerm.value === 'prompt')   return 'Bật Camera'
  if (camPerm.value === 'denied')   return 'Bị chặn'
  if (camPerm.value === 'not-found') return 'Không có'
  if (camPerm.value === 'in-use')   return 'Đang bận'
  if (camPerm.value === 'granted')  return camOn.value ? 'Camera' : 'Camera tắt'
  return 'Camera'
})
const micBtnLabel = computed(() => {
  if (micPerm.value === 'checking') return 'Mic'
  if (micPerm.value === 'prompt')   return 'Bật Mic'
  if (micPerm.value === 'denied')   return 'Bị chặn'
  if (micPerm.value === 'not-found') return 'Không có'
  if (micPerm.value === 'in-use')   return 'Đang bận'
  if (micPerm.value === 'granted')  return micOn.value ? 'Micro' : 'Micro tắt'
  return 'Mic'
})
const camBtnTitle = computed(() => {
  if (camPerm.value !== 'granted') return 'Bấm để cấp quyền camera'
  return camOn.value ? 'Tắt camera' : 'Bật camera'
})
const micBtnTitle = computed(() => {
  if (micPerm.value !== 'granted') return 'Bấm để cấp quyền microphone'
  return micOn.value ? 'Tắt tiếng' : 'Bật tiếng'
})

// ── Session ──────────────────────────────────────────────────────────────────
async function loadSession() {
  try {
    const { data } = await api.get(`/live/rooms/${sessionId}/info`)
    session.value          = data.data.session
    participantCount.value = data.data.participants?.length ?? 0
  } catch { session.value = null }
  finally { loading.value = false }
}

// ── Permission helpers ────────────────────────────────────────────────────────
function classifyError(err) {
  const name = err?.name ?? ''
  if (name === 'NotAllowedError' || name === 'PermissionDeniedError') return 'denied'
  if (name === 'NotFoundError'   || name === 'DevicesNotFoundError')  return 'not-found'
  if (name === 'NotReadableError'|| name === 'TrackStartError')       return 'in-use'
  return 'error'
}

/** Ghép video + audio track vào 1 stream để gắn lên <video> preview */
function rebuildPreviewStream() {
  const combined = new MediaStream()
  videoStream?.getVideoTracks().forEach(t => combined.addTrack(t))
  audioStream?.getAudioTracks().forEach(t => combined.addTrack(t))
  if (localVideo.value) localVideo.value.srcObject = combined
}

// ── Camera ────────────────────────────────────────────────────────────────────
async function requestCamAccess() {
  if (requestingCam.value || !navigator.mediaDevices?.getUserMedia) return
  requestingCam.value = true
  try {
    videoStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false })
    rebuildPreviewStream()
    const devices = await navigator.mediaDevices.enumerateDevices()
    cameras.value = devices.filter(d => d.kind === 'videoinput')
    selectedCamera.value = videoStream.getVideoTracks()[0]?.getSettings()?.deviceId ?? ''
    camPerm.value = 'granted'
    camOn.value   = true
  } catch (err) {
    camPerm.value = classifyError(err)
    camOn.value   = false
  } finally {
    requestingCam.value = false
  }
}

// ── Mic ───────────────────────────────────────────────────────────────────────
async function requestMicAccess() {
  if (requestingMic.value || !navigator.mediaDevices?.getUserMedia) return
  requestingMic.value = true
  try {
    audioStream = await navigator.mediaDevices.getUserMedia({ video: false, audio: true })
    rebuildPreviewStream()
    const devices = await navigator.mediaDevices.enumerateDevices()
    microphones.value = devices.filter(d => d.kind === 'audioinput')
    selectedMic.value = audioStream.getAudioTracks()[0]?.getSettings()?.deviceId ?? ''
    micPerm.value = 'granted'
    micOn.value   = true
  } catch (err) {
    micPerm.value = classifyError(err)
    micOn.value   = false
  } finally {
    requestingMic.value = false
  }
}

// ── Check existing permissions on load ───────────────────────────────────────
async function checkPermissions() {
  if (!navigator.mediaDevices?.getUserMedia) {
    camPerm.value = 'prompt'
    micPerm.value = 'prompt'
    return
  }
  try {
    const [cp, mp] = await Promise.all([
      navigator.permissions.query({ name: 'camera' }),
      navigator.permissions.query({ name: 'microphone' }),
    ])
    // Camera
    if      (cp.state === 'granted') { await requestCamAccess() }
    else if (cp.state === 'denied')  { camPerm.value = 'denied' }
    else                             { camPerm.value = 'prompt' }
    // Mic
    if      (mp.state === 'granted') { await requestMicAccess() }
    else if (mp.state === 'denied')  { micPerm.value = 'denied' }
    else                             { micPerm.value = 'prompt' }
    // Listen for user changes via browser settings
    cp.addEventListener('change', () => {
      if (cp.state === 'granted' && camPerm.value !== 'granted') requestCamAccess()
      if (cp.state === 'denied')  camPerm.value = 'denied'
    })
    mp.addEventListener('change', () => {
      if (mp.state === 'granted' && micPerm.value !== 'granted') requestMicAccess()
      if (mp.state === 'denied')  micPerm.value = 'denied'
    })
  } catch {
    // Permissions API not supported → show prompt buttons
    camPerm.value = 'prompt'
    micPerm.value = 'prompt'
  }
}

// ── Toggles ───────────────────────────────────────────────────────────────────
async function toggleCam() {
  if (camOn.value) {
    // Stop tracks hoàn toàn → đèn camera tắt
    videoStream?.getVideoTracks().forEach(t => t.stop())
    videoStream = null
    camOn.value = false
    rebuildPreviewStream()
  } else {
    // Mở lại camera
    requestingCam.value = true
    try {
      const constraints = selectedCamera.value
        ? { video: { deviceId: { exact: selectedCamera.value } }, audio: false }
        : { video: true, audio: false }
      videoStream = await navigator.mediaDevices.getUserMedia(constraints)
      selectedCamera.value = videoStream.getVideoTracks()[0]?.getSettings()?.deviceId ?? ''
      rebuildPreviewStream()
      camOn.value = true
    } catch (err) {
      camPerm.value = classifyError(err)
    } finally {
      requestingCam.value = false
    }
  }
}

async function toggleMic() {
  if (micOn.value) {
    // Stop tracks hoàn toàn → micro tắt hoàn toàn
    audioStream?.getAudioTracks().forEach(t => t.stop())
    audioStream = null
    micOn.value = false
    rebuildPreviewStream()
  } else {
    // Mở lại mic
    requestingMic.value = true
    try {
      const constraints = selectedMic.value
        ? { video: false, audio: { deviceId: { exact: selectedMic.value } } }
        : { video: false, audio: true }
      audioStream = await navigator.mediaDevices.getUserMedia(constraints)
      selectedMic.value = audioStream.getAudioTracks()[0]?.getSettings()?.deviceId ?? ''
      rebuildPreviewStream()
      micOn.value = true
    } catch (err) {
      micPerm.value = classifyError(err)
    } finally {
      requestingMic.value = false
    }
  }
}

async function switchCamera() {
  if (!videoStream || !selectedCamera.value) return
  try {
    const newStream = await navigator.mediaDevices.getUserMedia({
      video: { deviceId: { exact: selectedCamera.value } },
      audio: false,
    })
    videoStream.getTracks().forEach(t => t.stop())
    videoStream = newStream
    rebuildPreviewStream()
    if (!camOn.value) videoStream.getVideoTracks().forEach(t => { t.enabled = false })
  } catch {}
}

// ── Join ──────────────────────────────────────────────────────────────────────
async function joinRoom() {
  joining.value   = true
  joinError.value = ''
  try {
    if (isHost.value && !isAdmin.value && session.value?.status !== 'live') {
      const cid = session.value?.classroom?.id
      if (cid) await api.post(`/teacher/classrooms/${cid}/room/start`)
    }
    sessionStorage.setItem(`live_lobby_${sessionId}`, JSON.stringify({
      camOn: camOn.value, micOn: micOn.value,
      selectedCamera: selectedCamera.value, selectedMic: selectedMic.value,
    }))
    videoStream?.getTracks().forEach(t => t.stop())
    audioStream?.getTracks().forEach(t => t.stop())
    router.push(`/live/${sessionId}/room`)
  } catch (e) {
    joinError.value = e.response?.data?.message ?? 'Không thể vào phòng'
    joining.value = false
  }
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  // Camera/mic yêu cầu HTTPS. Nếu đang dùng HTTP (không phải localhost) → redirect sang HTTPS
  const host = window.location.hostname
  const isLocalhost = host === 'localhost' || host === '127.0.0.1'
  if (window.location.protocol === 'http:' && !isLocalhost) {
    window.location.replace(window.location.href.replace('http://', 'https://'))
    return
  }

  await Promise.all([loadSession(), checkPermissions()])
  sessionPollInterval = setInterval(loadSession, 5000)
})

onUnmounted(() => {
  clearInterval(sessionPollInterval)
  videoStream?.getTracks().forEach(t => t.stop())
  audioStream?.getTracks().forEach(t => t.stop())
})
</script>
