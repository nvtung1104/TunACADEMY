<template>
  <div class="level-cards">
    <div v-for="level in levels" :key="level.name" class="level-card" :style="{ borderColor: level.color }">
      <div class="level-card__icon">{{ level.icon }}</div>
      <div class="level-card__name" :style="{ color: level.color }">{{ level.name }}</div>
      <div class="level-card__range">{{ level.range }}</div>
      <div class="level-card__desc">{{ level.desc }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
const levels = [
  {
    name: 'Beginner',
    range: 'A1 – A2',
    icon: '🌱',
    color: '#22c55e',
    desc: 'Từ vựng cơ bản, câu ngắn, nghe chậm với phụ đề tiếng Việt.'
  },
  {
    name: 'Elementary',
    range: 'A2 – B1',
    icon: '🌿',
    color: '#84cc16',
    desc: 'Câu đơn giản, chủ đề quen thuộc, nghe có phụ đề tiếng Anh.'
  },
  {
    name: 'Intermediate',
    range: 'B1 – B2',
    icon: '🌳',
    color: '#f59e0b',
    desc: 'Hội thoại tự nhiên, nghe không phụ đề, đọc văn bản dài hơn.'
  },
  {
    name: 'Advanced',
    range: 'B2 – C1',
    icon: '🚀',
    color: '#6366f1',
    desc: 'Nội dung học thuật, phim/podcast gốc, viết và nói lưu loát.'
  }
]
</script>
