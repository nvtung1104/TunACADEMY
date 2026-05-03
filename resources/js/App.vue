<template>
  <div v-if="!ready" class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="w-8 h-8 rounded-full border-2 border-[#d63015] border-t-transparent animate-spin"/>
  </div>
  <RouterView v-else />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@stores/auth'

const auth = useAuthStore()
// If there is no token, no need to wait for a user fetch — show the page immediately
const ready = ref(!auth.token)

onMounted(async () => {
  if (auth.token) {
    await auth.fetchUser().catch(() => auth.logout())
  }
  ready.value = true
})
</script>
