<template>
  <div>
    <!-- Header + filters -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
      <div class="flex gap-2 flex-wrap">
        <button v-for="f in filters" :key="f.value"
          @click="activeFilter = f.value"
          class="px-4 py-2 rounded-xl text-sm font-medium border transition-all"
          :class="activeFilter === f.value
            ? 'bg-[#d63015] border-[#d63015] text-white shadow-sm'
            : 'bg-white border-gray-200 text-gray-600 hover:border-[#d63015] hover:text-[#d63015]'">
          <span class="mr-1.5">{{ f.icon }}</span>{{ f.label }}
          <span v-if="counts[f.value] !== undefined" class="ml-1.5 text-xs opacity-70">({{ counts[f.value] }})</span>
        </button>
      </div>

      <div class="sm:ml-auto relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" type="text" placeholder="Tìm trong mục đã lưu..."
          class="pl-9 pr-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#d63015]/30 focus:border-[#d63015] w-64"/>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-gray-100 p-5 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-1/3 mb-3"/>
        <div class="h-5 bg-gray-200 rounded w-3/4 mb-2"/>
        <div class="h-4 bg-gray-200 rounded w-1/2"/>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!filtered.length" class="text-center py-20">
      <div class="text-5xl mb-4">🔖</div>
      <h3 class="text-lg font-semibold text-gray-700 mb-2">
        {{ search ? 'Không tìm thấy kết quả' : 'Chưa có mục nào được lưu' }}
      </h3>
      <p class="text-gray-400 text-sm">
        {{ search ? 'Thử tìm từ khóa khác.' : 'Bấm nút lưu trên các bài học, đề thi, bài tập để lưu lại.' }}
      </p>
    </div>

    <!-- Grid -->
    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="item in filtered" :key="item.bookmark_id"
        class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all overflow-hidden">

        <!-- Top accent -->
        <div class="h-1.5 w-full" :style="{ backgroundColor: item.subject?.color || typeColor(item.type) }"/>

        <div class="p-5">
          <!-- Type badge + unsave button -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold"
                :style="{ backgroundColor: typeColor(item.type) + '18', color: typeColor(item.type) }">
                {{ typeIcon(item.type) }} {{ typeLabel(item.type) }}
              </span>
              <span v-if="item.subject?.name" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold"
                :style="{ backgroundColor: (item.subject.color || '#6366f1') + '18', color: item.subject.color || '#6366f1' }">
                {{ item.subject.name }}
              </span>
            </div>
            <button @click.stop="unsave(item)"
              class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0 ml-2"
              title="Bỏ lưu">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17 3H7a2 2 0 00-2 2v16l7-3 7 3V5a2 2 0 00-2-2z"/>
              </svg>
            </button>
          </div>

          <!-- Title -->
          <h3 class="font-semibold text-gray-900 text-sm line-clamp-2 mb-2 group-hover:text-indigo-700 transition-colors">
            {{ item.title }}
          </h3>

          <p v-if="item.description" class="text-xs text-gray-400 line-clamp-2 mb-3">
            {{ item.description }}
          </p>

          <!-- Footer -->
          <div class="flex items-center justify-between pt-3 border-t border-gray-50">
            <span class="text-xs text-gray-400">
              {{ item.classroom?.grade?.name ?? '' }}
              <span v-if="item.classroom?.name"> · {{ item.classroom.name }}</span>
            </span>
            <RouterLink :to="detailPath(item)"
              class="flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
              Xem
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import bookmarkApi from '@api/bookmarks'

const loading = ref(true)
const items = ref([])
const activeFilter = ref('all')
const search = ref('')

const filters = [
  { value: 'all',        label: 'Tất cả',   icon: '🔖' },
  { value: 'lesson',     label: 'Bài học',  icon: '🎬' },
  { value: 'exam',       label: 'Đề thi',   icon: '📝' },
  { value: 'assignment', label: 'Bài tập',  icon: '✏️' },
]

const counts = computed(() => {
  const map = { all: items.value.length }
  filters.slice(1).forEach(f => {
    map[f.value] = items.value.filter(i => i.type === f.value).length
  })
  return map
})

const filtered = computed(() => {
  let list = activeFilter.value === 'all'
    ? items.value
    : items.value.filter(i => i.type === activeFilter.value)
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(i => i.title?.toLowerCase().includes(q) || i.description?.toLowerCase().includes(q))
  }
  return list
})

function typeColor(type) {
  return { lesson: '#6366f1', exam: '#10b981', assignment: '#f59e0b' }[type] ?? '#6366f1'
}
function typeLabel(type) {
  return { lesson: 'Bài học', exam: 'Đề thi', assignment: 'Bài tập' }[type] ?? type
}
function typeIcon(type) {
  return { lesson: '🎬', exam: '📝', assignment: '✏️' }[type] ?? '📌'
}
function detailPath(item) {
  if (item.type === 'lesson')     return `/lessons/${item.id}`
  if (item.type === 'exam')       return `/exams/${item.id}`
  if (item.type === 'assignment') return `/practice/${item.id}`
  return '/'
}

async function unsave(item) {
  await bookmarkApi.toggle(item.type, item.id)
  items.value = items.value.filter(i => i.bookmark_id !== item.bookmark_id)
}

onMounted(async () => {
  try {
    const { data } = await bookmarkApi.list()
    items.value = data.data
  } catch {
    items.value = []
  } finally {
    loading.value = false
  }
})
</script>
