import { ref, shallowRef } from 'vue'

/**
 * Generic async state wrapper — eliminates the repetitive
 * loading/error/data trio from every view component.
 *
 * Usage:
 *   const { data, loading, error, execute } = useAsync(fetchFn)
 *   await execute(arg1, arg2)
 *
 * @param {Function} fn - Async function to wrap
 * @param {*} initialData - Initial value for data (default: null)
 */
export function useAsync(fn, initialData = null) {
  const data    = shallowRef(initialData)
  const loading = ref(false)
  const error   = ref(null)

  async function execute(...args) {
    loading.value = true
    error.value   = null
    try {
      const result = await fn(...args)
      // Support both raw data and axios response ({ data: ... })
      data.value = result?.data !== undefined ? result.data : result
      return data.value
    } catch (err) {
      error.value = err?.response?.data?.message ?? err?.message ?? 'Đã xảy ra lỗi'
      throw err
    } finally {
      loading.value = false
    }
  }

  function reset() {
    data.value    = initialData
    loading.value = false
    error.value   = null
  }

  return { data, loading, error, execute, reset }
}
