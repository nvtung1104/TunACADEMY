<template>
  <div class="min-h-screen flex">

    <!-- ── Left: Brand panel ─────────────────────────────────── -->
    <div class="hidden lg:flex lg:w-[52%] bg-[#0d0c22] flex-col justify-between p-14 relative overflow-hidden select-none">
      <!-- Decorative blobs -->
      <div class="absolute -top-32 -right-32 w-80 h-80 bg-[#d63015] opacity-10 rounded-full blur-3xl pointer-events-none" />
      <div class="absolute -bottom-40 -left-20 w-96 h-96 bg-[#d63015] opacity-5 rounded-full blur-3xl pointer-events-none" />
      <div class="absolute top-1/2 right-8 -translate-y-1/2 w-40 h-40 bg-[#d63015] opacity-[0.06] rounded-full blur-2xl pointer-events-none" />

      <!-- Logo -->
      <RouterLink to="/" class="flex items-center gap-3 relative z-10 w-fit">
        <img src="/images/logo.png" alt="TunAcademy" class="h-10 w-auto object-contain" />
        <span class="font-black text-xl text-white tracking-tight">TunAcademy</span>
      </RouterLink>

      <!-- Main copy -->
      <div class="relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#d63015]/15 border border-[#d63015]/20 rounded-full mb-6">
          <span class="w-1.5 h-1.5 bg-[#d63015] rounded-full" />
          <span class="text-[#d63015] text-xs font-bold tracking-wide">Nền tảng học tập K-12</span>
        </div>

        <h2 class="text-[2.6rem] font-black text-white leading-[1.15] mb-5">
          Học thật vui,<br />
          <span class="text-[#d63015]">thi thật tự tin</span>
        </h2>

        <p class="text-white/40 text-sm leading-relaxed max-w-xs">
          Hơn 10.000 học sinh và giáo viên đang học tập mỗi ngày trên TunAcademy — hoàn toàn miễn phí.
        </p>

        <!-- Stats -->
        <div class="flex items-center gap-0 mt-10">
          <div class="pr-8">
            <p class="text-3xl font-black text-white">10K+</p>
            <p class="text-xs text-white/30 mt-0.5 font-medium">Học sinh</p>
          </div>
          <div class="w-px h-10 bg-white/10 mx-0" />
          <div class="px-8">
            <p class="text-3xl font-black text-white">500+</p>
            <p class="text-xs text-white/30 mt-0.5 font-medium">Bài học</p>
          </div>
          <div class="w-px h-10 bg-white/10" />
          <div class="pl-8">
            <p class="text-3xl font-black text-white">K-12</p>
            <p class="text-xs text-white/30 mt-0.5 font-medium">Cấp độ</p>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <p class="text-white/15 text-xs relative z-10 font-medium">
        © {{ new Date().getFullYear() }} TunAcademy. All rights reserved.
      </p>
    </div>

    <!-- ── Right: Form panel ──────────────────────────────────── -->
    <div class="flex-1 flex items-center justify-center p-6 bg-[#faf7f2]">
      <div class="w-full max-w-sm">

        <!-- Mobile logo (only on small screens) -->
        <div class="lg:hidden text-center mb-8">
          <RouterLink to="/" class="inline-flex flex-col items-center gap-2">
            <img src="/images/logo.png" alt="TunAcademy" class="h-12 w-auto object-contain" />
            <span class="font-black text-lg text-[#0d0c22]">TunAcademy</span>
          </RouterLink>
        </div>

        <!-- Heading -->
        <div class="mb-8">
          <h1 class="text-2xl font-black text-[#0d0c22]">Chào mừng trở lại 👋</h1>
          <p class="text-sm text-[#6b6a7a] mt-1.5">Đăng nhập để tiếp tục học tập</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-[#0d0c22] mb-1.5">Email</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="example@tunacademy.vn"
              class="w-full px-4 py-3 rounded-xl border border-[#e8e0d8] bg-white focus:outline-none focus:ring-2 focus:ring-[#d63015]/25 focus:border-[#d63015] text-sm transition-all placeholder:text-[#c4bdb5]"
              :disabled="loading"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-[#0d0c22] mb-1.5">Mật khẩu</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-xl border border-[#e8e0d8] bg-white focus:outline-none focus:ring-2 focus:ring-[#d63015]/25 focus:border-[#d63015] text-sm transition-all pr-12 placeholder:text-[#c4bdb5]"
                :disabled="loading"
                required
              />
              <button type="button" @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#a09890] hover:text-[#0d0c22] transition-colors">
                <svg v-if="showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Error -->
          <Transition name="fade">
            <div v-if="error"
              class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
              <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              {{ error }}
            </div>
          </Transition>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3 rounded-xl bg-[#d63015] hover:bg-[#c02a10] text-white font-bold text-sm transition-all disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg shadow-red-200/60 hover:-translate-y-0.5 active:translate-y-0"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
          </button>
        </form>

        <div class="mt-6 text-center">
          <RouterLink to="/"
            class="text-sm text-[#6b6a7a] hover:text-[#d63015] transition-colors font-medium">
            &larr; Quay về trang chủ
          </RouterLink>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '@stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = reactive({ email: '', password: '' })
const loading = ref(false)
const error = ref('')
const showPassword = ref(false)

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    await auth.login(form.email, form.password)
    const redirect = route.query.redirect
    router.push(typeof redirect === 'string' ? redirect : auth.homeRoute())
  } catch (e) {
    error.value = e.response?.data?.message || 'Email hoặc mật khẩu không đúng'
  } finally {
    loading.value = false
  }
}
</script>
