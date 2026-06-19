<template>
  <div class="relative shrink-0" :style="containerStyle">
    <!-- Avatar: 82% of container, centered — ring có chỗ hiện xung quanh -->
    <div
      class="absolute rounded-full overflow-hidden bg-indigo-50 flex items-center justify-center transition-all duration-300"
      :style="avatarStyle"
    >
      <img v-if="src" :src="src" :alt="name" class="w-full h-full object-cover" />
      <span v-else class="font-bold text-indigo-600 uppercase select-none" :style="initialsStyle">
        {{ name?.[0] ?? 'U' }}
      </span>
    </div>

    <!-- Gender frame: phủ toàn container, ring bao quanh avatar -->
    <img
      v-if="frameUrl"
      :src="frameUrl"
      alt=""
      class="absolute inset-0 w-full h-full pointer-events-none select-none transition-all duration-300"
      :style="frameStyle"
      draggable="false"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  src:     { type: String, default: null },
  name:    { type: String, default: '' },
  gender:  { type: String, default: '' },
  frame:   { type: String, default: null },
  size:    { type: Number, default: 40 },
  hasRing: { type: Boolean, default: true },
})

// Avatar occupies 82% of container when frame is present (to allow space for borders/ornaments), otherwise 100%
const frameUrl = computed(() => {
  // explicit frame prop takes priority over gender-derived frame
  const f = (props.frame !== null && props.frame !== undefined && props.frame !== '')
    ? props.frame
    : (props.gender === 'male' ? 'male' : props.gender === 'female' ? 'female' : null)
  if (!f || f === 'none') return null
  
  // Sử dụng đường dẫn đầy đủ với HTTPS để tránh Mixed Content
  const baseUrl = window.location.origin || 'https://tunacademy.site'
  if (f === 'male')   return `${baseUrl}/storage/decorate/male.svg`
  if (f === 'female') return `${baseUrl}/storage/decorate/female.svg`
  return null
})

const ratio = computed(() => frameUrl.value ? 0.82 : 1.0)

const containerStyle = computed(() => ({
  width:  `${props.size}px`,
  height: `${props.size}px`,
}))

const ringWidth = computed(() => {
  if (!props.hasRing) return 0
  if (props.size >= 80) return 3
  if (props.size >= 48) return 2
  return 1
})

const shadowStyle = computed(() => {
  if (!props.hasRing) return 'none'
  if (props.size >= 80) {
    return `0 0 0 ${ringWidth.value}px #ffffff, 0 6px 16px rgba(0, 0, 0, 0.08), 0 3px 6px rgba(0, 0, 0, 0.04)`
  }
  if (props.size >= 48) {
    return `0 0 0 ${ringWidth.value}px #ffffff, 0 3px 8px rgba(0, 0, 0, 0.06)`
  }
  return `0 0 0 ${ringWidth.value}px #ffffff, 0 1px 3px rgba(0, 0, 0, 0.05)`
})

const avatarStyle = computed(() => {
  const inner = Math.round(props.size * ratio.value)
  const offset = Math.round((props.size - inner) / 2)
  return {
    width:     `${inner}px`,
    height:    `${inner}px`,
    top:       `${offset}px`,
    left:      `${offset}px`,
    boxShadow: shadowStyle.value,
  }
})

const frameStyle = computed(() => {
  if (!frameUrl.value) return {}
  return {
    filter: 'drop-shadow(0 3px 6px rgba(0, 0, 0, 0.12))',
  }
})

const initialsStyle = computed(() => ({
  fontSize: `${Math.round(props.size * ratio.value * 0.4)}px`,
}))
</script>

