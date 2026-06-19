<template>
  <div class="question-type-selector">
    <h3 class="text-lg font-semibold mb-4">Chọn loại câu hỏi</h3>
    
    <!-- Trắc nghiệm -->
    <div class="category-section mb-6">
      <h4 class="category-title">📝 Trắc nghiệm</h4>
      <div class="type-grid">
        <button
          v-for="type in multipleChoiceTypes"
          :key="type.type"
          @click="selectType(type.type)"
          :class="['type-card', { active: modelValue === type.type }]"
        >
          <span class="type-icon">{{ type.icon }}</span>
          <span class="type-label">{{ type.label }}</span>
        </button>
      </div>
    </div>

    <!-- Tự luận -->
    <div class="category-section mb-6">
      <h4 class="category-title">✍️ Tự luận</h4>
      <div class="type-grid">
        <button
          v-for="type in essayTypes"
          :key="type.type"
          @click="selectType(type.type)"
          :class="['type-card', { active: modelValue === type.type }]"
        >
          <span class="type-icon">{{ type.icon }}</span>
          <span class="type-label">{{ type.label }}</span>
        </button>
      </div>
    </div>

    <!-- Tương tác -->
    <div class="category-section mb-6">
      <h4 class="category-title">🎮 Tương tác</h4>
      <div class="type-grid">
        <button
          v-for="type in interactiveTypes"
          :key="type.type"
          @click="selectType(type.type)"
          :class="['type-card', { active: modelValue === type.type }]"
        >
          <span class="type-icon">{{ type.icon }}</span>
          <span class="type-label">{{ type.label }}</span>
        </button>
      </div>
    </div>

    <!-- Kỹ năng ngôn ngữ -->
    <div class="category-section">
      <h4 class="category-title">🗣️ Kỹ năng ngôn ngữ</h4>
      <div class="type-grid">
        <button
          v-for="type in languageTypes"
          :key="type.type"
          @click="selectType(type.type)"
          :class="['type-card', { active: modelValue === type.type }]"
        >
          <span class="type-icon">{{ type.icon }}</span>
          <span class="type-label">{{ type.label }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { getTypesByCategory } from '@/utils/questionTypes'

const props = defineProps({
  modelValue: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue'])

const multipleChoiceTypes = computed(() => getTypesByCategory('multiple_choice'))
const essayTypes = computed(() => getTypesByCategory('essay'))
const interactiveTypes = computed(() => getTypesByCategory('interactive'))
const languageTypes = computed(() => getTypesByCategory('language'))

function selectType(type) {
  emit('update:modelValue', type)
}
</script>

<style scoped>
.question-type-selector {
  padding: 1.5rem;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.category-section {
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 1.5rem;
}

.category-section:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.category-title {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
}

.type-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 0.75rem;
}

.type-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
}

.type-card:hover {
  border-color: #3b82f6;
  background: #eff6ff;
  transform: translateY(-2px);
}

.type-card.active {
  border-color: #3b82f6;
  background: #dbeafe;
}

.type-icon {
  font-size: 2rem;
}

.type-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  text-align: center;
}
</style>
