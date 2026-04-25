<template>
  <div class="space-y-4">
    <p class="text-sm text-gray-500">Các lớp học bạn đang tham gia</p>

    <div v-if="loading" class="py-16 text-center text-gray-400">
      <div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>
      Đang tải...
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-if="classrooms.length === 0" class="col-span-full py-16 text-center text-gray-400">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Bạn chưa tham gia lớp học nào
      </div>

      <div v-for="c in classrooms" :key="c.id"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all cursor-pointer"
        @click="openClass(c)">
        <!-- Header strip -->
        <div class="h-2 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
        <div class="p-5">
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-semibold text-gray-900">{{ c.name }}</h3>
              <p class="text-xs text-gray-400 mt-0.5">{{ c.school_year?.name }} · Khối {{ c.grade?.level }}</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-medium">
              Đang học
            </span>
          </div>

          <!-- Teachers -->
          <div v-if="c.teachers?.length" class="mb-3">
            <p class="text-xs text-gray-500 mb-1">Giáo viên phụ trách</p>
            <div class="flex flex-wrap gap-1">
              <span v-for="t in c.teachers" :key="t.id"
                class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                {{ t.name }}
              </span>
            </div>
          </div>

          <!-- Stats -->
          <div class="flex items-center gap-4 text-xs text-gray-500">
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
              {{ c.students_count ?? 0 }} học sinh
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              {{ c.lessons_count ?? 0 }} bài học
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Class detail modal -->
    <AppModal v-model="detailModal" :title="selectedClass?.name ?? ''" size="lg">
      <div v-if="loadingDetail" class="py-8 text-center text-gray-400">
        <div class="animate-spin w-5 h-5 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto mb-2"></div>
        Đang tải...
      </div>
      <div v-else class="space-y-5">
        <!-- Info -->
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-500 text-xs mb-0.5">Năm học</p>
            <p class="font-medium text-gray-800">{{ selectedClass?.school_year?.name ?? '—' }}</p>
          </div>
          <div>
            <p class="text-gray-500 text-xs mb-0.5">Khối</p>
            <p class="font-medium text-gray-800">{{ selectedClass?.grade?.level ? `Khối ${selectedClass.grade.level}` : '—' }}</p>
          </div>
        </div>

        <!-- Lessons -->
        <div>
          <h4 class="font-semibold text-gray-800 mb-3">Bài học ({{ classLessons.length }})</h4>
          <div class="space-y-2">
            <div v-if="classLessons.length === 0" class="text-sm text-gray-400">Chưa có bài học</div>
            <div v-for="l in classLessons" :key="l.id"
              class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-indigo-50 transition-colors">
              <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ l.title }}</p>
                <p class="text-xs text-gray-400">{{ l.subject?.name }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@api/axios'
import AppModal from '@components/common/AppModal.vue'

const classrooms = ref([])
const loading = ref(true)
const detailModal = ref(false)
const loadingDetail = ref(false)
const selectedClass = ref(null)
const classLessons = ref([])

async function openClass(c) {
  selectedClass.value = c
  detailModal.value = true
  loadingDetail.value = true
  classLessons.value = []
  try {
    const { data } = await api.get(`/student/classrooms/${c.id}`)
    selectedClass.value = data.data ?? c
    classLessons.value = data.data?.lessons ?? []
  } finally { loadingDetail.value = false }
}

onMounted(async () => {
  try {
    const { data } = await api.get('/student/classrooms')
    classrooms.value = data.data ?? []
  } finally { loading.value = false }
})
</script>
