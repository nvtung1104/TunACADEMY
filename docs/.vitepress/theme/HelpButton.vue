<template>
  <!-- Floating button -->
  <button class="help-fab" @click="open = true" aria-label="Hướng dẫn sử dụng">
    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/>
      <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
      <line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
  </button>

  <!-- Backdrop -->
  <Transition name="fade">
    <div v-if="open" class="help-backdrop" @click="open = false" />
  </Transition>

  <!-- Panel -->
  <Transition name="slide-up">
    <div v-if="open" class="help-panel" role="dialog" aria-modal="true" aria-label="Hướng dẫn sử dụng">
      <!-- Header -->
      <div class="help-panel__header">
        <div class="help-panel__title">
          <span class="help-panel__icon">🗂️</span>
          Hướng Dẫn Sử Dụng
        </div>
        <button class="help-panel__close" @click="open = false" aria-label="Đóng">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Search -->
      <div class="help-panel__search">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input v-model="query" type="text" placeholder="Tìm hướng dẫn..." class="help-panel__search-input" />
      </div>

      <!-- Guide list -->
      <nav class="help-panel__list">
        <a
          v-for="guide in filtered"
          :key="guide.link"
          :href="guide.link"
          class="help-panel__item"
          @click="open = false"
        >
          <span class="help-panel__item-icon">{{ guide.icon }}</span>
          <div class="help-panel__item-body">
            <span class="help-panel__item-title">{{ guide.title }}</span>
            <span v-if="guide.badge" class="help-panel__badge" :class="`help-panel__badge--${guide.badge.type}`">{{ guide.badge.text }}</span>
          </div>
          <svg class="help-panel__item-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </a>

        <p v-if="filtered.length === 0" class="help-panel__empty">
          Không tìm thấy kết quả cho "<strong>{{ query }}</strong>"
        </p>
      </nav>

      <!-- Footer -->
      <div class="help-panel__footer">
        <a href="/" @click="open = false">Xem tất cả hướng dẫn →</a>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const open = ref(false)
const query = ref('')

const guides = [
  {
    title: 'Giới thiệu chung',
    link: '/general-introduction',
    icon: '📖',
    badge: { text: 'Nên đọc trước', type: 'featured' }
  },
  {
    title: 'Đăng ký / Đăng nhập',
    link: '/register-login',
    icon: '🔑',
    badge: null
  },
  {
    title: 'Đổi mật khẩu',
    link: '/change-password',
    icon: '🔒',
    badge: null
  },
  {
    title: 'Luyện nghe',
    link: '/listening-practice',
    icon: '🎧',
    badge: { text: 'Phổ biến', type: 'popular' }
  },
  {
    title: 'Luyện đọc',
    link: '/reading-practice',
    icon: '📝',
    badge: null
  },
  {
    title: 'Lồng tiếng',
    link: '/voice-acting-guide',
    icon: '🎙️',
    badge: null
  },
  {
    title: 'Đọc sách',
    link: '/book-reading-guide',
    icon: '📚',
    badge: null
  },
  {
    title: 'Học qua game',
    link: '/learning-through-game',
    icon: '🎮',
    badge: null
  },
  {
    title: 'Hướng dẫn cho người mới',
    link: '/beginner-guide',
    icon: '🌱',
    badge: { text: 'New', type: 'new' }
  },
  {
    title: 'Lộ trình học tập',
    link: '/roadmap',
    icon: '🗺️',
    badge: null
  }
]

const filtered = computed(() => {
  if (!query.value.trim()) return guides
  const q = query.value.toLowerCase()
  return guides.filter(g => g.title.toLowerCase().includes(q))
})
</script>
