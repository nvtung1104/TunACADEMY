import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])
  let nextId = 0

  function add(type, message, duration = 4000) {
    const id = ++nextId
    toasts.value.push({ id, type, message })
    if (duration > 0) setTimeout(() => remove(id), duration)
  }

  function remove(id) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const success = (message, duration) => add('success', message, duration)
  const error   = (message, duration) => add('error',   message, duration)
  const warning = (message, duration) => add('warning', message, duration)
  const info    = (message, duration) => add('info',    message, duration)

  return { toasts, remove, success, error, warning, info }
})
