<template>
  <!-- Floating button -->
  <button
    class="help-fab"
    @click="open = !open"
    :aria-label="open ? 'Đóng hướng dẫn' : 'Mở hướng dẫn'"
  >
    <Transition name="icon-swap" mode="out-in">
      <!-- Close icon -->
      <svg v-if="open" key="close" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
      <!-- Question icon -->
      <svg v-else key="question" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
        <circle cx="12" cy="17" r="0.5" fill="currentColor"/>
      </svg>
    </Transition>
  </button>

  <!-- Backdrop -->
  <Transition name="fade">
    <div v-if="open" class="help-backdrop" @click="open = false" />
  </Transition>

  <!-- Panel -->
  <Transition name="panel-up">
    <div v-if="open" class="help-panel" role="dialog" aria-modal="true">
      <!-- Header -->
      <div class="help-panel-header">
        <div class="flex items-center gap-2.5">
          <div class="help-panel-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <circle cx="12" cy="17" r="0.5" fill="currentColor"/>
            </svg>
          </div>
          <div>
            <p class="help-panel-title">Trung tâm hỗ trợ</p>
            <p class="help-panel-subtitle">Hướng dẫn sử dụng TunAcademy</p>
          </div>
        </div>
        <button class="help-close-btn" @click="open = false">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Search -->
      <div class="help-search-wrap">
        <svg class="help-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input v-model="query" type="text" placeholder="Tìm hướng dẫn..." class="help-search-input" />
        <button v-if="query" @click="query = ''" class="help-search-clear">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Guide list -->
      <div class="help-list">
        <template v-if="filtered.length">
          <template v-for="group in filtered" :key="group.category">
            <p class="help-category">{{ group.category }}</p>
            <a
              v-for="item in group.items"
              :key="item.link"
              :href="item.link"
              target="_blank"
              rel="noopener"
              class="help-item"
              @click="open = false"
            >
              <span class="help-item-icon">{{ item.icon }}</span>
              <div class="help-item-body">
                <span class="help-item-title">{{ item.title }}</span>
                <span v-if="item.badge" class="help-item-badge" :class="`help-badge-${item.badge}`">
                  {{ badgeLabel[item.badge] }}
                </span>
              </div>
              <svg class="help-item-arrow" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </a>
          </template>
        </template>

        <div v-else class="help-empty">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-2 opacity-30">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <p>Không tìm thấy "<strong>{{ query }}</strong>"</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="help-footer">
        <a href="/guides/index.html" target="_blank" rel="noopener" @click="open = false">
          Xem đầy đủ hướng dẫn
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
            <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
          </svg>
        </a>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed } from 'vue'

const open = ref(false)
const query = ref('')

const badgeLabel = { hot: 'Phổ biến', new: 'Mới' }

const DOCS = '/guides'

const guides = [
  {
    category: 'Bắt đầu',
    items: [
      { title: 'Giới thiệu TunAcademy',   icon: '🏫', link: `${DOCS}/general-introduction.html`, badge: null },
      { title: 'Đăng ký / Đăng nhập',     icon: '🔑', link: `${DOCS}/register-login.html`,       badge: null },
      { title: 'Quản lý tài khoản',        icon: '👤', link: `${DOCS}/account-management.html`,   badge: null },
    ]
  },
  {
    category: 'Học sinh',
    items: [
      { title: 'Xem bài học',              icon: '🎬', link: `${DOCS}/lessons-guide.html`,         badge: 'hot' },
      { title: 'Làm bài tập',              icon: '✏️', link: `${DOCS}/assignment-guide.html`,      badge: null },
      { title: 'Thi trực tuyến',           icon: '📝', link: `${DOCS}/exam-guide.html`,            badge: null },
      { title: 'Học trực tuyến (Live)',    icon: '📡', link: `${DOCS}/live-guide.html`,            badge: 'new' },
    ]
  },
  {
    category: 'Giáo viên',
    items: [
      { title: 'Quản lý bài học',          icon: '📋', link: `${DOCS}/manage-lessons.html`,        badge: null },
      { title: 'Tạo đề thi & bài tập',    icon: '📊', link: `${DOCS}/manage-exams.html`,          badge: null },
      { title: 'Mở phòng trực tuyến',      icon: '🎙️', link: `${DOCS}/manage-live.html`,          badge: null },
    ]
  }
]

const filtered = computed(() => {
  if (!query.value.trim()) return guides
  const q = query.value.toLowerCase()
  return guides
    .map(g => ({ ...g, items: g.items.filter(i => i.title.toLowerCase().includes(q)) }))
    .filter(g => g.items.length > 0)
})
</script>

<style scoped>
/* ── Floating button ── */
.help-fab {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9990;
  width: 52px;
  height: 52px;
  border-radius: 16px;
  background: #d63015;
  color: #fff;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 20px rgba(214, 48, 21, 0.45);
  transition: transform 0.15s, box-shadow 0.15s, background 0.15s;
}
.help-fab:hover {
  background: #c02a10;
  transform: translateY(-2px);
  box-shadow: 0 6px 24px rgba(214, 48, 21, 0.5);
}
.help-fab:active { transform: scale(0.93); }

/* ── Backdrop ── */
.help-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9990;
  background: rgba(0,0,0,0.25);
  backdrop-filter: blur(2px);
}

/* ── Panel ── */
.help-panel {
  position: fixed;
  bottom: 92px;
  right: 20px;
  z-index: 9991;
  width: 330px;
  max-height: 72vh;
  background: #fff;
  border: 1px solid #e8e0d8;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(13,12,34,0.18);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  font-family: inherit;
}

@media (max-width: 480px) {
  .help-panel { left: 12px; right: 12px; width: auto; bottom: 84px; }
  .help-fab   { bottom: 20px; right: 20px; }
}

/* Header */
.help-panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px 12px;
  border-bottom: 1px solid #f0ebe4;
  flex-shrink: 0;
}
.help-panel-header-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: #d63015;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.help-panel-title    { font-size: 0.875rem; font-weight: 700; color: #0d0c22; line-height: 1.2; }
.help-panel-subtitle { font-size: 0.72rem;  color: #9b8f84; margin-top: 1px; }
.help-close-btn {
  width: 28px; height: 28px;
  border-radius: 8px;
  border: none;
  background: #f5f0eb;
  color: #6b5f57;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s, color 0.15s;
  flex-shrink: 0;
}
.help-close-btn:hover { background: #ede5dc; color: #0d0c22; }

/* Search */
.help-search-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 10px 12px;
  padding: 8px 12px;
  border: 1.5px solid #ede5dc;
  border-radius: 10px;
  background: #faf7f2;
  flex-shrink: 0;
}
.help-search-icon { color: #9b8f84; flex-shrink: 0; }
.help-search-input {
  flex: 1;
  background: none;
  border: none;
  outline: none;
  font-size: 0.82rem;
  color: #0d0c22;
}
.help-search-input::placeholder { color: #b5a89e; }
.help-search-clear {
  background: none; border: none; cursor: pointer;
  color: #9b8f84; display: flex; align-items: center;
  padding: 2px;
  border-radius: 4px;
  transition: color 0.15s;
}
.help-search-clear:hover { color: #0d0c22; }

/* List */
.help-list {
  overflow-y: auto;
  flex: 1;
  padding: 4px 8px 8px;
}
.help-category {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #9b8f84;
  padding: 8px 8px 4px;
  margin: 0;
}
.help-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 10px;
  border-radius: 10px;
  text-decoration: none;
  color: #0d0c22;
  cursor: pointer;
  transition: background 0.12s;
}
.help-item:hover { background: #faf7f2; }
.help-item-icon   { font-size: 1.15rem; width: 28px; text-align: center; flex-shrink: 0; }
.help-item-body   { flex: 1; display: flex; align-items: center; gap: 6px; min-width: 0; }
.help-item-title  { font-size: 0.82rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.help-item-badge  { flex-shrink: 0; font-size: 0.62rem; font-weight: 700; padding: 1px 6px; border-radius: 999px; }
.help-badge-hot   { background: #fee2e2; color: #b91c1c; }
.help-badge-new   { background: #dbeafe; color: #1d4ed8; }
.help-item-arrow  { flex-shrink: 0; color: #c4b9b0; }

/* Empty */
.help-empty {
  padding: 28px 16px;
  text-align: center;
  font-size: 0.82rem;
  color: #9b8f84;
}

/* Footer */
.help-footer {
  padding: 10px 16px;
  border-top: 1px solid #f0ebe4;
  flex-shrink: 0;
  text-align: center;
}
.help-footer a {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.78rem;
  font-weight: 600;
  color: #d63015;
  text-decoration: none;
  transition: opacity 0.15s;
}
.help-footer a:hover { opacity: 0.8; }

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from,  .fade-leave-to      { opacity: 0; }

.panel-up-enter-active { transition: opacity 0.22s, transform 0.24s cubic-bezier(0.34,1.56,0.64,1); }
.panel-up-leave-active { transition: opacity 0.15s, transform 0.15s ease-in; }
.panel-up-enter-from   { opacity: 0; transform: translateY(14px) scale(0.97); }
.panel-up-leave-to     { opacity: 0; transform: translateY(8px)  scale(0.97); }

.icon-swap-enter-active, .icon-swap-leave-active { transition: opacity 0.12s, transform 0.12s; }
.icon-swap-enter-from   { opacity: 0; transform: rotate(-45deg) scale(0.7); }
.icon-swap-leave-to     { opacity: 0; transform: rotate(45deg)  scale(0.7); }
</style>
