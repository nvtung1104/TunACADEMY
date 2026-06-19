<template>
  <!-- Listening audio URL -->
  <div v-if="type === 'listening'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <label class="block text-sm font-semibold text-gray-800 mb-1.5">URL âm thanh <span class="text-red-500">*</span></label>
    <input v-model="audio" class="input" placeholder="https://..." />
    <p class="text-xs text-gray-400 mt-1">Link file MP3/WAV hoặc embed từ dịch vụ lưu trữ</p>
  </div>

  <!-- MC / multiple_select / reading / listening: options -->
  <div v-if="['multiple_choice','multiple_select','reading','listening'].includes(type)"
    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
    <div>
      <label class="block text-sm font-semibold text-gray-800 mb-3">Các đáp án <span class="text-red-500">*</span></label>
      <div class="space-y-2.5">
        <div v-for="(opt, idx) in options" :key="idx" class="flex items-center gap-3">
          <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 text-sm font-bold flex items-center justify-center shrink-0">{{ opt.id }}</span>
          <input v-model="opt.text" class="input flex-1" :placeholder="`Nhập đáp án ${opt.id}...`" />
          <button v-if="options.length > 2" type="button" @click="options.splice(idx, 1)"
            class="p-2 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <button v-if="options.length < 8" type="button" @click="addOption"
          class="text-sm text-[#d63015] hover:underline font-medium">+ Thêm đáp án</button>
      </div>
    </div>

    <div v-if="['multiple_choice','reading','listening'].includes(type)" class="pt-3 border-t border-gray-100">
      <label class="block text-sm font-semibold text-gray-800 mb-1.5">Đáp án đúng <span class="text-red-500">*</span></label>
      <select v-model="correctAnswer" class="input">
        <option value="">— Chọn đáp án đúng —</option>
        <option v-for="opt in options" :key="opt.id" :value="opt.id">{{ opt.id }}: {{ opt.text }}</option>
      </select>
    </div>

    <div v-else class="pt-3 border-t border-gray-100">
      <label class="block text-sm font-semibold text-gray-800 mb-2">
        Đáp án đúng <span class="text-red-500">*</span>
        <span class="text-xs font-normal text-gray-400 ml-1">(chọn tất cả đáp án đúng)</span>
      </label>
      <div class="flex flex-wrap gap-3">
        <label v-for="opt in options" :key="opt.id"
          class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border transition-colors"
          :class="selected.includes(opt.id) ? 'border-[#d63015] bg-red-50' : 'border-gray-200 bg-white'">
          <input type="checkbox" :value="opt.id" v-model="selected" class="rounded text-[#d63015]" />
          <span class="text-sm font-medium text-gray-700">{{ opt.id }}: {{ opt.text }}</span>
        </label>
      </div>
    </div>
  </div>

  <!-- true_false -->
  <div v-else-if="type === 'true_false'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <label class="block text-sm font-semibold text-gray-800 mb-3">Đáp án đúng <span class="text-red-500">*</span></label>
    <div class="grid grid-cols-2 gap-3">
      <button type="button" @click="correctAnswer = 'T'"
        class="flex items-center justify-center gap-2 p-4 rounded-xl border-2 font-medium text-sm transition-all"
        :class="correctAnswer === 'T' ? 'border-green-400 bg-green-50 text-green-700' : 'border-gray-200 text-gray-600 hover:border-green-200'">
        <span class="w-8 h-8 rounded-full bg-green-500 text-white text-sm font-bold flex items-center justify-center">T</span>
        Đúng
      </button>
      <button type="button" @click="correctAnswer = 'F'"
        class="flex items-center justify-center gap-2 p-4 rounded-xl border-2 font-medium text-sm transition-all"
        :class="correctAnswer === 'F' ? 'border-red-400 bg-red-50 text-red-700' : 'border-gray-200 text-gray-600 hover:border-red-200'">
        <span class="w-8 h-8 rounded-full bg-red-500 text-white text-sm font-bold flex items-center justify-center">F</span>
        Sai
      </button>
    </div>
  </div>

  <!-- fill_blank -->
  <div v-else-if="type === 'fill_blank'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <label class="block text-sm font-semibold text-gray-800 mb-1">
      Đáp án cho từng chỗ trống <span class="text-red-500">*</span>
      <span class="text-xs font-normal text-gray-400 ml-1">({{ blankCount }} chỗ trống)</span>
    </label>
    <div v-if="blankCount === 0" class="mt-2 text-sm text-gray-400 italic">Thêm <code class="bg-gray-100 px-1 rounded">___</code> vào nội dung câu hỏi để tạo chỗ trống.</div>
    <div v-else class="mt-3 space-y-2.5">
      <div v-for="i in blankCount" :key="i" class="flex items-center gap-3">
        <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 text-xs font-bold flex items-center justify-center shrink-0">{{ i }}</span>
        <input v-model="fillBlank[i - 1]" class="input flex-1" :placeholder="`Đáp án chỗ trống ${i}`" />
      </div>
    </div>
  </div>

  <!-- short_answer -->
  <div v-else-if="type === 'short_answer'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Đáp án đúng <span class="text-red-500">*</span></label>
    <input v-model="correctAnswer" class="input" placeholder="Nhập câu trả lời ngắn đúng..." />
  </div>

  <!-- calculation -->
  <div v-else-if="type === 'calculation'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <label class="block text-sm font-semibold text-gray-800 mb-3">Đáp án số <span class="text-red-500">*</span></label>
    <div class="grid grid-cols-3 gap-3">
      <div>
        <label class="block text-xs text-gray-500 mb-1">Giá trị</label>
        <input v-model.number="calc.value" type="number" step="any" class="input" placeholder="78.5" />
      </div>
      <div>
        <label class="block text-xs text-gray-500 mb-1">Sai số ±</label>
        <input v-model.number="calc.tolerance" type="number" step="any" min="0" class="input" placeholder="0.5" />
      </div>
      <div>
        <label class="block text-xs text-gray-500 mb-1">Đơn vị</label>
        <input v-model="calc.unit" class="input" placeholder="cm²" />
      </div>
    </div>
  </div>

  <!-- ordering -->
  <div v-else-if="type === 'ordering'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <label class="block text-sm font-semibold text-gray-800 mb-1">Các mục cần sắp xếp <span class="text-red-500">*</span></label>
    <p class="text-xs text-gray-400 mb-3">Nhập theo thứ tự đúng — học sinh sẽ thấy bị xáo trộn ngẫu nhiên.</p>
    <div class="space-y-2.5">
      <div v-for="(item, idx) in ordering" :key="idx" class="flex items-center gap-3">
        <span class="w-8 h-8 rounded-full bg-pink-100 text-pink-700 text-sm font-bold flex items-center justify-center shrink-0">{{ idx + 1 }}</span>
        <input v-model="item.text" class="input flex-1" :placeholder="`Mục thứ ${idx + 1}...`" />
        <button v-if="ordering.length > 2" type="button" @click="ordering.splice(idx, 1)"
          class="p-2 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <button type="button" @click="ordering.push({ text: '' })" class="text-sm text-[#d63015] hover:underline font-medium">+ Thêm mục</button>
    </div>
  </div>

  <!-- matching -->
  <div v-else-if="type === 'matching'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <label class="block text-sm font-semibold text-gray-800 mb-1">Các cặp nối <span class="text-red-500">*</span></label>
    <p class="text-xs text-gray-400 mb-3">Mỗi hàng là một cặp đúng — học sinh thấy cột phải bị xáo trộn.</p>
    <div class="space-y-2.5">
      <div class="grid grid-cols-2 gap-3 px-9 text-xs font-semibold text-gray-400"><span>Cột trái</span><span>Cột phải</span></div>
      <div v-for="(pair, idx) in matching" :key="idx" class="flex items-center gap-2">
        <span class="w-7 h-7 rounded-full bg-pink-100 text-pink-700 text-xs font-bold flex items-center justify-center shrink-0">{{ idx + 1 }}</span>
        <input v-model="pair.left" class="input flex-1" placeholder="Mục trái..." />
        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        <input v-model="pair.right" class="input flex-1" placeholder="Mục phải..." />
        <button v-if="matching.length > 2" type="button" @click="matching.splice(idx, 1)"
          class="p-2 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <button type="button" @click="matching.push({ left: '', right: '' })" class="text-sm text-[#d63015] hover:underline font-medium">+ Thêm cặp</button>
    </div>
  </div>

  <!-- drag_drop -->
  <div v-else-if="type === 'drag_drop'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <label class="block text-sm font-semibold text-gray-800 mb-1">Vùng thả & mục <span class="text-red-500">*</span></label>
    <p class="text-xs text-gray-400 mb-3">Mỗi vùng có tên và các mục thuộc về nó — học sinh kéo mục vào vùng đúng.</p>
    <div class="space-y-3">
      <div v-for="(zone, zi) in dragDrop" :key="zi" class="p-3 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold text-gray-500 shrink-0">Vùng {{ zi + 1 }}</span>
          <input v-model="zone.label" class="input flex-1 text-sm" placeholder="Tên vùng / danh mục..." />
          <button v-if="dragDrop.length > 1" type="button" @click="dragDrop.splice(zi, 1)"
            class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div v-for="(item, ii) in zone.items" :key="ii" class="flex items-center gap-2 pl-4">
          <span class="w-5 h-5 rounded-full bg-pink-100 text-pink-600 text-xs font-bold flex items-center justify-center shrink-0">{{ ii + 1 }}</span>
          <input v-model="zone.items[ii]" class="input flex-1 text-sm" :placeholder="`Mục ${ii + 1}...`" />
          <button v-if="zone.items.length > 1" type="button" @click="zone.items.splice(ii, 1)"
            class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <button type="button" @click="zone.items.push('')" class="text-xs text-[#d63015] hover:underline font-medium pl-4">+ Thêm mục</button>
      </div>
      <button type="button" @click="dragDrop.push({ label: '', items: [''] })" class="text-sm text-[#d63015] hover:underline font-medium">+ Thêm vùng</button>
    </div>
  </div>

  <!-- essay / speaking / writing -->
  <div v-else-if="['essay','speaking','writing'].includes(type)"
    class="bg-amber-50 rounded-2xl border border-amber-100 p-4 text-sm text-amber-700">
    Loại câu hỏi này do giáo viên chấm thủ công — không cần nhập đáp án mẫu.
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'

const props = defineProps({
  type:    { type: String, required: true },
  content: { type: String, default: '' },
})

const correctAnswer = ref('')
const options       = ref([{ id: 'A', text: '' }, { id: 'B', text: '' }, { id: 'C', text: '' }, { id: 'D', text: '' }])
const selected      = ref([])
const calc          = reactive({ value: null, tolerance: 0, unit: '' })
const ordering      = ref([{ text: '' }, { text: '' }, { text: '' }])
const matching      = ref([{ left: '', right: '' }, { left: '', right: '' }])
const dragDrop      = ref([{ label: '', items: ['', ''] }, { label: '', items: ['', ''] }])
const audio         = ref('')
const fillBlank     = ref([''])

const blankCount = computed(() => (props.content.match(/___/g) || []).length)

watch(blankCount, (n) => {
  while (fillBlank.value.length < n) fillBlank.value.push('')
})

watch(() => props.type, () => {
  correctAnswer.value = ''
  selected.value      = []
  options.value       = [{ id: 'A', text: '' }, { id: 'B', text: '' }, { id: 'C', text: '' }, { id: 'D', text: '' }]
})

function addOption() {
  const ids = 'ABCDEFGH'
  const nextId = ids[options.value.length] ?? String(options.value.length + 1)
  options.value.push({ id: nextId, text: '' })
}

function loadFrom(q) {
  if (q.options) {
    if (q.type === 'ordering' && Array.isArray(q.options)) {
      ordering.value = q.options.map(t => ({ text: String(t) }))
    } else if (q.type === 'matching') {
      const left  = q.options?.left ?? []
      const right = q.options?.right ?? []
      matching.value = left.map((l, i) => ({ left: l, right: right[i] ?? '' }))
    } else if (q.type === 'drag_drop' && Array.isArray(q.options)) {
      dragDrop.value = q.options
    } else if (Array.isArray(q.options)) {
      options.value = q.options.map((o, i) => {
        if (typeof o === 'object' && o !== null) return { id: o.id ?? String.fromCharCode(65 + i), text: o.text ?? '' }
        return { id: String.fromCharCode(65 + i), text: String(o) }
      })
    }
  }
  if (q.type === 'true_false') {
    const val = Array.isArray(q.correct_answer) ? q.correct_answer[0] : q.correct_answer
    correctAnswer.value = String(val) === '0' ? 'T' : 'F'
  } else if (q.type === 'multiple_select' && Array.isArray(q.correct_answer)) {
    selected.value = q.correct_answer.map(v => {
      const n = Number(v)
      return Number.isNaN(n) ? String(v) : (options.value[n]?.id ?? String(v))
    })
  } else if (q.type === 'calculation' && q.correct_answer?.value !== undefined) {
    Object.assign(calc, q.correct_answer)
  } else if (q.type === 'fill_blank' && Array.isArray(q.correct_answer)) {
    fillBlank.value = q.correct_answer
  } else if (!['ordering', 'matching', 'drag_drop', 'essay', 'speaking', 'writing'].includes(q.type)) {
    const val = Array.isArray(q.correct_answer) ? q.correct_answer[0] : q.correct_answer
    const n   = Number(val)
    correctAnswer.value = Number.isNaN(n) ? (val ?? '') : (options.value[n]?.id ?? '')
  }
  if (q.type === 'listening' && q.audio_path) audio.value = q.audio_path
}

defineExpose({ correctAnswer, options, selected, calc, ordering, matching, dragDrop, audio, fillBlank, blankCount, loadFrom })
</script>

<style scoped>
@reference "tailwindcss";
.input { @apply w-full px-3 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#d63015] text-sm; }
</style>
