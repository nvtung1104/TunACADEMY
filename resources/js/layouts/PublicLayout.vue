<template>
  <div class="min-h-screen flex flex-col bg-[#faf7f2]">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-[#e8e0d8]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <RouterLink to="/" class="flex items-center gap-2 shrink-0">
            <img src="/images/logo.png" alt="TunAcademy" class="h-9 w-auto object-contain" />
            <span class="font-black text-base tracking-tight text-[#d63015]">TunAcademy</span>
          </RouterLink>

          <!-- Desktop Nav -->
          <nav class="hidden md:flex items-center gap-1">
            <RouterLink
              v-for="item in navItems" :key="item.to"
              :to="item.to"
              class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
              :class="isActive(item.to)
                ? 'bg-red-50 text-[#d63015]'
                : 'text-[#0d0c22] hover:text-[#d63015] hover:bg-red-50/50'"
            >
              {{ item.label }}
            </RouterLink>
          </nav>

          <!-- Right side -->
          <div class="flex items-center gap-3">
            <!-- Logged in -->
            <template v-if="auth.isLoggedIn">
              <div class="relative" ref="userMenuRef">
                <button @click="userMenuOpen = !userMenuOpen"
                  class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-red-50 transition-colors">
                  <AvatarFrame
                    :src="auth.user?.avatar"
                    :name="auth.user?.name"
                    :gender="auth.user?.gender"
                    :frame="auth.user?.frame"
                    :size="32"
                  />
                  <span class="hidden sm:block text-sm font-semibold text-[#0d0c22] max-w-28 truncate">
                    {{ auth.user?.name }}
                  </span>
                  <svg class="w-4 h-4 text-[#6b6a7a] transition-transform" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <Transition name="dropdown">
                  <div v-if="userMenuOpen"
                    class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-[#e8e0d8] py-1 z-50">
                    <RouterLink :to="auth.homeRoute()"
                      class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-[#0d0c22] hover:bg-red-50 transition-colors"
                      @click="userMenuOpen = false">
                      <svg class="w-4 h-4 text-[#6b6a7a]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                      </svg>
                      Vào Dashboard
                    </RouterLink>
                    <div class="h-px bg-[#e8e0d8] my-1" />
                    <button @click="handleLogout"
                      class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      Đăng xuất
                    </button>
                  </div>
                </Transition>
              </div>
            </template>

            <!-- Not logged in -->
            <template v-else>
              <RouterLink to="/login"
                class="hidden sm:inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#d63015] hover:bg-[#c02a10] text-white text-sm font-bold transition-all shadow-md shadow-red-200 hover:-translate-y-0.5">
                Đăng nhập
              </RouterLink>
            </template>

            <!-- Mobile toggle -->
            <button @click="mobileOpen = !mobileOpen"
              class="md:hidden p-2 rounded-lg text-[#0d0c22] hover:bg-red-50 transition-colors">
              <svg v-if="!mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile Menu -->
        <Transition name="slide-down">
          <div v-if="mobileOpen" class="md:hidden border-t border-[#e8e0d8] py-3 space-y-1">
            <RouterLink
              v-for="item in navItems" :key="item.to"
              :to="item.to"
              class="block px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors"
              :class="isActive(item.to)
                ? 'bg-red-50 text-[#d63015]'
                : 'text-[#0d0c22] hover:bg-red-50 hover:text-[#d63015]'"
              @click="mobileOpen = false"
            >
              {{ item.label }}
            </RouterLink>
            <div class="pt-2 border-t border-[#e8e0d8] mt-2">
              <RouterLink v-if="!auth.isLoggedIn" to="/login"
                class="block px-4 py-2.5 text-sm font-bold text-[#d63015]"
                @click="mobileOpen = false">
                Đăng nhập
              </RouterLink>
              <template v-else>
                <RouterLink :to="auth.homeRoute()"
                  class="block px-4 py-2.5 text-sm font-semibold text-[#0d0c22]"
                  @click="mobileOpen = false">
                  Vào Dashboard
                </RouterLink>
                <button @click="handleLogout" class="block w-full text-left px-4 py-2.5 text-sm font-semibold text-red-600">
                  Đăng xuất
                </button>
              </template>
            </div>
          </div>
        </Transition>
      </div>
    </header>

    <!-- Main content -->
    <main class="flex-1">
      <RouterView v-slot="{ Component }">
        <transition
          enter-active-class="animate__animated animate__fadeIn animate__faster"
          leave-active-class="animate__animated animate__fadeOut animate__faster"
          mode="out-in"
        >
          <component :is="Component" />
        </transition>
      </RouterView>
    </main>

    <!-- Help Widget -->
    <HelpWidget />

    <!-- Footer -->
    <footer class="bg-[#0d0c22]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid sm:grid-cols-3 gap-8 mb-10">
          <!-- Brand -->
          <div>
            <div class="flex items-center gap-2 mb-4">
              <img src="/images/logo.png" alt="TunAcademy" class="h-9 w-auto object-contain" />
              <span class="font-black text-base text-white">TunAcademy</span>
            </div>
            <p class="text-sm text-white/40 leading-relaxed">
              Nền tảng học tập K-12 số 1 Việt Nam. Học mọi lúc, mọi nơi — hoàn toàn miễn phí.
            </p>
          </div>
          <!-- Links -->
          <div>
            <p class="text-xs font-bold uppercase tracking-widest text-white/30 mb-4">Nội dung</p>
            <nav class="flex flex-col gap-2">
              <RouterLink to="/lessons" class="text-sm text-white/50 hover:text-[#d63015] transition-colors">Bài học</RouterLink>
              <RouterLink to="/practice" class="text-sm text-white/50 hover:text-[#d63015] transition-colors">Bài tập</RouterLink>
              <RouterLink to="/exams" class="text-sm text-white/50 hover:text-[#d63015] transition-colors">Đề thi</RouterLink>
              <RouterLink to="/classrooms" class="text-sm text-white/50 hover:text-[#d63015] transition-colors">Lớp học</RouterLink>
            </nav>
          </div>
          <!-- Info -->
          <div>
            <p class="text-xs font-bold uppercase tracking-widest text-white/30 mb-4">Liên hệ</p>
            <div class="flex flex-col gap-2 text-sm text-white/40">
              <span>📧 contact@tunacademy.vn</span>
              <span>📍 Việt Nam</span>
            </div>
          </div>
        </div>
        <div class="pt-6 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-3">
          <p class="text-xs text-white/20">
            &copy; {{ new Date().getFullYear() }} TunAcademy. All rights reserved.
          </p>
          <RouterLink to="/" class="text-xs text-white/20 hover:text-[#d63015] transition-colors">Trang chủ</RouterLink>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter, RouterLink, RouterView } from 'vue-router'
import { useAuthStore } from '@stores/auth'
import AvatarFrame from '@components/common/AvatarFrame.vue'
import HelpWidget from '@components/common/HelpWidget.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const mobileOpen = ref(false)
const userMenuOpen = ref(false)
const userMenuRef = ref(null)

const navItems = [
  { to: '/', label: 'Trang chủ' },
  { to: '/lessons', label: 'Bài học' },
  { to: '/practice', label: 'Bài tập' },
  { to: '/exams', label: 'Đề thi' },
  { to: '/classrooms', label: 'Lớp học' },
]

function isActive(path) {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

async function handleLogout() {
  userMenuOpen.value = false
  mobileOpen.value = false
  await auth.logout()
  router.push('/')
}

function onClickOutside(e) {
  if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
    userMenuOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', onClickOutside))
onUnmounted(() => document.removeEventListener('click', onClickOutside))
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active { transition: all 0.15s ease; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-6px); }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.2s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
