<template>
  <div class="relative shrink-0" :style="containerStyle">
    <!-- Avatar: 82% of container, centered — ring có chỗ hiện xung quanh -->
    <div
      class="absolute rounded-full overflow-hidden bg-indigo-100 flex items-center justify-center"
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
      class="absolute inset-0 w-full h-full pointer-events-none select-none"
      draggable="false"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  src:    { type: String, default: null },
  name:   { type: String, default: '' },
  gender: { type: String, default: '' },
  frame:  { type: String, default: null },
  size:   { type: Number, default: 40 },
})

// Avatar chiếm 82% container → phần còn lại (18%) dành cho khung viền
const RATIO = 0.82

const containerStyle = computed(() => ({
  width:  `${props.size}px`,
  height: `${props.size}px`,
}))

const avatarStyle = computed(() => {
  const inner = Math.round(props.size * RATIO)
  const offset = Math.round((props.size - inner) / 2)
  return {
    width:  `${inner}px`,
    height: `${inner}px`,
    top:    `${offset}px`,
    left:   `${offset}px`,
  }
})

const initialsStyle = computed(() => ({
  fontSize: `${Math.round(props.size * RATIO * 0.4)}px`,
}))

const frameUrl = computed(() => {
  // explicit frame prop takes priority over gender-derived frame
  const f = (props.frame !== null && props.frame !== undefined && props.frame !== '')
    ? props.frame
    : (props.gender === 'male' ? 'male' : props.gender === 'female' ? 'female' : null)
  if (!f || f === 'none') return null
  if (f === 'male')   return '/storage/decorate/male.svg'
  if (f === 'female') return '/storage/decorate/female.svg'
  return null
})
</script>
