<template>
  <div class="roadmap">
    <div v-for="(phase, index) in phases" :key="phase.title" class="roadmap__phase">
      <div class="roadmap__dot">{{ index + 1 }}</div>
      <div class="roadmap__title">{{ phase.title }}</div>
      <ul class="roadmap__items">
        <li v-for="item in phase.items" :key="item">{{ item }}</li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
const phases = [
  {
    title: 'Giai đoạn 1 — Xây nền tảng (1–3 tháng)',
    items: [
      'Học bảng chữ cái phiên âm IPA cơ bản',
      'Nghe 15 phút/ngày với phụ đề tiếng Việt',
      'Học 5–10 từ vựng mới mỗi ngày qua flashcard',
      'Luyện đọc câu đơn giản, chủ đề gia đình / trường học'
    ]
  },
  {
    title: 'Giai đoạn 2 — Phát triển kỹ năng (3–6 tháng)',
    items: [
      'Chuyển sang phụ đề tiếng Anh khi nghe',
      'Luyện lồng tiếng các đoạn hội thoại ngắn',
      'Đọc bài đọc hiểu cấp độ Elementary',
      'Bắt đầu chơi game luyện từ vựng theo chủ đề'
    ]
  },
  {
    title: 'Giai đoạn 3 — Tăng tốc (6–12 tháng)',
    items: [
      'Nghe podcast và video không phụ đề',
      'Luyện lồng tiếng phim/series tiếng Anh',
      'Đọc truyện ngắn và sách dành cho người học',
      'Thực hành hội thoại qua tính năng voice acting'
    ]
  },
  {
    title: 'Giai đoạn 4 — Thành thạo (12+ tháng)',
    items: [
      'Xem phim gốc không phụ đề',
      'Đọc sách tiếng Anh nguyên bản',
      'Giao tiếp tự nhiên trong các tình huống thực tế',
      'Ôn thi chứng chỉ IELTS / TOEIC nếu cần'
    ]
  }
]
</script>
