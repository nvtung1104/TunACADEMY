import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useExamStore = defineStore('examSession', () => {
  const examId = ref(null)
  const currentAttemptId = ref(null)
  const answers = ref({})
  const startedAt = ref(null)
  const durationMinutes = ref(null)

  // Deeply watch answers and auto-save the session on any changes
  watch(answers, () => {
    saveSession()
  }, { deep: true })

  /**
   * Initializes or resumes an exam session.
   *
   * @param {string|number} id - Exam ID
   * @param {string|number} attemptId - Attempt ID (or 'public')
   * @param {number} durMin - Duration in minutes
   * @param {Array} initialQuestions - Array of questions to initialize defaults
   * @returns {boolean} True if a session was successfully restored, false otherwise
   */
  function initSession(id, attemptId, durMin, initialQuestions = []) {
    examId.value = id
    currentAttemptId.value = attemptId
    durationMinutes.value = durMin

    // Try to restore an existing session from sessionStorage
    const saved = sessionStorage.getItem(`exam_session_${id}`)
    if (saved) {
      try {
        const parsed = JSON.parse(saved)
        // Resume only if it is the same attempt/mode
        if (parsed.currentAttemptId === attemptId) {
          answers.value = parsed.answers || {}
          startedAt.value = parsed.startedAt
          return true // Successfully restored
        }
      } catch (e) {
        console.error('Failed to parse saved exam session', e)
      }
    }

    // Initialize a new session
    startedAt.value = Date.now()
    answers.value = {}

    // Set default values for specific question types (e.g. ordering questions)
    initialQuestions.forEach(q => {
      if (q.type === 'ordering' && Array.isArray(q.options)) {
        answers.value[q.id] = q.options.map((_, i) => String(i))
      }
    })

    saveSession()
    return false // Fresh session started
  }

  /**
   * Serializes the current store state to sessionStorage.
   */
  function saveSession() {
    if (!examId.value) return
    sessionStorage.setItem(`exam_session_${examId.value}`, JSON.stringify({
      examId: examId.value,
      currentAttemptId: currentAttemptId.value,
      answers: answers.value,
      startedAt: startedAt.value,
      durationMinutes: durationMinutes.value
    }))
  }

  /**
   * Wipes the store state and deletes the corresponding sessionStorage entry.
   */
  function clearSession() {
    if (examId.value) {
      sessionStorage.removeItem(`exam_session_${examId.value}`)
    }
    examId.value = null
    currentAttemptId.value = null
    answers.value = {}
    startedAt.value = null
    durationMinutes.value = null
  }

  return {
    examId,
    currentAttemptId,
    answers,
    startedAt,
    durationMinutes,
    initSession,
    saveSession,
    clearSession
  }
})
