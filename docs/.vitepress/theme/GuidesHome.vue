<template>
  <div class="guides-home">
    <div class="guides-home__header">
      <h1 class="guides-home__title">
        <span class="guides-home__icon">📚</span>
        Hướng Dẫn Sử Dụng
      </h1>
      <p class="guides-home__subtitle">Chọn chủ đề bên dưới hoặc dùng thanh tìm kiếm để tra cứu nhanh.</p>
    </div>

    <div v-for="group in groups" :key="group.label" class="guides-home__section">
      <h2 class="guides-home__section-title">{{ group.label }}</h2>
      <div class="guides-home__grid">
        <a
          v-for="guide in group.items"
          :key="guide.link"
          :href="withBase(guide.link)"
          class="guide-card"
        >
          <div class="guide-card__header">
            <span class="guide-card__emoji">{{ guide.icon }}</span>
            <div class="guide-card__title-wrap">
              <span class="guide-card__title">{{ guide.title }}</span>
              <span
                v-if="guide.badge"
                class="guide-card__badge"
                :class="`guide-card__badge--${guide.badge.type}`"
              >{{ guide.badge.text }}</span>
            </div>
          </div>
          <p class="guide-card__description">{{ guide.description }}</p>
          <span class="guide-card__link">Đọc ngay →</span>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { withBase } from 'vitepress'

const groups = [
  {
    label: '🚀 Bắt đầu',
    items: [
      {
        title: 'Giới thiệu TunAcademy',
        link: '/general-introduction',
        icon: '🏫',
        badge: { text: 'Nên đọc trước', type: 'featured' },
        description: 'Tổng quan về nền tảng, các tính năng chính và hướng dẫn bắt đầu nhanh cho từng vai trò.'
      },
      {
        title: 'Đăng ký / Đăng nhập',
        link: '/register-login',
        icon: '🔑',
        badge: null,
        description: 'Hướng dẫn tạo tài khoản, đăng nhập và xử lý các lỗi thường gặp khi truy cập hệ thống.'
      },
      {
        title: 'Quản lý tài khoản',
        link: '/account-management',
        icon: '👤',
        badge: null,
        description: 'Cập nhật thông tin cá nhân, đổi mật khẩu, ảnh đại diện và các cài đặt bảo mật.'
      }
    ]
  },
  {
    label: '🎓 Dành cho Học sinh',
    items: [
      {
        title: 'Tổng quan cho Học sinh',
        link: '/student-guide',
        icon: '📖',
        badge: { text: 'Phổ biến', type: 'popular' },
        description: 'Làm quen với Dashboard học sinh — xem lịch học, điểm số và tiến độ học tập.'
      },
      {
        title: 'Xem bài học',
        link: '/lessons-guide',
        icon: '🎬',
        badge: null,
        description: 'Cách truy cập và theo dõi bài học theo từng môn, lớp học được phân công.'
      },
      {
        title: 'Làm bài tập',
        link: '/assignment-guide',
        icon: '✏️',
        badge: null,
        description: 'Hướng dẫn nộp bài tập, xem nhận xét từ giáo viên và kiểm tra hạn nộp.'
      },
      {
        title: 'Thi trực tuyến',
        link: '/exam-guide',
        icon: '📝',
        badge: null,
        description: 'Quy trình tham gia bài kiểm tra trực tuyến, xem kết quả và điểm số chi tiết.'
      },
      {
        title: 'Học trực tuyến (Live)',
        link: '/live-guide',
        icon: '📡',
        badge: { text: 'Mới', type: 'new' },
        description: 'Hướng dẫn tham gia phòng học trực tuyến, tương tác với giáo viên qua livestream.'
      }
    ]
  },
  {
    label: '👨‍🏫 Dành cho Giáo viên',
    items: [
      {
        title: 'Tổng quan cho Giáo viên',
        link: '/teacher-guide',
        icon: '🗂️',
        badge: null,
        description: 'Làm quen với Dashboard giáo viên — quản lý lớp, bài học, điểm số và lịch dạy.'
      },
      {
        title: 'Quản lý bài học',
        link: '/manage-lessons',
        icon: '📋',
        badge: null,
        description: 'Tạo, chỉnh sửa và phân công bài học cho từng lớp học do mình phụ trách.'
      },
      {
        title: 'Tạo đề thi & bài tập',
        link: '/manage-exams',
        icon: '📊',
        badge: null,
        description: 'Xây dựng đề thi, giao bài tập, chấm điểm và nhận xét kết quả học sinh.'
      },
      {
        title: 'Mở phòng trực tuyến',
        link: '/manage-live',
        icon: '🎙️',
        badge: { text: 'Mới', type: 'new' },
        description: 'Tạo và quản lý phòng học livestream, theo dõi danh sách học sinh tham gia.'
      }
    ]
  },
  {
    label: '⚙️ Dành cho Quản trị viên',
    items: [
      {
        title: 'Tổng quan cho Admin',
        link: '/admin-guide',
        icon: '🛡️',
        badge: null,
        description: 'Tổng quan hệ thống — thống kê người dùng, lớp học, bài học và hoạt động nền tảng.'
      },
      {
        title: 'Quản lý người dùng',
        link: '/manage-users',
        icon: '👥',
        badge: null,
        description: 'Thêm, sửa, phân quyền tài khoản học sinh và giáo viên trong hệ thống.'
      },
      {
        title: 'Quản lý lớp học',
        link: '/manage-classrooms',
        icon: '🏛️',
        badge: null,
        description: 'Tạo lớp học, phân công giáo viên chủ nhiệm, thêm học sinh và quản lý năm học.'
      }
    ]
  }
]
</script>
