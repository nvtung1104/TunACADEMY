<template>
  <div class="max-w-md mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
      <h2 class="text-xl font-bold text-gray-900 mb-6">Đổi mật khẩu</h2>
      <form @submit.prevent="submit" class="space-y-4">
        <div v-for="f in fields" :key="f.key">
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ f.label }}</label>
          <input v-model="form[f.key]" type="password" :placeholder="f.placeholder"
            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" required />
        </div>
        <div v-if="error" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl">{{ error }}</div>
        <div v-if="success" class="text-sm text-green-600 bg-green-50 p-3 rounded-xl">{{ success }}</div>
        <button type="submit" :disabled="loading"
          class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm disabled:opacity-60">
          {{ loading ? 'Đang lưu...' : 'Cập nhật mật khẩu' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import api from '@api/axios'

const form = reactive({ current_password: '', password: '', password_confirmation: '' })
const fields = [
  { key: 'current_password', label: 'Mật khẩu hiện tại', placeholder: '••••••••' },
  { key: 'password', label: 'Mật khẩu mới', placeholder: '••••••••' },
  { key: 'password_confirmation', label: 'Xác nhận mật khẩu mới', placeholder: '••••••••' },
]
const loading = ref(false)
const error = ref('')
const success = ref('')

async function submit() {
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    await api.post('/auth/change-password', form)
    success.value = 'Đổi mật khẩu thành công!'
    Object.keys(form).forEach(k => form[k] = '')
  } catch (e) {
    error.value = e.response?.data?.message || 'Có lỗi xảy ra'
  } finally {
    loading.value = false
  }
}
</script>
