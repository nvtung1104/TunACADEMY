import { ref, computed, onUnmounted } from 'vue'

/**
 * Manages the countdown timer for exam taking.
 * Extracted from ExamTakeView to keep timing logic isolated and reusable.
 *
 * @param {number} initialSeconds - Starting seconds (from expires_at diff or duration_minutes * 60)
 * @param {Function} onExpire - Callback fired when timer reaches zero
 */
export function useExamTimer(initialSeconds, onExpire) {
  const remaining = ref(Math.max(0, initialSeconds))
  let interval = null

  const minutes = computed(() => Math.floor(remaining.value / 60))
  const seconds = computed(() => remaining.value % 60)

  const formatted = computed(() => {
    const m = String(minutes.value).padStart(2, '0')
    const s = String(seconds.value).padStart(2, '0')
    return `${m}:${s}`
  })

  const isWarning = computed(() => remaining.value <= 300 && remaining.value > 60)  // last 5 min
  const isDanger  = computed(() => remaining.value <= 60)                           // last 1 min
  const isExpired = computed(() => remaining.value <= 0)

  function start() {
    if (interval) return
    interval = setInterval(() => {
      if (remaining.value <= 0) {
        stop()
        onExpire?.()
        return
      }
      remaining.value -= 1
    }, 1000)
  }

  function stop() {
    clearInterval(interval)
    interval = null
  }

  function reset(seconds) {
    stop()
    remaining.value = Math.max(0, seconds)
  }

  // Clean up on component unmount
  onUnmounted(stop)

  return {
    remaining,
    formatted,
    minutes,
    seconds,
    isWarning,
    isDanger,
    isExpired,
    start,
    stop,
    reset,
  }
}
