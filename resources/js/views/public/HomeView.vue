<template>
  <div class="overflow-x-hidden">

    <!-- ── HERO ── -->
    <section class="relative bg-[#faf7f2] overflow-hidden pt-20 pb-32">
      <!-- Decorative blobs -->
      <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-red-100/60 rounded-full -translate-y-1/3 translate-x-1/3 blur-3xl pointer-events-none" />
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-orange-100/40 rounded-full translate-y-1/2 -translate-x-1/3 blur-3xl pointer-events-none" />

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-16">

          <!-- Left text -->
          <div class="flex-1 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-red-50 border border-red-200 text-red-600 text-sm font-semibold mb-8">
              <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse" />
              Nền tảng học tập K-12 số 1 Việt Nam
            </div>

            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-[#0d0c22] leading-[1.05] tracking-tight mb-6">
              Kho kiến thức<br />
              <span class="relative inline-block text-[#d63015]">bất tận
                <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 220 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2 8C50 2 110 2 140 5C170 8 195 8 218 4" stroke="#d63015" stroke-width="3" stroke-linecap="round" stroke-opacity="0.5"/>
                </svg>
              </span><br />
              <span class="text-[#0d0c22]">ngay trên thiết bị của bạn</span>
            </h1>

            <p class="text-lg sm:text-xl text-[#6b6a7a] leading-relaxed mb-10 max-w-xl mx-auto lg:mx-0">
              TunAcademy mang đến kho bài học, bài tập và đề thi trực tuyến dành riêng
              cho học sinh lớp 1–12. Học mọi lúc, mọi nơi — hoàn toàn miễn phí.
            </p>

            <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
              <RouterLink to="/lessons"
                class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-2xl bg-[#d63015] text-white font-bold text-base hover:bg-[#c02a10] transition-all shadow-lg shadow-red-200 hover:-translate-y-0.5">
                Khám phá ngay
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </RouterLink>
              <RouterLink v-if="auth.isLoggedIn" :to="auth.homeRoute()"
                class="inline-flex items-center gap-2 px-7 py-3.5 rounded-2xl border-2 border-[#0d0c22]/15 text-[#0d0c22] font-semibold text-base hover:bg-[#0d0c22]/5 transition-all">
                Vào Dashboard →
              </RouterLink>
              <RouterLink v-else to="/login"
                class="inline-flex items-center gap-2 px-7 py-3.5 rounded-2xl border-2 border-[#0d0c22]/15 text-[#0d0c22] font-semibold text-base hover:bg-[#0d0c22]/5 transition-all">
                Đăng nhập
              </RouterLink>
            </div>

            <!-- Stats row -->
            <div class="flex flex-wrap gap-8 mt-12 justify-center lg:justify-start">
              <div v-for="stat in stats" :key="stat.label" class="flex flex-col">
                <span class="text-3xl font-black text-[#d63015]">{{ stat.value }}</span>
                <span class="text-sm text-[#6b6a7a] mt-0.5">{{ stat.label }}</span>
              </div>
            </div>
          </div>

          <!-- Right — feature cards preview -->
          <div class="flex-1 hidden lg:block">
            <div class="relative grid grid-cols-2 gap-4 max-w-sm ml-auto">
              <div v-for="card in heroCards" :key="card.title"
                class="rounded-2xl p-5 border border-[#e8e0d8] shadow-sm"
                :class="card.bg">
                <span class="text-3xl mb-3 block">{{ card.icon }}</span>
                <p class="font-bold text-[#0d0c22] text-sm leading-tight">{{ card.title }}</p>
                <p class="text-xs text-[#6b6a7a] mt-1">{{ card.sub }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Wave bottom -->
      <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
          <path d="M0 60L1440 60L1440 10C1100 55 700 60 0 10L0 60Z" fill="#f0ebe3"/>
        </svg>
      </div>
    </section>

    <!-- ── FEATURES (game-card style) ── -->
    <section class="bg-[#f0ebe3] py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <p class="text-[#d63015] font-bold uppercase tracking-widest text-sm mb-3">Tính năng nổi bật</p>
          <h2 class="text-4xl sm:text-5xl font-black text-[#0d0c22] mb-4">Tất cả trong một nền tảng</h2>
          <p class="text-[#6b6a7a] text-lg max-w-xl mx-auto">Từ bài học lý thuyết đến đề thi trực tuyến — TunAcademy lo hết.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
          <div v-for="feature in features" :key="feature.title"
            class="group relative rounded-3xl overflow-hidden bg-white border border-[#e8e0d8] hover:shadow-xl hover:shadow-red-100/50 transition-all hover:-translate-y-1 cursor-default">
            <!-- Top color bar -->
            <div class="h-1.5 w-full" :class="feature.bar" />
            <div class="p-8">
              <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6 text-3xl" :class="feature.iconBg">
                {{ feature.icon }}
              </div>
              <h3 class="text-xl font-black text-[#0d0c22] mb-3">{{ feature.title }}</h3>
              <p class="text-[#6b6a7a] leading-relaxed mb-8 text-sm">{{ feature.desc }}</p>
              <RouterLink :to="feature.to"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all"
                :class="feature.btnClass">
                Xem ngay
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── SUBJECTS ── -->
    <section class="bg-[#faf7f2] py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <p class="text-[#d63015] font-bold uppercase tracking-widest text-sm mb-3">Danh mục</p>
          <h2 class="text-4xl sm:text-5xl font-black text-[#0d0c22] mb-4">Đa dạng môn học</h2>
          <p class="text-[#6b6a7a] text-lg">Bao quát đầy đủ chương trình K-12</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
          <div v-for="subject in subjects" :key="subject.name"
            class="group flex flex-col items-center gap-3 p-5 rounded-2xl bg-white border border-[#e8e0d8] hover:border-[#d63015]/40 hover:shadow-md hover:shadow-red-50 transition-all cursor-default hover:-translate-y-0.5">
            <span class="text-4xl group-hover:scale-110 transition-transform">{{ subject.icon }}</span>
            <span class="text-sm font-semibold text-[#0d0c22] text-center">{{ subject.name }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- ── GRADE LEVELS ── -->
    <section class="bg-[#f0ebe3] py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <p class="text-[#d63015] font-bold uppercase tracking-widest text-sm mb-3">Cấp học</p>
          <h2 class="text-4xl sm:text-5xl font-black text-[#0d0c22] mb-4">Từ lớp 1 đến lớp 12</h2>
          <p class="text-[#6b6a7a] text-lg">Nội dung được phân loại chính xác theo từng khối lớp</p>
        </div>

        <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-12 gap-3">
          <RouterLink v-for="grade in grades" :key="grade"
            :to="`/lessons?grade=${grade}`"
            class="group flex flex-col items-center justify-center py-5 rounded-2xl bg-white border-2 border-[#e8e0d8] hover:border-[#d63015] hover:bg-[#d63015] transition-all hover:-translate-y-0.5">
            <span class="text-xl font-black text-[#0d0c22] group-hover:text-white transition-colors">{{ grade }}</span>
            <span class="text-[10px] text-[#6b6a7a] group-hover:text-white/80 transition-colors mt-0.5">Lớp</span>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- ── HOW IT WORKS ── -->
    <section class="bg-[#faf7f2] py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <p class="text-[#d63015] font-bold uppercase tracking-widest text-sm mb-3">Bắt đầu</p>
          <h2 class="text-4xl sm:text-5xl font-black text-[#0d0c22] mb-4">3 bước đơn giản</h2>
          <p class="text-[#6b6a7a] text-lg">Dễ dàng, nhanh chóng và hoàn toàn miễn phí</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 relative">
          <!-- connector line -->
          <div class="hidden md:block absolute top-12 left-[calc(16.66%+2rem)] right-[calc(16.66%+2rem)] h-px bg-gradient-to-r from-[#d63015]/20 via-[#d63015]/50 to-[#d63015]/20" />

          <div v-for="(step, i) in steps" :key="step.title"
            class="flex flex-col items-center text-center p-8 rounded-3xl bg-white border border-[#e8e0d8] hover:shadow-md hover:shadow-red-50 transition-all">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-6 text-2xl font-black bg-[#d63015] text-white shadow-lg shadow-red-200">
              {{ i + 1 }}
            </div>
            <h3 class="text-xl font-bold text-[#0d0c22] mb-3">{{ step.title }}</h3>
            <p class="text-[#6b6a7a] text-sm leading-relaxed">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── CTA BANNER ── -->
    <section class="relative py-24 overflow-hidden bg-[#0d0c22]">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-20 -left-20 w-[500px] h-[500px] bg-[#d63015]/15 rounded-full blur-[100px]" />
        <div class="absolute -bottom-20 -right-20 w-[500px] h-[500px] bg-[#d63015]/10 rounded-full blur-[100px]" />
        <!-- Dot pattern -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.04]" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="dots" width="30" height="30" patternUnits="userSpaceOnUse">
              <circle cx="2" cy="2" r="1.5" fill="white"/>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#dots)" />
        </svg>
      </div>

      <div class="relative max-w-4xl mx-auto px-4 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#d63015]/20 border border-[#d63015]/30 text-red-300 text-sm font-semibold mb-8">
          🚀 Tham gia ngay hôm nay
        </div>
        <h2 class="text-4xl sm:text-6xl font-black text-white leading-tight mb-6">
          Sẵn sàng bắt đầu<br class="hidden sm:block" />
          hành trình học tập?
        </h2>
        <p class="text-xl text-white/60 mb-10 max-w-xl mx-auto">
          Hàng nghìn học sinh đã tin tưởng lựa chọn TunAcademy. Đến lượt bạn!
        </p>
        <div class="flex flex-wrap gap-4 justify-center">
          <RouterLink v-if="auth.isLoggedIn" :to="auth.homeRoute()"
            class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-[#d63015] text-white font-black text-lg hover:bg-[#c02a10] transition-all shadow-2xl shadow-red-900/30 hover:-translate-y-0.5">
            Vào Dashboard →
          </RouterLink>
          <RouterLink v-else to="/login"
            class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-[#d63015] text-white font-black text-lg hover:bg-[#c02a10] transition-all shadow-2xl shadow-red-900/30 hover:-translate-y-0.5">
            Đăng nhập ngay →
          </RouterLink>
          <RouterLink to="/lessons"
            class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl border-2 border-white/20 text-white font-bold text-lg hover:bg-white/10 transition-all">
            Khám phá bài học
          </RouterLink>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@stores/auth'

const auth = useAuthStore()

const stats = [
  { value: '500+', label: 'Bài học' },
  { value: '200+', label: 'Đề thi' },
  { value: '2.000+', label: 'Học sinh' },
  { value: '100%', label: 'Miễn phí' },
]

const heroCards = [
  { icon: '📚', title: 'Bài học phong phú', sub: '500+ bài học K-12', bg: 'bg-red-50' },
  { icon: '✏️', title: 'Bài tập thực hành', sub: 'Từ cơ bản đến nâng cao', bg: 'bg-orange-50' },
  { icon: '📝', title: 'Đề thi trực tuyến', sub: 'Chấm điểm tự động', bg: 'bg-amber-50' },
  { icon: '🏆', title: 'Theo dõi kết quả', sub: 'Tiến độ học tập rõ ràng', bg: 'bg-yellow-50' },
]

const features = [
  {
    icon: '📚',
    iconBg: 'bg-red-50',
    bar: 'bg-[#d63015]',
    btnClass: 'bg-red-50 text-[#d63015] hover:bg-red-100',
    title: 'Bài học phong phú',
    desc: 'Kho bài học đa dạng theo từng môn học và khối lớp, được biên soạn bởi giáo viên có kinh nghiệm. Học mọi lúc, mọi nơi.',
    to: '/lessons',
  },
  {
    icon: '✏️',
    iconBg: 'bg-orange-50',
    bar: 'bg-gradient-to-r from-orange-400 to-amber-400',
    btnClass: 'bg-orange-50 text-orange-600 hover:bg-orange-100',
    title: 'Bài tập thực hành',
    desc: 'Hàng trăm bài tập từ cơ bản đến nâng cao giúp học sinh củng cố kiến thức và rèn luyện kỹ năng làm bài hiệu quả.',
    to: '/practice',
  },
  {
    icon: '📝',
    iconBg: 'bg-emerald-50',
    bar: 'bg-gradient-to-r from-emerald-500 to-teal-400',
    btnClass: 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100',
    title: 'Đề thi trực tuyến',
    desc: 'Đề thi chuẩn cấu trúc, có giới hạn thời gian và hệ thống chấm điểm tự động chính xác ngay sau khi hoàn thành.',
    to: '/exams',
  },
]

const subjects = [
  { name: 'Toán học', icon: '🔢' },
  { name: 'Ngữ văn', icon: '📖' },
  { name: 'Tiếng Anh', icon: '🌏' },
  { name: 'Vật lý', icon: '⚡' },
  { name: 'Hóa học', icon: '🧪' },
  { name: 'Sinh học', icon: '🌱' },
  { name: 'Lịch sử', icon: '🏛️' },
  { name: 'Địa lý', icon: '🗺️' },
  { name: 'GDCD', icon: '⚖️' },
  { name: 'Tin học', icon: '💻' },
]

const grades = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]

const steps = [
  {
    title: 'Đăng nhập tài khoản',
    desc: 'Đăng nhập với tài khoản được cung cấp bởi nhà trường để truy cập đầy đủ tính năng.',
  },
  {
    title: 'Chọn bài học & đề thi',
    desc: 'Duyệt danh sách bài học, bài tập theo môn học và lớp. Học theo lộ trình có sẵn.',
  },
  {
    title: 'Theo dõi tiến độ',
    desc: 'Hoàn thành bài học, làm bài tập và theo dõi kết quả học tập trực tiếp trên hệ thống.',
  },
]
</script>
