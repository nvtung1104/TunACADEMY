import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useConfirmStore = defineStore('confirm', () => {
  const visible = ref(false)
  const title   = ref('Xác nhận')
  const message = ref('')
  let resolver  = null

  function ask(msg, options = {}) {
    message.value = msg
    title.value   = options.title ?? 'Xác nhận'
    visible.value = true
    return new Promise(resolve => { resolver = resolve })
  }

  function accept() {
    visible.value = false
    resolver?.(true)
    resolver = null
  }

  function reject() {
    visible.value = false
    resolver?.(false)
    resolver = null
  }

  return { visible, title, message, ask, accept, reject }
})
