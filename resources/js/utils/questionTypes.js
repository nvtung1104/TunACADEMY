/**
 * Question Types Configuration - Simplified
 * Cấu hình các loại câu hỏi cho TunAcademy
 */

export const QUESTION_CATEGORIES = {
  multiple_choice: {
    label: 'Trắc nghiệm',
    icon: '📝',
    color: 'blue',
  },
  essay: {
    label: 'Tự luận',
    icon: '✍️',
    color: 'green',
  },
  interactive: {
    label: 'Tương tác',
    icon: '🎮',
    color: 'purple',
  },
  language: {
    label: 'Kỹ năng ngôn ngữ',
    icon: '�️',
    color: 'orange',
  },
}

export const QUESTION_TYPES = {
  // Trắc nghiệm
  multiple_choice: {
    label: 'Chọn 1 đáp án',
    description: 'Chọn 1 đáp án đúng từ nhiều lựa chọn',
    category: 'multiple_choice',
    icon: '⭕',
    autoGraded: true,
    hasOptions: true,
    hasSubQuestions: false,
  },
  multiple_select: {
    label: 'Chọn nhiều đáp án',
    description: 'Chọn tất cả đáp án đúng',
    category: 'multiple_choice',
    icon: '☑️',
    autoGraded: true,
    hasOptions: true,
    hasSubQuestions: false,
  },
  true_false: {
    label: 'Đúng / Sai',
    description: 'Chọn đúng hoặc sai',
    category: 'multiple_choice',
    icon: '✓✗',
    autoGraded: true,
    hasOptions: true,
    hasSubQuestions: false,
  },

  // Tự luận
  fill_blank: {
    label: 'Điền vào chỗ trống',
    description: 'Điền từ/cụm từ vào chỗ trống',
    category: 'essay',
    icon: '___',
    autoGraded: true,
    hasOptions: false,
    hasSubQuestions: false,
  },
  short_answer: {
    label: 'Trả lời ngắn',
    description: 'Trả lời ngắn gọn (1-2 câu)',
    category: 'essay',
    icon: '✍️',
    autoGraded: false,
    hasOptions: false,
    hasSubQuestions: false,
  },
  essay: {
    label: 'Tự luận',
    description: 'Viết bài luận dài',
    category: 'essay',
    icon: '📄',
    autoGraded: false,
    hasOptions: false,
    hasSubQuestions: false,
  },
  calculation: {
    label: 'Tính toán (có sai số)',
    description: 'Bài toán tính toán hỗ trợ sai số',
    category: 'essay',
    icon: '�',
    autoGraded: true,
    hasOptions: false,
    hasSubQuestions: false,
  },

  // Tương tác
  ordering: {
    label: 'Sắp xếp thứ tự',
    description: 'Sắp xếp các mục theo thứ tự đúng',
    category: 'interactive',
    icon: '🔢',
    autoGraded: true,
    hasOptions: true,
    hasSubQuestions: false,
  },
  matching: {
    label: 'Nối cặp',
    description: 'Nối các cặp tương ứng',
    category: 'interactive',
    icon: '🔗',
    autoGraded: true,
    hasOptions: true,
    hasSubQuestions: false,
  },
  drag_drop: {
    label: 'Kéo thả',
    description: 'Kéo thả vào nhóm đúng',
    category: 'interactive',
    icon: '🎯',
    autoGraded: true,
    hasOptions: true,
    hasSubQuestions: false,
  },

  // Kỹ năng ngôn ngữ
  reading: {
    label: 'Đọc hiểu',
    description: 'Đọc đoạn văn và trả lời câu hỏi',
    category: 'language',
    icon: '�',
    autoGraded: true,
    hasOptions: false,
    hasSubQuestions: true,
  },
  listening: {
    label: 'Nghe hiểu',
    description: 'Nghe audio và trả lời câu hỏi',
    category: 'language',
    icon: '🎧',
    autoGraded: true,
    hasOptions: false,
    hasSubQuestions: true,
    requiresAudio: true,
  },
  speaking: {
    label: 'Nói',
    description: 'Ghi âm câu trả lời',
    category: 'language',
    icon: '�',
    autoGraded: false,
    hasOptions: false,
    hasSubQuestions: false,
    requiresRecording: true,
  },
  writing: {
    label: 'Viết',
    description: 'Viết đoạn văn / bài luận',
    category: 'language',
    icon: '✏️',
    autoGraded: false,
    hasOptions: false,
    hasSubQuestions: false,
  },
}


export const DIFFICULTY_LEVELS = {
  easy: {
    label: 'Dễ',
    color: 'green',
    icon: '🟢',
  },
  medium: {
    label: 'Trung bình',
    color: 'yellow',
    icon: '🟡',
  },
  hard: {
    label: 'Khó',
    color: 'red',
    icon: '🔴',
  },
}

/**
 * Get question type config
 */
export function getQuestionType(type) {
  return QUESTION_TYPES[type] || null
}

/**
 * Get all types by category
 */
export function getTypesByCategory(category) {
  return Object.entries(QUESTION_TYPES)
    .filter(([_, config]) => config.category === category)
    .map(([type, config]) => ({ type, ...config }))
}

/**
 * Get all auto-graded types
 */
export function getAutoGradedTypes() {
  return Object.entries(QUESTION_TYPES)
    .filter(([_, config]) => config.autoGraded)
    .map(([type]) => type)
}

/**
 * Get all teacher-graded types
 */
export function getTeacherGradedTypes() {
  return Object.entries(QUESTION_TYPES)
    .filter(([_, config]) => !config.autoGraded)
    .map(([type]) => type)
}

/**
 * Check if type has sub-questions
 */
export function hasSubQuestions(type) {
  return QUESTION_TYPES[type]?.hasSubQuestions || false
}

/**
 * Check if type is auto-graded
 */
export function isAutoGraded(type) {
  return QUESTION_TYPES[type]?.autoGraded || false
}

/**
 * Get difficulty config
 */
export function getDifficulty(level) {
  return DIFFICULTY_LEVELS[level] || null
}

/**
 * Get category config
 */
export function getCategory(category) {
  return QUESTION_CATEGORIES[category] || null
}

/**
 * Format question for display
 */
export function formatQuestion(question) {
  const typeConfig = getQuestionType(question.type)
  const difficultyConfig = getDifficulty(question.difficulty)
  const categoryConfig = getCategory(typeConfig?.category)

  return {
    ...question,
    typeLabel: typeConfig?.label || question.type,
    typeIcon: typeConfig?.icon || '❓',
    difficultyLabel: difficultyConfig?.label || question.difficulty,
    difficultyColor: difficultyConfig?.color || 'gray',
    categoryLabel: categoryConfig?.label || 'Unknown',
    categoryIcon: categoryConfig?.icon || '📁',
    isAutoGraded: typeConfig?.autoGraded || false,
    hasSubQuestions: typeConfig?.hasSubQuestions || false,
  }
}

/**
 * Validate question data
 */
export function validateQuestion(question) {
  const errors = []

  if (!question.type) {
    errors.push('Vui lòng chọn loại câu hỏi')
  }

  if (!question.content || question.content.trim() === '') {
    errors.push('Vui lòng nhập nội dung câu hỏi')
  }

  if (!question.points || question.points <= 0) {
    errors.push('Điểm số phải lớn hơn 0')
  }

  const typeConfig = getQuestionType(question.type)

  if (typeConfig?.hasOptions && (!question.options || question.options.length === 0)) {
    errors.push('Vui lòng thêm các lựa chọn')
  }

  if (typeConfig?.autoGraded && !question.correct_answer) {
    errors.push('Vui lòng nhập đáp án đúng')
  }

  if (typeConfig?.hasSubQuestions && (!question.sub_questions || question.sub_questions.length === 0)) {
    errors.push('Vui lòng thêm câu hỏi con')
  }

  return errors
}

export default {
  QUESTION_CATEGORIES,
  QUESTION_TYPES,
  DIFFICULTY_LEVELS,
  getQuestionType,
  getTypesByCategory,
  getAutoGradedTypes,
  getTeacherGradedTypes,
  hasSubQuestions,
  isAutoGraded,
  getDifficulty,
  getCategory,
  formatQuestion,
  validateQuestion,
}
