import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'TunAcademy Docs',
  description: 'Hướng dẫn sử dụng nền tảng học tập K-12 TunAcademy',
  lang: 'vi-VN',
  outDir: '../public/guides',
  base: '/guides/',
  themeConfig: {
    nav: [
      { text: 'Quay lại trang chủ ↗', link: '/' }
    ],
    sidebar: [
      {
        text: 'Bắt đầu',
        items: [
          { text: 'Giới thiệu TunAcademy', link: '/general-introduction' },
          { text: 'Đăng ký / Đăng nhập',   link: '/register-login' },
          { text: 'Quản lý tài khoản',      link: '/account-management' }
        ]
      },
      {
        text: 'Dành cho Học sinh',
        items: [
          { text: 'Tổng quan',             link: '/student-guide' },
          { text: 'Xem bài học',           link: '/lessons-guide' },
          { text: 'Làm bài tập',           link: '/assignment-guide' },
          { text: 'Thi trực tuyến',        link: '/exam-guide' },
          { text: 'Học trực tuyến (Live)', link: '/live-guide' }
        ]
      },
      {
        text: 'Dành cho Giáo viên',
        items: [
          { text: 'Tổng quan',             link: '/teacher-guide' },
          { text: 'Quản lý bài học',       link: '/manage-lessons' },
          { text: 'Tạo đề thi & bài tập', link: '/manage-exams' },
          { text: 'Mở phòng trực tuyến',   link: '/manage-live' }
        ]
      },
      {
        text: 'Dành cho Quản trị viên',
        items: [
          { text: 'Tổng quan',             link: '/admin-guide' },
          { text: 'Quản lý người dùng',    link: '/manage-users' },
          { text: 'Quản lý lớp học',       link: '/manage-classrooms' }
        ]
      }
    ],
    search: {
      provider: 'local',
      options: {
        locales: {
          root: {
            translations: {
              button: {
                buttonText: 'Tìm kiếm',
                buttonAriaLabel: 'Tìm kiếm tài liệu'
              },
              modal: {
                noResultsText: 'Không tìm thấy kết quả cho',
                resetButtonTitle: 'Xóa từ khóa',
                footer: {
                  selectText: 'chọn',
                  navigateText: 'điều hướng',
                  closeText: 'đóng'
                }
              }
            }
          }
        }
      }
    },
    outline: {
      label: 'Mục lục',
      level: [2, 3]
    },
    docFooter: {
      prev: 'Trang trước',
      next: 'Trang tiếp'
    },
    lastUpdated: {
      text: 'Cập nhật lần cuối'
    }
  }
})
