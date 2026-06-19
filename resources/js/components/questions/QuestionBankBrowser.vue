<template>
  <div class="question-bank-browser">
    <div class="header">
      <h3>Ngân Hàng Câu Hỏi</h3>
      <button @click="loadQuestions" class="btn-refresh">🔄 Tải lại</button>
    </div>

    <!-- Filters -->
    <div class="filters">
      <select v-model="filters.type" @change="loadQuestions">
        <option value="">Tất cả loại</option>
        <option value="multiple_choice">Chọn 1 đáp án</option>
        <option value="multiple_select">Chọn nhiều đáp án</option>
        <option value="true_false">Đúng / Sai</option>
        <option value="fill_blank">Điền vào chỗ trống</option>
        <option value="short_answer">Trả lời ngắn</option>
        <option value="essay">Tự luận</option>
        <option value="calculation">Tính toán</option>
        <option value="ordering">Sắp xếp thứ tự</option>
        <option value="matching">Nối cặp</option>
        <option value="drag_drop">Kéo thả</option>
        <option value="reading">Đọc hiểu</option>
        <option value="listening">Nghe hiểu</option>
        <option value="speaking">Nói</option>
        <option value="writing">Viết</option>
      </select>

      <select v-model="filters.difficulty" @change="loadQuestions">
        <option value="">Tất cả độ khó</option>
        <option value="easy">Dễ</option>
        <option value="medium">Trung bình</option>
        <option value="hard">Khó</option>
      </select>

      <input
        v-model="filters.search"
        @input="debounceSearch"
        type="text"
        placeholder="Tìm kiếm..."
        class="search-input"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading">Đang tải...</div>

    <!-- Questions List -->
    <div v-else class="questions-list">
      <div
        v-for="question in questions"
        :key="question.id"
        :class="['question-item', { selected: isSelected(question.id) }]"
        @click="toggleSelect(question.id)"
      >
        <div class="question-header">
          <input
            type="checkbox"
            :checked="isSelected(question.id)"
            @click.stop="toggleSelect(question.id)"
          />
          <span class="question-type">{{ getTypeLabel(question.type) }}</span>
          <span :class="['difficulty', question.difficulty]">
            {{ getDifficultyLabel(question.difficulty) }}
          </span>
          <span class="points">{{ question.default_points }} điểm</span>
        </div>
        <div class="question-content" v-html="renderMath(question.content)"></div>
      </div>

      <!-- Empty State -->
      <div v-if="questions.length === 0" class="empty-state">
        Không có câu hỏi nào
      </div>
    </div>

    <!-- Actions -->
    <div class="actions" v-if="selectedIds.length > 0">
      <button @click="importToExam" class="btn-primary" v-if="targetType === 'exam'">
        Thêm {{ selectedIds.length }} câu vào bài thi
      </button>
      <button @click="importToAssignment" class="btn-primary" v-if="targetType === 'assignment'">
        Thêm {{ selectedIds.length }} câu vào bài tập
      </button>
      <button @click="clearSelection" class="btn-secondary">
        Bỏ chọn
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import questionService from '@/services/questionService'
import { getQuestionType, getDifficulty } from '@/utils/questionTypes'
import { useToastStore } from '@stores/toast'
import { renderMath } from '@/utils/math'

const props = defineProps({
  targetType: {
    type: String,
    required: true,
    validator: (value) => ['exam', 'assignment'].includes(value),
  },
  targetId: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['imported'])

const toast = useToastStore()
const loading = ref(false)
const questions = ref([])
const selectedIds = ref([])
const filters = reactive({
  type: '',
  difficulty: '',
  search: '',
})

let searchTimeout = null

onMounted(() => {
  loadQuestions()
})

async function loadQuestions() {
  loading.value = true
  try {
    const response = await questionService.questionBank.getList(filters)
    questions.value = response.data.data.data || []
  } catch (error) {
    console.error('Error loading questions:', error)
    toast.error('Lỗi khi tải câu hỏi')
  } finally {
    loading.value = false
  }
}

function debounceSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadQuestions()
  }, 500)
}

function toggleSelect(id) {
  const index = selectedIds.value.indexOf(id)
  if (index > -1) {
    selectedIds.value.splice(index, 1)
  } else {
    selectedIds.value.push(id)
  }
}

function isSelected(id) {
  return selectedIds.value.includes(id)
}

function clearSelection() {
  selectedIds.value = []
}

async function importToExam() {
  if (selectedIds.value.length === 0) return

  loading.value = true
  try {
    await questionService.examQuestion.importFromBank(props.targetId, selectedIds.value)
    toast.success(`Đã thêm ${selectedIds.value.length} câu hỏi vào bài thi`)
    clearSelection()
    emit('imported')
  } catch (error) {
    console.error('Error importing to exam:', error)
    toast.error('Lỗi khi thêm câu hỏi vào bài thi')
  } finally {
    loading.value = false
  }
}

async function importToAssignment() {
  if (selectedIds.value.length === 0) return

  loading.value = true
  try {
    await questionService.assignmentQuestion.importFromBank(props.targetId, selectedIds.value)
    toast.success(`Đã thêm ${selectedIds.value.length} câu hỏi vào bài tập`)
    clearSelection()
    emit('imported')
  } catch (error) {
    console.error('Error importing to assignment:', error)
    toast.error('Lỗi khi thêm câu hỏi vào bài tập')
  } finally {
    loading.value = false
  }
}

function getTypeLabel(type) {
  return getQuestionType(type)?.label || type
}

function getDifficultyLabel(difficulty) {
  return getDifficulty(difficulty)?.label || difficulty
}
</script>

<style scoped>
.question-bank-browser {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.header h3 {
  font-size: 1.25rem;
  font-weight: 600;
}

.btn-refresh {
  padding: 0.5rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: white;
  cursor: pointer;
}

.btn-refresh:hover {
  background: #f9fafb;
}

.filters {
  display: grid;
  grid-template-columns: 1fr 1fr 2fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filters select,
.search-input {
  padding: 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}

.questions-list {
  max-height: 500px;
  overflow-y: auto;
  margin-bottom: 1rem;
}

.question-item {
  padding: 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 6px;
  margin-bottom: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.question-item:hover {
  border-color: #3b82f6;
  background: #eff6ff;
}

.question-item.selected {
  border-color: #3b82f6;
  background: #dbeafe;
}

.question-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.question-type {
  font-weight: 500;
  color: #374151;
}

.difficulty {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.difficulty.easy {
  background: #d1fae5;
  color: #065f46;
}

.difficulty.medium {
  background: #fef3c7;
  color: #92400e;
}

.difficulty.hard {
  background: #fee2e2;
  color: #991b1b;
}

.points {
  margin-left: auto;
  color: #6b7280;
  font-size: 0.875rem;
}

.question-content {
  color: #4b5563;
  font-size: 0.875rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #9ca3af;
}

.actions {
  display: flex;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.btn-primary {
  flex: 1;
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: white;
  color: #374151;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
}

.btn-secondary:hover {
  background: #f9fafb;
}
</style>
