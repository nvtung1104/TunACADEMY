<template>
  <div class="overflow-x-hidden">

    <!-- ── HERO ── -->
    <section class="relative bg-[#faf7f2] overflow-hidden pt-20 pb-32">
      <!-- Decorative blobs -->
      <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-purple-100/40 rounded-full -translate-y-1/3 translate-x-1/3 blur-3xl pointer-events-none animate-pulse duration-[10000ms]" />
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-indigo-100/30 rounded-full translate-y-1/2 -translate-x-1/3 blur-3xl pointer-events-none" />

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-16">

          <!-- Left text -->
          <div class="flex-1 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#7c3aed]/10 border border-[#7c3aed]/25 text-[#7c3aed] text-sm font-semibold mb-8 animate__animated animate__fadeInLeft">
              <span class="w-2 h-2 rounded-full bg-[#7c3aed] animate-pulse" />
              Nền tảng học tập K-12 số 1 Việt Nam
            </div>

            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-[#0d0c22] leading-[1.05] tracking-tight mb-6 animate__animated animate__fadeInLeft animate__delay-100ms">
              Kho kiến thức<br />
              <span class="relative inline-block text-[#7c3aed]">bất tận
                <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 220 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path class="underline-path" d="M2 8C50 2 110 2 140 5C170 8 195 8 218 4" stroke="#7c3aed" stroke-width="3" stroke-linecap="round" stroke-opacity="0.6"/>
                </svg>
              </span><br />
              <span class="text-[#0d0c22]">ngay trên thiết bị của bạn</span>
            </h1>

            <p class="text-lg sm:text-xl text-[#6b6a7a] leading-relaxed mb-10 max-w-xl mx-auto lg:mx-0 animate__animated animate__fadeInLeft animate__delay-200ms">
              TunAcademy mang đến kho bài học, bài tập và đề thi trực tuyến dành riêng
              cho học sinh lớp 1–12. Học mọi lúc, mọi nơi — hoàn toàn miễn phí.
            </p>

            <div class="flex flex-wrap gap-4 justify-center lg:justify-start animate__animated animate__fadeInLeft animate__delay-300ms">
              <RouterLink to="/practice"
                class="group inline-flex items-center gap-2.5 px-7 py-3.5 rounded-2xl bg-[#7c3aed] hover:bg-[#6d28d9] text-white font-semibold text-base transition-all shadow-sm active:scale-95 btn-animate">
                Khám phá ngay
                <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </RouterLink>
              <RouterLink v-slot="{ href, navigate }" v-if="auth.isLoggedIn" :to="auth.homeRoute()" custom>
                <a :href="href" @click="navigate"
                  class="inline-flex items-center gap-2 px-7 py-3.5 rounded-2xl border border-[#7c3aed]/25 text-[#7c3aed] font-semibold text-base hover:bg-[#7c3aed] hover:text-white hover:border-[#7c3aed] transition-all active:scale-95 btn-animate">
                  Vào Dashboard →
                </a>
              </RouterLink>
              <RouterLink v-else to="/login"
                class="inline-flex items-center gap-2 px-7 py-3.5 rounded-2xl border border-[#7c3aed]/25 text-[#7c3aed] font-semibold text-base hover:bg-[#7c3aed] hover:text-white hover:border-[#7c3aed] transition-all active:scale-95 btn-animate">
                Đăng nhập
              </RouterLink>
            </div>


          </div>

          <!-- Right — feature cards preview -->
          <div class="flex-1 hidden lg:block">
            <div class="relative grid grid-cols-2 gap-6 max-w-md ml-auto">
              <!-- Soft background glow behind cards -->
              <div class="absolute inset-0 bg-[#7c3aed]/5 rounded-full filter blur-3xl pointer-events-none -z-10 animate-pulse duration-[8000ms]"></div>
              
              <div v-for="(card, i) in heroCards" :key="card.title"
                class="w-full"
                :class="[i % 2 === 0 ? 'float-animation' : 'float-animation-delayed']">
                <div
                  class="hero-preview-card rounded-3xl p-6 border bg-white cursor-default animate__animated animate__fadeInRight"
                  :class="[`hero-preview-card-${i}`, card.bg]"
                  :style="{ animationDelay: `${i * 100}ms` }">
                  <div class="hero-card-icon w-12 h-12 rounded-xl flex items-center justify-center mb-4 text-2xl transition-transform duration-300" :class="card.iconBg">
                    {{ card.icon }}
                  </div>
                  <p class="font-bold text-[#0d0c22] text-sm leading-tight">{{ card.title }}</p>
                  <p class="text-xs text-[#6b6a7a] mt-1.5 font-medium">{{ card.sub }}</p>
                </div>
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

        <div class="grid md:grid-cols-3 gap-8">
          <div v-for="(feature, i) in features" :key="feature.title"
            class="group relative rounded-3xl overflow-hidden bg-white border border-[#e5e7eb] cursor-default transition-all duration-300 hover:-translate-y-2 hover:shadow-md flex flex-col justify-between min-h-[380px] animate__animated animate__fadeInUp"
            :style="{ animationDelay: `${i * 100}ms` }">
            <!-- Top color bar with expansion on hover -->
            <div class="h-1.5 w-full transition-all duration-300 group-hover:h-3" :class="feature.bar" />
            <div class="p-8 flex flex-col justify-between flex-1">
              <div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6 text-3xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6" :class="feature.iconBg">
                  {{ feature.icon }}
                </div>
                <h3 class="text-xl font-bold text-[#0d0c22] mb-3">{{ feature.title }}</h3>
                <p class="text-[#6b6a7a] leading-relaxed mb-8 text-xs sm:text-sm">{{ feature.desc }}</p>
              </div>
              <div>
                <RouterLink :to="feature.to"
                  class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all duration-300 cursor-pointer shadow-sm active:scale-95"
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

        <!-- Row 1: moves to the right -->
        <div class="w-full overflow-hidden mb-6 relative">
          <!-- Gradient overlay left & right for a premium fade effect -->
          <div class="absolute inset-y-0 left-0 w-24 marquee-fade-left z-10 pointer-events-none"></div>
          <div class="absolute inset-y-0 right-0 w-24 marquee-fade-right z-10 pointer-events-none"></div>
          
          <div class="marquee-track-right flex gap-6 w-max py-2">
            <!-- Set 1 -->
            <div class="flex gap-6 shrink-0">
              <div v-for="subject in subjectsRow1" :key="subject.name"
                class="group flex flex-col items-center p-6 rounded-2xl bg-white border border-[#e5e7eb] cursor-default card-animate w-[220px]">
                <!-- Icon container -->
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-transform group-hover:scale-110"
                  :class="subject.iconBg">
                  <span class="text-3xl">{{ subject.icon }}</span>
                </div>
                <!-- Title -->
                <span class="text-base font-semibold text-center leading-tight mb-2" :class="subject.color">
                  {{ subject.name }}
                </span>
                <!-- Grade level badge -->
                <span class="text-xs font-semibold px-3 py-1 rounded-full" :class="subject.badge">
                  {{ subject.grades }}
                </span>
              </div>
            </div>
            <!-- Set 2 (Duplicate for loop) -->
            <div class="flex gap-6 shrink-0">
              <div v-for="subject in subjectsRow1" :key="subject.name + '-dup'"
                class="group flex flex-col items-center p-6 rounded-2xl bg-white border border-[#e5e7eb] cursor-default card-animate w-[220px]">
                <!-- Icon container -->
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-transform group-hover:scale-110"
                  :class="subject.iconBg">
                  <span class="text-3xl">{{ subject.icon }}</span>
                </div>
                <!-- Title -->
                <span class="text-base font-semibold text-center leading-tight mb-2" :class="subject.color">
                  {{ subject.name }}
                </span>
                <!-- Grade level badge -->
                <span class="text-xs font-semibold px-3 py-1 rounded-full" :class="subject.badge">
                  {{ subject.grades }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Row 2: moves to the left -->
        <div class="w-full overflow-hidden relative py-2">
          <!-- Gradient overlay left & right for a premium fade effect -->
          <div class="absolute inset-y-0 left-0 w-24 marquee-fade-left z-10 pointer-events-none"></div>
          <div class="absolute inset-y-0 right-0 w-24 marquee-fade-right z-10 pointer-events-none"></div>
          
          <div class="marquee-track-left flex gap-6 w-max">
            <!-- Set 1 -->
            <div class="flex gap-6 shrink-0">
              <div v-for="subject in subjectsRow2" :key="subject.name"
                class="group flex flex-col items-center p-6 rounded-2xl bg-white border border-[#e5e7eb] cursor-default card-animate w-[220px]">
                <!-- Icon container -->
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-transform group-hover:scale-110"
                  :class="subject.iconBg">
                  <span class="text-3xl">{{ subject.icon }}</span>
                </div>
                <!-- Title -->
                <span class="text-base font-semibold text-center leading-tight mb-2" :class="subject.color">
                  {{ subject.name }}
                </span>
                <!-- Grade level badge -->
                <span class="text-xs font-semibold px-3 py-1 rounded-full" :class="subject.badge">
                  {{ subject.grades }}
                </span>
              </div>
            </div>
            <!-- Set 2 (Duplicate for loop) -->
            <div class="flex gap-6 shrink-0">
              <div v-for="subject in subjectsRow2" :key="subject.name + '-dup'"
                class="group flex flex-col items-center p-6 rounded-2xl bg-white border border-[#e5e7eb] cursor-default card-animate w-[220px]">
                <!-- Icon container -->
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-transform group-hover:scale-110"
                  :class="subject.iconBg">
                  <span class="text-3xl">{{ subject.icon }}</span>
                </div>
                <!-- Title -->
                <span class="text-base font-semibold text-center leading-tight mb-2" :class="subject.color">
                  {{ subject.name }}
                </span>
                <!-- Grade level badge -->
                <span class="text-xs font-semibold px-3 py-1 rounded-full" :class="subject.badge">
                  {{ subject.grades }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── GRADE LEVELS ── -->
    <section class="bg-[#f0ebe3] py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <p class="text-[#d63015] font-bold uppercase tracking-widest text-sm mb-3">Cấp học</p>
          <h2 class="text-4xl sm:text-5xl font-black text-[#0d0c22] mb-4">Khối lớp học tập</h2>
          <p class="text-[#6b6a7a] text-lg">Hệ thống bài giảng phân loại chính xác theo từng cấp học</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Cấp Tiểu Học -->
          <div class="bg-white rounded-3xl p-6 border border-[#e5e7eb] shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between min-h-[320px] animate__animated animate__fadeInUp">
            <div>
              <div class="flex items-center gap-3.5 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-2xl shadow-sm">
                  🎒
                </div>
                <div>
                  <h3 class="text-lg font-bold text-[#0d0c22]">Cấp Tiểu học</h3>
                  <p class="text-xs text-[#6b6a7a]">Khối lớp 1 - 5</p>
                </div>
              </div>
              <p class="text-xs text-[#6b6a7a] leading-relaxed mb-6">
                Giúp các bé làm quen với tư duy Toán học trực quan, kỹ năng đánh vần Tiếng Việt cơ bản và các bài học Tiếng Anh sinh động.
              </p>
            </div>
            <div class="grid grid-cols-5 gap-2">
              <RouterLink v-for="grade in [1, 2, 3, 4, 5]" :key="grade"
                :to="`/lessons?grade=${grade}`"
                class="flex flex-col items-center justify-center py-3.5 rounded-xl border border-blue-100/70 hover:bg-[#3b82f6] hover:border-[#3b82f6] hover:text-white hover:scale-105 active:scale-95 transition-all text-[#3b82f6] font-bold cursor-pointer bg-blue-50/10">
                <span class="text-base leading-none font-bold">{{ grade }}</span>
                <span class="text-[9px] font-medium opacity-85 mt-0.5">Lớp</span>
              </RouterLink>
            </div>
          </div>

          <!-- Cấp THCS -->
          <div class="bg-white rounded-3xl p-6 border border-[#e5e7eb] shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between min-h-[320px] animate__animated animate__fadeInUp animate__delay-100ms">
            <div>
              <div class="flex items-center gap-3.5 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-2xl shadow-sm">
                  📝
                </div>
                <div>
                  <h3 class="text-lg font-bold text-[#0d0c22]">Cấp THCS</h3>
                  <p class="text-xs text-[#6b6a7a]">Khối lớp 6 - 9</p>
                </div>
              </div>
              <p class="text-xs text-[#6b6a7a] leading-relaxed mb-6">
                Rèn luyện khả năng lập luận, các khái niệm Vật lý, Hóa học, Sinh học thực tiễn và tư duy phân tích các tác phẩm văn học.
              </p>
            </div>
            <div class="grid grid-cols-4 gap-2">
              <RouterLink v-for="grade in [6, 7, 8, 9]" :key="grade"
                :to="`/lessons?grade=${grade}`"
                class="flex flex-col items-center justify-center py-3.5 rounded-xl border border-emerald-100/70 hover:bg-[#10b981] hover:border-[#10b981] hover:text-white hover:scale-105 active:scale-95 transition-all text-[#10b981] font-bold cursor-pointer bg-emerald-50/10">
                <span class="text-base leading-none font-bold">{{ grade }}</span>
                <span class="text-[9px] font-medium opacity-85 mt-0.5">Lớp</span>
              </RouterLink>
            </div>
          </div>

          <!-- Cấp THPT -->
          <div class="bg-white rounded-3xl p-6 border border-[#e5e7eb] shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between min-h-[320px] animate__animated animate__fadeInUp animate__delay-200ms">
            <div>
              <div class="flex items-center gap-3.5 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center text-2xl shadow-sm">
                  🎓
                </div>
                <div>
                  <h3 class="text-lg font-bold text-[#0d0c22]">Cấp THPT</h3>
                  <p class="text-xs text-[#6b6a7a]">Khối lớp 10 - 12</p>
                </div>
              </div>
              <p class="text-xs text-[#6b6a7a] leading-relaxed mb-6">
                Chuẩn bị toàn diện các kiến thức chuyên sâu lớp 10-12, đề thi bám sát cấu trúc giúp chinh phục thành công kỳ thi THPT Quốc gia.
              </p>
            </div>
            <div class="grid grid-cols-3 gap-2">
              <RouterLink v-for="grade in [10, 11, 12]" :key="grade"
                :to="`/lessons?grade=${grade}`"
                class="flex flex-col items-center justify-center py-3.5 rounded-xl border border-purple-100/70 hover:bg-[#7c3aed] hover:border-[#7c3aed] hover:text-white hover:scale-105 active:scale-95 transition-all text-[#7c3aed] font-bold cursor-pointer bg-purple-50/10">
                <span class="text-base leading-none font-bold">{{ grade }}</span>
                <span class="text-[9px] font-medium opacity-85 mt-0.5">Lớp</span>
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── HOW IT WORKS ── -->
    <section class="bg-[#faf7f2] py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#7c3aed]/10 border border-[#7c3aed]/25 text-[#7c3aed] text-xs font-semibold uppercase tracking-widest mb-4 animate__animated animate__fadeInUp">
            Quy trình
          </div>
          <h2 class="text-4xl sm:text-5xl font-black text-[#0d0c22] mb-4 animate__animated animate__fadeInUp animate__delay-100ms">3 bước đơn giản</h2>
          <p class="text-[#6b6a7a] text-lg animate__animated animate__fadeInUp animate__delay-200ms">Dễ dàng, nhanh chóng và hoàn toàn miễn phí</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 relative z-10">
          <!-- Custom animated dashed connector line -->
          <svg class="hidden md:block absolute top-16 left-[16.6%] right-[16.6%] w-[66.8%] h-4 pointer-events-none z-0" viewBox="0 0 100 4" preserveAspectRatio="none" fill="none">
            <path d="M 0 2 L 100 2" stroke="#7c3aed" stroke-width="2" stroke-dasharray="6 4" class="animated-flow-line" opacity="0.35"/>
          </svg>

          <div v-for="(step, i) in steps" :key="step.title"
            class="how-step-card group relative flex flex-col items-center text-center p-8 rounded-3xl bg-white border border-[#e5e7eb] cursor-default animate__animated animate__fadeInUp"
            :class="`how-step-card-${i}`"
            :style="{ animationDelay: `${i * 150}ms` }">
            
            <!-- Number badge with halo glow -->
            <div class="relative mb-6">
              <!-- Outer glow ring -->
              <div class="absolute inset-0 rounded-2xl scale-110 opacity-0 group-hover:opacity-100 group-hover:scale-125 transition-all duration-300 blur-sm" :class="step.numGlow"></div>
              
              <div class="relative w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-bold transition-all duration-300 group-hover:scale-110" :class="step.badgeClass">
                {{ i + 1 }}
              </div>
            </div>

            <h3 class="text-xl font-bold text-[#0d0c22] mb-3 transition-colors duration-300 group-hover:text-[#7c3aed]">{{ step.title }}</h3>
            <p class="text-[#6b6a7a] text-sm leading-relaxed">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── CTA BANNER ── -->
    <section class="relative py-24 overflow-hidden bg-[#0d0c22]">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-[#7c3aed]/20 rounded-full blur-[120px] pointer-events-none animate-pulse duration-[8000ms]" />
        <div class="absolute -bottom-40 -right-40 w-[600px] h-[600px] bg-blue-500/15 rounded-full blur-[120px] pointer-events-none" />
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
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#7c3aed]/15 border border-[#7c3aed]/30 text-purple-300 text-sm font-semibold mb-8 animate__animated animate__fadeInUp">
          🚀 Tham gia ngay hôm nay
        </div>
        <h2 class="text-4xl sm:text-6xl font-black text-white leading-tight mb-6 animate__animated animate__fadeInUp animate__delay-100ms">
          Sẵn sàng bắt đầu<br class="hidden sm:block" />
          hành trình học tập?
        </h2>
        <p class="text-xl text-white/60 mb-10 max-w-xl mx-auto animate__animated animate__fadeInUp animate__delay-200ms">
          Hàng nghìn học sinh đã tin tưởng lựa chọn TunAcademy. Đến lượt bạn!
        </p>
        <div class="flex flex-wrap gap-4 justify-center animate__animated animate__fadeInUp animate__delay-300ms">
          <RouterLink v-if="auth.isLoggedIn" :to="auth.homeRoute()"
            class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-[#7c3aed] text-white font-bold text-lg hover:bg-[#6d28d9] transition-all shadow-lg hover:shadow-[#7c3aed]/30 active:scale-95 btn-animate">
            Vào Dashboard →
          </RouterLink>
          <RouterLink v-else to="/login"
            class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-[#7c3aed] text-white font-bold text-lg hover:bg-[#6d28d9] transition-all shadow-lg hover:shadow-[#7c3aed]/30 active:scale-95 btn-animate">
            Đăng nhập ngay →
          </RouterLink>
          <RouterLink to="/lessons"
            class="cta-outline-btn inline-flex items-center gap-2 px-8 py-4 rounded-2xl text-white font-bold text-lg hover:bg-white/10 transition-all btn-animate">
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



const heroCards = [
  { icon: '📚', title: 'Bài học phong phú', sub: '500+ bài học K-12', bg: 'border-blue-100 bg-blue-50/20', iconBg: 'bg-blue-50 text-blue-500', shadow: 'hover:shadow-blue-500/10' },
  { icon: '✏️', title: 'Bài tập thực hành', sub: 'Từ cơ bản đến nâng cao', bg: 'border-emerald-100 bg-emerald-50/20', iconBg: 'bg-emerald-50 text-emerald-500', shadow: 'hover:shadow-emerald-500/10' },
  { icon: '📝', title: 'Đề thi trực tuyến', sub: 'Chấm điểm tự động', bg: 'border-purple-100 bg-purple-50/20', iconBg: 'bg-purple-50 text-[#7c3aed]', shadow: 'hover:shadow-purple-500/10' },
  { icon: '🏆', title: 'Theo dõi kết quả', sub: 'Tiến độ học tập rõ ràng', bg: 'border-amber-100 bg-amber-50/20', iconBg: 'bg-amber-50 text-amber-600', shadow: 'hover:shadow-amber-500/10' },
]

const features = [
  {
    icon: '📚',
    iconBg: 'bg-blue-55/40',
    bar: 'bg-blue-500',
    btnClass: 'border border-blue-100 text-blue-600 hover:bg-blue-500 hover:text-white',
    title: 'Bài học phong phú',
    desc: 'Kho bài học đa dạng theo từng môn học và khối lớp, được biên soạn bởi giáo viên có kinh nghiệm. Học mọi lúc, mọi nơi.',
    to: '/lessons',
  },
  {
    icon: '✏️',
    iconBg: 'bg-emerald-55/40',
    bar: 'bg-emerald-500',
    btnClass: 'border border-emerald-100 text-emerald-600 hover:bg-emerald-500 hover:text-white',
    title: 'Bài tập thực hành',
    desc: 'Hàng trăm bài tập từ cơ bản đến nâng cao giúp học sinh củng cố kiến thức và rèn luyện kỹ năng làm bài hiệu quả.',
    to: '/practice',
  },
  {
    icon: '📝',
    iconBg: 'bg-purple-55/40',
    bar: 'bg-[#7c3aed]',
    btnClass: 'border border-purple-100 text-[#7c3aed] hover:bg-[#7c3aed] hover:text-white',
    title: 'Đề thi trực tuyến',
    desc: 'Đề thi chuẩn cấu trúc, có giới hạn thời gian và hệ thống chấm điểm tự động chính xác ngay sau khi hoàn thành.',
    to: '/exams',
  },
]

const subjects = [
  { name: 'Toán học', icon: '🔢', grades: 'Lớp 1–12', color: 'text-[#1e40af]', iconBg: 'bg-[#eff6ff]', badge: 'bg-[#eff6ff] text-[#2563eb]' },
  { name: 'Ngữ văn', icon: '📖', grades: 'Lớp 1–12', color: 'text-[#065f46]', iconBg: 'bg-[#ecfdf5]', badge: 'bg-[#ecfdf5] text-[#059669]' },
  { name: 'Tiếng Anh', icon: '🌏', grades: 'Lớp 3–12', color: 'text-[#4338ca]', iconBg: 'bg-[#f5f3ff]', badge: 'bg-[#f5f3ff] text-[#6366f1]' },
  { name: 'Vật lý', icon: '⚡', grades: 'Lớp 6–12', color: 'text-[#b45309]', iconBg: 'bg-[#fffbeb]', badge: 'bg-[#fffbeb] text-[#d97706]' },
  { name: 'Hóa học', icon: '🧪', grades: 'Lớp 8–12', color: 'text-[#be123c]', iconBg: 'bg-[#fff1f2]', badge: 'bg-[#fff1f2] text-[#e11d48]' },
  { name: 'Sinh học', icon: '🌱', grades: 'Lớp 6–12', color: 'text-[#15803d]', iconBg: 'bg-[#f0fdf4]', badge: 'bg-[#f0fdf4] text-[#16a34a]' },
  { name: 'Lịch sử', icon: '🏛️', grades: 'Lớp 1–12', color: 'text-[#c2410c]', iconBg: 'bg-[#fff7ed]', badge: 'bg-[#fff7ed] text-[#ea580c]' },
  { name: 'Địa lý', icon: '🗺️', grades: 'Lớp 6–12', color: 'text-[#0369a1]', iconBg: 'bg-[#f0f9ff]', badge: 'bg-[#f0f9ff] text-[#0284c7]' },
  { name: 'GDCD', icon: '⚖️', grades: 'Lớp 6–12', color: 'text-[#854d0e]', iconBg: 'bg-[#fefce8]', badge: 'bg-[#fefce8] text-[#ca8a04]' },
  { name: 'Tin học', icon: '💻', grades: 'Lớp 3–12', color: 'text-[#1d4ed8]', iconBg: 'bg-[#eff6ff]', badge: 'bg-[#eff6ff] text-[#2563eb]' },
]

const subjectsRow1 = subjects.slice(0, 5)
const subjectsRow2 = subjects.slice(5)

const grades = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]

const steps = [
  {
    title: 'Đăng nhập tài khoản',
    desc: 'Đăng nhập với tài khoản được cung cấp bởi nhà trường để truy cập đầy đủ tính năng.',
    badgeClass: 'bg-blue-50 text-blue-600 border border-blue-200/60 shadow-blue-100/50',
    numGlow: 'bg-blue-500/15'
  },
  {
    title: 'Chọn bài học & đề thi',
    desc: 'Duyệt danh sách bài học, bài tập theo môn học và lớp. Học theo lộ trình có sẵn.',
    badgeClass: 'bg-emerald-50 text-emerald-600 border border-emerald-200/60 shadow-emerald-100/50',
    numGlow: 'bg-emerald-500/15'
  },
  {
    title: 'Theo dõi tiến độ',
    desc: 'Hoàn thành bài học, làm bài tập và theo dõi kết quả học tập trực tiếp trên hệ thống.',
    badgeClass: 'bg-purple-50 text-[#7c3aed] border border-purple-200/60 shadow-purple-100/50',
    numGlow: 'bg-purple-500/15'
  },
]
</script>

<style scoped>
.marquee-track-right {
  animation: scroll-right 30s linear infinite;
}

.marquee-track-left {
  animation: scroll-left 30s linear infinite;
}

.marquee-track-right:hover,
.marquee-track-left:hover {
  animation-play-state: paused;
}

.marquee-fade-left {
  background-image: linear-gradient(to right, #faf7f2, transparent) !important;
}

.marquee-fade-right {
  background-image: linear-gradient(to left, #faf7f2, transparent) !important;
}

/* Floating Card Animations on Wrapper */
.float-animation {
  animation: float-y 5s ease-in-out infinite alternate;
}

.float-animation-delayed {
  animation: float-y-delayed 6s ease-in-out infinite alternate;
}

/* Underline SVG Draw-in animation */
.underline-path {
  stroke-dasharray: 220;
  stroke-dashoffset: 220;
  animation: draw-underline 1.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
  animation-delay: 0.6s;
}

/* Custom styles for Hero cards to prevent app.css shadow override */
.hero-preview-card {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.hero-preview-card-0 {
  --hover-border: #3b82f6;
  --hover-shadow: 0 12px 30px -5px rgba(59, 130, 246, 0.18);
}
.hero-preview-card-1 {
  --hover-border: #10b981;
  --hover-shadow: 0 12px 30px -5px rgba(16, 185, 129, 0.18);
}
.hero-preview-card-2 {
  --hover-border: #7c3aed;
  --hover-shadow: 0 12px 30px -5px rgba(124, 58, 237, 0.18);
}
.hero-preview-card-3 {
  --hover-border: #f59e0b;
  --hover-shadow: 0 12px 30px -5px rgba(245, 158, 11, 0.18);
}

.hero-preview-card:hover {
  transform: scale(1.05) !important;
  border-color: var(--hover-border) !important;
  box-shadow: var(--hover-shadow) !important;
}

.hero-preview-card:hover .hero-card-icon {
  transform: scale(1.15) rotate(6deg);
}

@keyframes float-y {
  0% {
    transform: translateY(0);
  }
  100% {
    transform: translateY(-16px);
  }
}

@keyframes float-y-delayed {
  0% {
    transform: translateY(-8px);
  }
  100% {
    transform: translateY(8px);
  }
}

@keyframes draw-underline {
  to {
    stroke-dashoffset: 0;
  }
}

/* How it Works Step Custom styles */
.how-step-card {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.how-step-card-0 {
  --step-hover-border: #3b82f6;
  --step-hover-shadow: 0 12px 30px -5px rgba(59, 130, 246, 0.12);
}
.how-step-card-1 {
  --step-hover-border: #10b981;
  --step-hover-shadow: 0 12px 30px -5px rgba(16, 185, 129, 0.12);
}
.how-step-card-2 {
  --step-hover-border: #7c3aed;
  --step-hover-shadow: 0 12px 30px -5px rgba(124, 58, 237, 0.12);
}

.how-step-card:hover {
  transform: translateY(-8px) !important;
  border-color: var(--step-hover-border) !important;
  box-shadow: var(--step-hover-shadow) !important;
}

.animated-flow-line {
  animation: flow-dash 12s linear infinite;
  stroke-dasharray: 6 4;
}

@keyframes flow-dash {
  to {
    stroke-dashoffset: -40;
  }
}

.cta-outline-btn {
  border: 1.5px solid rgba(255, 255, 255, 0.2) !important;
}

.cta-outline-btn:hover {
  border-color: rgba(255, 255, 255, 0.55) !important;
}

@keyframes scroll-right {
  0% {
    transform: translateX(-50%);
  }
  100% {
    transform: translateX(0);
  }
}

@keyframes scroll-left {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}
</style>
