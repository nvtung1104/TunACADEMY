<template>
  <div class="h-screen bg-[#1a1b2e] flex flex-col overflow-hidden select-none">

    <!-- Top bar -->
    <div class="flex items-center justify-between px-5 py-3 border-b border-white/10 shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 rounded-lg bg-[#d63015] flex items-center justify-center">
          <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <p class="text-white font-semibold text-sm leading-none">{{ session?.title }}</p>
          <p class="text-xs text-gray-500 mt-0.5">{{ session?.classroom?.name }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-1.5 text-xs text-gray-300 bg-white/5 px-3 py-1.5 rounded-full">
          <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"/>
          {{ timerDisplay }}
        </div>
        <div class="text-xs text-gray-400 bg-white/5 px-3 py-1.5 rounded-full font-mono">{{ session?.room_code }}</div>
        <div class="text-xs text-gray-400 flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          {{ participants.length }}
        </div>
      </div>
    </div>

    <!-- Main area -->
    <div class="flex-1 flex overflow-hidden min-h-0">

      <!-- Video grid -->
      <div class="flex-1 overflow-hidden p-3">
        <div class="h-full grid gap-2.5" :class="gridClass">
          <div v-for="p in displayParticipants" :key="p.userId"
            class="relative rounded-2xl overflow-hidden bg-[#0f1022] transition-all"
            :class="p.userId === speakingId ? 'ring-2 ring-green-400' : 'ring-1 ring-white/5'">

            <video
              :ref="el => setVideoRef(el, p.userId)"
              autoplay playsinline :muted="p.isMe"
              class="w-full h-full object-cover"
              :class="p.camOn ? 'opacity-100' : 'opacity-0'"/>

            <!-- Avatar when cam off -->
            <div v-if="!p.camOn" class="absolute inset-0 flex items-center justify-center">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#d63015] to-orange-400 flex items-center justify-center text-2xl font-bold text-white">
                {{ getInitials(p.name) }}
              </div>
            </div>

            <!-- Name bar -->
            <div class="absolute bottom-0 left-0 right-0 flex items-center justify-between px-3 py-2 bg-gradient-to-t from-black/70 to-transparent">
              <span class="text-xs text-white font-medium truncate max-w-[80%]">
                {{ p.name }}{{ p.isMe ? ' (Bạn)' : '' }}{{ p.isHost ? ' 👑' : '' }}
              </span>
              <div v-if="!p.micOn" class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center shrink-0 ml-1">
                <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5.586 15H4a1 1 0 01-1-1V9a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                </svg>
              </div>
            </div>

            <!-- Connecting overlay -->
            <div v-if="!p.isMe && p.status === 'connecting'" class="absolute inset-0 flex flex-col items-center justify-center bg-black/60">
              <div class="w-6 h-6 border-2 border-white/20 border-t-white rounded-full animate-spin mb-2"/>
              <p class="text-xs text-gray-300">Đang kết nối...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Chat sidebar -->
      <transition name="slide">
        <div v-if="showChat" class="w-80 flex flex-col border-l border-white/10 bg-[#111229] shrink-0">
          <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
            <p class="text-white font-semibold text-sm">Trò chuyện</p>
            <button @click="showChat = false; unreadCount = 0" class="text-gray-400 hover:text-white">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-4 min-h-0">
            <div v-if="!messages.length" class="text-center text-gray-500 text-xs pt-8">Chưa có tin nhắn nào</div>
            <div v-for="msg in messages" :key="msg.id" class="flex gap-2"
              :class="msg.userId === myUserId ? 'flex-row-reverse' : ''">
              <div class="w-7 h-7 rounded-full bg-gray-700 flex items-center justify-center text-[10px] font-bold text-white shrink-0">
                {{ getInitials(msg.name) }}
              </div>
              <div class="max-w-[200px]">
                <p class="text-[10px] text-gray-500 mb-1" :class="msg.userId === myUserId ? 'text-right' : ''">{{ msg.name }}</p>
                <div class="px-3 py-2 rounded-2xl text-xs text-white leading-relaxed"
                  :class="msg.userId === myUserId ? 'bg-[#d63015] rounded-tr-none' : 'bg-white/10 rounded-tl-none'">
                  {{ msg.text }}
                </div>
              </div>
            </div>
          </div>
          <div class="p-3 border-t border-white/10 shrink-0">
            <div class="flex gap-2">
              <input v-model="chatInput" @keydown.enter="sendChat" type="text" placeholder="Nhắn tin..."
                class="flex-1 px-3 py-2 rounded-xl bg-white/8 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-white/20 bg-[#1a1b2e]"/>
              <button @click="sendChat" :disabled="!chatInput.trim()"
                class="w-9 h-9 rounded-xl bg-[#d63015] hover:bg-[#c02a10] text-white flex items-center justify-center disabled:opacity-40 shrink-0">
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
        <div v-if="showParticipants" class="w-60 flex flex-col border-l border-white/10 bg-[#111229] shrink-0">
          <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
            <p class="text-white font-semibold text-sm">Thành viên ({{ participants.length }})</p>
            <button @click="showParticipants = false" class="text-gray-400 hover:text-white">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="flex-1 overflow-y-auto p-3 space-y-1">
            <div v-for="p in participants" :key="p.userId"
              class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-white/5 transition-colors">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#d63015] to-orange-400 flex items-center justify-center text-xs font-bold text-white shrink-0">
                {{ getInitials(p.name) }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-white text-xs font-medium truncate">{{ p.name }}</p>
                <p class="text-gray-500 text-[10px]">{{ p.isHost ? '👑 Giáo viên' : 'Học sinh' }}</p>
              </div>
              <div class="flex items-center gap-1 shrink-0">
                <div class="w-4.5 h-4.5 flex items-center justify-center rounded-full" :class="!p.micOn ? 'bg-red-500' : 'text-gray-500'">
                  <svg class="w-3 h-3" :class="p.micOn ? 'text-gray-500' : 'text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                  </svg>
                </div>
                <div class="w-4.5 h-4.5 flex items-center justify-center rounded-full" :class="!p.camOn ? 'bg-red-500' : 'text-gray-500'">
                  <svg class="w-3 h-3" :class="p.camOn ? 'text-gray-500' : 'text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    <div class="shrink-0 flex items-center justify-between px-5 py-3.5 border-t border-white/10 bg-[#111229]">
      <!-- Left controls -->
      <div class="flex items-center gap-2">
        <button @click="toggleMic" class="flex flex-col items-center gap-1 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-medium"
          :class="micOn ? 'bg-white/10 hover:bg-white/15 text-white' : 'bg-red-500 hover:bg-red-600 text-white'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path v-if="micOn" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1V9a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
          </svg>
          {{ micOn ? 'Tắt mic' : 'Bật mic' }}
        </button>

        <button @click="toggleCam" class="flex flex-col items-center gap-1 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-medium"
          :class="camOn ? 'bg-white/10 hover:bg-white/15 text-white' : 'bg-red-500 hover:bg-red-600 text-white'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
          {{ camOn ? 'Tắt cam' : 'Bật cam' }}
        </button>

        <button @click="toggleScreenShare" class="flex flex-col items-center gap-1 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-medium"
          :class="sharing ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-white/10 hover:bg-white/15 text-white'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          {{ sharing ? 'Dừng chia sẻ' : 'Chia sẻ' }}
        </button>
      </div>

      <!-- Center controls -->
      <div class="flex items-center gap-2">
        <button @click="toggleChat" class="relative flex flex-col items-center gap-1 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-medium"
          :class="showChat ? 'bg-indigo-500 text-white' : 'bg-white/10 hover:bg-white/15 text-white'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          Chat
          <span v-if="unreadCount > 0 && !showChat"
            class="absolute -top-1 -right-1 w-4.5 h-4.5 rounded-full bg-red-500 text-[9px] font-bold flex items-center justify-center text-white">
            {{ unreadCount > 9 ? '9+' : unreadCount }}
          </span>
        </button>

        <button @click="toggleParticipants" class="flex flex-col items-center gap-1 px-4 py-2.5 rounded-2xl transition-all text-[11px] font-medium"
          :class="showParticipants ? 'bg-indigo-500 text-white' : 'bg-white/10 hover:bg-white/15 text-white'">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Thành viên
        </button>
      </div>

      <!-- Right: End call -->
      <button @click="leaveRoom"
        class="flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-semibold text-sm transition-all">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/>
        </svg>
        Rời phòng
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@stores/auth'
import api from '@api/axios'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()

const sessionId = route.params.id
const myUserId  = auth.user?.id

// ── State ─────────────────────────────────────────────────────────────────
const session      = ref(null)
const participants = ref([])         // [{ userId, name, isMe, isHost, camOn, micOn, status }]
const speakingId   = ref(null)

const micOn   = ref(true)
const camOn   = ref(true)
const sharing = ref(false)

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

// ── WebRTC state (non-reactive intentionally) ─────────────────────────────
let localStream  = null
let screenStream = null
let iceServers   = []
const peerConnections = new Map()  // userId → RTCPeerConnection
const videoRefs       = new Map()  // userId → HTMLVideoElement
const streams         = new Map()  // userId → MediaStream
let lastMessageId = 0

// ── Intervals ─────────────────────────────────────────────────────────────
let timerInterval        = null
let signalPollInterval   = null
let msgPollInterval      = null
let participantPollInterval = null

// ── Grid layout ───────────────────────────────────────────────────────────
const displayParticipants = computed(() => participants.value.slice(0, 9))

const gridClass = computed(() => {
  const n = displayParticipants.value.length
  if (n <= 1) return 'grid-cols-1'
  if (n === 2) return 'grid-cols-2'
  if (n <= 4) return 'grid-cols-2 grid-rows-2'
  if (n <= 6) return 'grid-cols-3 grid-rows-2'
  return 'grid-cols-3 grid-rows-3'
})

const isHost = computed(() => session.value?.teacher?.id === myUserId)

// ── Helpers ───────────────────────────────────────────────────────────────
function getInitials(name = '') {
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
}

function setVideoRef(el, userId) {
  if (el) {
    videoRefs.set(userId, el)
    const stream = streams.get(userId)
    if (stream) el.srcObject = stream
  } else {
    videoRefs.delete(userId)
  }
}

function applyStream(userId, stream) {
  streams.set(userId, stream)
  const el = videoRefs.get(userId)
  if (el) el.srcObject = stream
  const p = participants.value.find(x => x.userId === userId)
  if (p) p.camOn = true
}

// ── WebRTC ────────────────────────────────────────────────────────────────
async function createPeerConnection(userId, initiator) {
  if (peerConnections.has(userId)) return peerConnections.get(userId)

  const pc = new RTCPeerConnection({ iceServers })
  peerConnections.set(userId, pc)

  updateStatus(userId, 'connecting')

  localStream?.getTracks().forEach(track => pc.addTrack(track, localStream))

  const remoteStream = new MediaStream()
  pc.ontrack = event => {
    event.streams[0]?.getTracks().forEach(t => remoteStream.addTrack(t))
    applyStream(userId, remoteStream)
    updateStatus(userId, 'connected')
  }

  pc.onicecandidate = async event => {
    if (!event.candidate) return
    try {
      await api.post('/live/signal', {
        live_session_id: sessionId,
        to_user_id: userId,
        type: 'ice-candidate',
        payload: { candidate: event.candidate.toJSON() },
      })
    } catch {}
  }

  pc.onconnectionstatechange = () => {
    if (pc.connectionState === 'connected') updateStatus(userId, 'connected')
    if (pc.connectionState === 'failed' || pc.connectionState === 'disconnected') {
      peerConnections.delete(userId)
      updateStatus(userId, 'disconnected')
    }
  }

  if (initiator) {
    try {
      const offer = await pc.createOffer()
      await pc.setLocalDescription(offer)
      await api.post('/live/signal', {
        live_session_id: sessionId,
        to_user_id: userId,
        type: 'offer',
        payload: { sdp: { type: offer.type, sdp: offer.sdp } },
      })
    } catch {}
  }

  return pc
}

function updateStatus(userId, status) {
  const p = participants.value.find(x => x.userId === userId)
  if (p) p.status = status
}

async function handleSignal(signal) {
  const { from_user_id, type, payload } = signal
  try {
    if (type === 'offer') {
      if (!participants.value.find(p => p.userId === from_user_id)) {
        participants.value.push({ userId: from_user_id, name: `User ${from_user_id}`, isMe: false, isHost: false, camOn: true, micOn: true, status: 'connecting' })
      }
      const pc = await createPeerConnection(from_user_id, false)
      await pc.setRemoteDescription(new RTCSessionDescription(payload.sdp))
      const answer = await pc.createAnswer()
      await pc.setLocalDescription(answer)
      await api.post('/live/signal', {
        live_session_id: sessionId,
        to_user_id: from_user_id,
        type: 'answer',
        payload: { sdp: { type: answer.type, sdp: answer.sdp } },
      })
    } else if (type === 'answer') {
      const pc = peerConnections.get(from_user_id)
      if (pc?.signalingState === 'have-local-offer') {
        await pc.setRemoteDescription(new RTCSessionDescription(payload.sdp))
      }
    } else if (type === 'ice-candidate') {
      const pc = peerConnections.get(from_user_id)
      if (pc?.remoteDescription) {
        try { await pc.addIceCandidate(new RTCIceCandidate(payload.candidate)) } catch {}
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
      messages.value.push({ id: m.id, userId: m.user_id, name: m.user?.name ?? `User ${m.user_id}`, text: m.message })
      lastMessageId = Math.max(lastMessageId, m.id)
    })
    if (!showChat.value) unreadCount.value += newMsgs.filter(m => m.user_id !== myUserId).length
    await nextTick()
    if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight
  } catch {}
}

async function refreshParticipants() {
  try {
    const { data } = await api.get(`/live/rooms/${sessionId}/info`)
    const active = data.data.participants ?? []
    const activeIds = new Set(active.map(p => p.user_id))
    activeIds.add(myUserId)

    for (const p of active) {
      if (p.user_id === myUserId) continue
      if (!participants.value.find(x => x.userId === p.user_id)) {
        participants.value.push({ userId: p.user_id, name: p.user?.name ?? `User ${p.user_id}`, isMe: false, isHost: p.role === 'host', camOn: true, micOn: true, status: 'connecting' })
        await createPeerConnection(p.user_id, true)
      }
    }
    // Remove left participants
    participants.value = participants.value.filter(p => activeIds.has(p.userId))
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
  localStream?.getVideoTracks().forEach(t => { t.enabled = camOn.value })
  const me = participants.value.find(p => p.isMe)
  if (me) me.camOn = camOn.value
}

async function toggleScreenShare() {
  if (sharing.value) {
    screenStream?.getTracks().forEach(t => t.stop())
    screenStream = null
    sharing.value = false
    const camTrack = localStream?.getVideoTracks()[0] ?? null
    peerConnections.forEach(pc => {
      const sender = pc.getSenders().find(s => s.track?.kind === 'video')
      if (sender) sender.replaceTrack(camTrack)
    })
    applyStream(myUserId, localStream)
  } else {
    try {
      screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: false })
      sharing.value = true
      const screenTrack = screenStream.getVideoTracks()[0]
      screenTrack.onended = () => { sharing.value = false; screenStream = null }
      peerConnections.forEach(pc => {
        const sender = pc.getSenders().find(s => s.track?.kind === 'video')
        if (sender) sender.replaceTrack(screenTrack)
      })
      const combined = new MediaStream([screenTrack, ...(localStream?.getAudioTracks() ?? [])])
      applyStream(myUserId, combined)
    } catch {}
  }
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
  const temp = { id: Date.now(), userId: myUserId, name: auth.user?.name || 'Bạn', text }
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

  // Get local stream
  try {
    localStream = await navigator.mediaDevices.getUserMedia({
      video: lobby.selectedCamera ? { deviceId: { exact: lobby.selectedCamera } } : true,
      audio: lobby.selectedMic  ? { deviceId: { exact: lobby.selectedMic  } } : true,
    })
    if (!camOn.value) localStream.getVideoTracks().forEach(t => { t.enabled = false })
    if (!micOn.value) localStream.getAudioTracks().forEach(t => { t.enabled = false })
  } catch { localStream = null }

  // Add self tile
  const self = {
    userId: myUserId, name: auth.user?.name || 'Bạn',
    isMe: true, isHost: session.value?.teacher?.id === myUserId,
    camOn: camOn.value, micOn: micOn.value, status: 'connected',
  }
  participants.value.push(self)
  if (localStream) {
    streams.set(myUserId, localStream)
    await nextTick()
    const el = videoRefs.get(myUserId)
    if (el) el.srcObject = localStream
  }

  // Join room — get ICE servers + existing participants
  try {
    const { data } = await api.post(`/live/rooms/${sessionId}/join`)
    iceServers = data.data.ice_servers ?? []

    for (const p of data.data.participants ?? []) {
      if (p.user_id === myUserId) continue
      participants.value.push({
        userId: p.user_id, name: p.user?.name ?? `User ${p.user_id}`,
        isMe: false, isHost: p.role === 'host',
        camOn: true, micOn: true, status: 'connecting',
      })
      await createPeerConnection(p.user_id, true)
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
</style>
