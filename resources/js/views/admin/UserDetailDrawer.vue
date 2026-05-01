<template>
  <Teleport to="body">
    <Transition name="drawer">
      <div v-if="modelValue" class="fixed inset-0 z-50 flex justify-end">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('update:modelValue', false)" />

        <!-- Panel -->
        <div class="panel relative w-full max-w-xl bg-white shadow-2xl flex flex-col overflow-hidden">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
            <h3 class="text-base font-semibold text-gray-900">Chi tiết người dùng</h3>
            <button @click="$emit('update:modelValue', false)"
              class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="flex-1 flex items-center justify-center">
            <div class="text-center text-gray-400">
              <div class="animate-spin w-8 h-8 border-2 border-[#d63015] border-t-transparent rounded-full mx-auto mb-3" />
              Đang tải...
            </div>
          </div>

          <!-- Content -->
          <div v-else-if="detail" class="flex-1 overflow-y-auto">
            <!-- Profile header -->
            <div class="px-6 py-5 bg-gradient-to-br from-red-50 to-orange-50 border-b border-gray-100">
              <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-[#d63015]/10 flex items-center justify-center text-2xl font-bold text-[#d63015] shrink-0 overflow-hidden">
                  <img v-if="detail.user.avatar" :src="detail.user.avatar" class="w-full h-full object-cover" />
                  <span v-else>{{ detail.user.name?.charAt(0)?.toUpperCase() }}</span>
                </div>
                <div class="min-w-0">
                  <p class="text-lg font-semibold text-gray-900 truncate">{{ detail.user.name }}</p>
                  <p class="text-sm text-gray-500 truncate">{{ detail.user.email }}</p>
                  <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="roleClass(role)">
                      {{ roleLabel(role) }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                      :class="detail.user.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                      {{ detail.user.status === 'active' ? 'Hoạt động' : 'Vô hiệu' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="px-6 py-5 space-y-6">
              <!-- Personal info -->
              <section>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Thông tin cá nhân</h4>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3">
                  <div v-if="detail.user.phone">
                    <dt class="text-xs text-gray-400">Số điện thoại</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ detail.user.phone }}</dd>
                  </div>
                  <div v-if="detail.user.date_of_birth">
                    <dt class="text-xs text-gray-400">Ngày sinh</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ formatDate(detail.user.date_of_birth) }}</dd>
                  </div>
                  <div v-if="detail.user.gender">
                    <dt class="text-xs text-gray-400">Giới tính</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ genderLabel(detail.user.gender) }}</dd>
                  </div>
                  <div v-if="detail.user.qualification">
                    <dt class="text-xs text-gray-400">Chuyên môn</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ detail.user.qualification }}</dd>
                  </div>
                  <div v-if="detail.user.address" class="col-span-2">
                    <dt class="text-xs text-gray-400">Địa chỉ</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ detail.user.address }}</dd>
                  </div>
                </dl>
              </section>

              <!-- STUDENT sections -->
              <template v-if="role === 'student'">
                <!-- Parent info -->
                <section v-if="detail.user.parent_name">
                  <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Thông tin phụ huynh</h4>
                  <dl class="grid grid-cols-2 gap-x-6 gap-y-3">
                    <div v-if="detail.user.parent_name" class="col-span-2">
                      <dt class="text-xs text-gray-400">Họ tên</dt>
                      <dd class="text-sm font-medium text-gray-800">{{ detail.user.parent_name }}</dd>
                    </div>
                    <div v-if="detail.user.parent_phone">
                      <dt class="text-xs text-gray-400">Số điện thoại</dt>
                      <dd class="text-sm font-medium text-gray-800">{{ detail.user.parent_phone }}</dd>
                    </div>
                    <div v-if="detail.user.parent_address">
                      <dt class="text-xs text-gray-400">Địa chỉ</dt>
                      <dd class="text-sm font-medium text-gray-800">{{ detail.user.parent_address }}</dd>
                    </div>
                  </dl>
                </section>

                <!-- Class -->
                <section v-if="detail.classrooms?.length">
                  <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Lớp học</h4>
                  <div class="space-y-2">
                    <div v-for="(c, i) in detail.classrooms" :key="i"
                      class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100">
                      <div>
                        <p class="text-sm font-semibold text-gray-800">{{ c.classroom }}</p>
                        <p class="text-xs text-gray-500">{{ c.grade }} · {{ c.school_year }}</p>
                        <p v-if="c.homeroom_teacher" class="text-xs text-gray-400 mt-0.5">GVCN: {{ c.homeroom_teacher }}</p>
                      </div>
                      <span class="text-xs px-2 py-1 rounded-full" :class="statusClass(c.status)">
                        {{ statusLabel(c.status) }}
                      </span>
                    </div>
                  </div>
                </section>

                <!-- Scores -->
                <section v-if="detail.scores?.length">
                  <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Bảng điểm</h4>
                  <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full text-sm">
                      <thead class="bg-gray-50">
                        <tr>
                          <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Môn học</th>
                          <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">Năm học</th>
                          <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">BT</th>
                          <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">Thi</th>
                          <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">Tổng</th>
                          <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">XL</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-50">
                        <tr v-for="(s, i) in detail.scores" :key="i" class="hover:bg-gray-50">
                          <td class="px-3 py-2 font-medium text-gray-800">{{ s.subject }}</td>
                          <td class="px-3 py-2 text-center text-gray-500 text-xs">{{ s.school_year }}</td>
                          <td class="px-3 py-2 text-center">{{ fmt(s.assignment_avg) }}</td>
                          <td class="px-3 py-2 text-center">{{ fmt(s.exam_avg) }}</td>
                          <td class="px-3 py-2 text-center font-semibold" :class="scoreColor(s.final_score)">{{ fmt(s.final_score) }}</td>
                          <td class="px-3 py-2 text-center">
                            <span v-if="s.classification" class="px-1.5 py-0.5 rounded text-xs font-medium" :class="classificationClass(s.classification)">
                              {{ s.classification }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </section>

                <p v-else-if="!detail.classrooms?.length && !detail.scores?.length"
                  class="text-sm text-gray-400 text-center py-4">Chưa có dữ liệu học tập</p>
              </template>

              <!-- TEACHER sections -->
              <template v-if="role === 'teacher'">
                <!-- Subjects -->
                <section v-if="detail.subjects?.length">
                  <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Môn giảng dạy</h4>
                  <div class="flex flex-wrap gap-2">
                    <div v-for="(s, i) in detail.subjects" :key="i"
                      class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-gray-100 bg-gray-50">
                      <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: s.color }" />
                      <span class="text-sm font-medium text-gray-800">{{ s.name }}</span>
                      <span class="text-xs text-gray-400">{{ s.code }}</span>
                      <span v-if="s.verified" class="text-green-500">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                      </span>
                    </div>
                  </div>
                </section>

                <!-- Homeroom -->
                <section v-if="detail.homeroom_classrooms?.length">
                  <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Lớp chủ nhiệm</h4>
                  <div class="space-y-2">
                    <div v-for="(c, i) in detail.homeroom_classrooms" :key="i"
                      class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100">
                      <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                      </svg>
                      <div>
                        <p class="text-sm font-semibold text-gray-800">{{ c.name }}</p>
                        <p class="text-xs text-gray-500">{{ c.grade }} · {{ c.school_year }}</p>
                      </div>
                    </div>
                  </div>
                </section>

                <!-- Teaching assignments -->
                <section v-if="detail.teaching_assignments?.length">
                  <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Phân công giảng dạy</h4>
                  <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full text-sm">
                      <thead class="bg-gray-50">
                        <tr>
                          <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Lớp</th>
                          <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Khối</th>
                          <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Môn</th>
                          <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Năm học</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-50">
                        <tr v-for="(a, i) in detail.teaching_assignments" :key="i" class="hover:bg-gray-50">
                          <td class="px-3 py-2 font-medium text-gray-800">{{ a.classroom }}</td>
                          <td class="px-3 py-2 text-gray-500 text-xs">{{ a.grade }}</td>
                          <td class="px-3 py-2 text-gray-700">{{ a.subject }}</td>
                          <td class="px-3 py-2 text-gray-500 text-xs">{{ a.school_year }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </section>
              </template>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import api from '@api/axios'

const props = defineProps({ modelValue: Boolean, userId: Number })
const emit = defineEmits(['update:modelValue'])

const loading = ref(false)
const detail = ref(null)
const role = ref(null)

watch([() => props.userId, () => props.modelValue], async ([id, isOpen]) => {
  if (!id || !isOpen) return
  loading.value = true
  detail.value = null
  try {
    const { data } = await api.get(`/admin/users/${id}`)
    const payload = data.data
    if (payload.user) {
      detail.value = payload
      role.value = payload.user.roles?.[0] ?? null
    } else {
      detail.value = { user: payload }
      role.value = payload.roles?.[0] ?? null
    }
  } finally {
    loading.value = false
  }
})

function formatDate(d) {
  if (!d) return ''
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}
function genderLabel(g) { return { male: 'Nam', female: 'Nữ', other: 'Khác' }[g] ?? g }
function roleLabel(r) { return { admin: 'Admin', teacher: 'Giáo viên', student: 'Học sinh' }[r] ?? r }
function roleClass(r) { return { admin: 'bg-violet-100 text-violet-700', teacher: 'bg-emerald-100 text-emerald-700', student: 'bg-blue-100 text-blue-700' }[r] ?? 'bg-gray-100 text-gray-600' }
function statusLabel(s) { return { active: 'Đang học', transferred: 'Chuyển trường', graduated: 'Tốt nghiệp' }[s] ?? s }
function statusClass(s) { return { active: 'bg-green-100 text-green-700', transferred: 'bg-amber-100 text-amber-700', graduated: 'bg-blue-100 text-blue-700' }[s] ?? 'bg-gray-100 text-gray-600' }
function fmt(v) { return v != null ? Number(v).toFixed(1) : '—' }
function scoreColor(v) {
  if (v == null) return 'text-gray-400'
  if (v >= 8) return 'text-green-600'
  if (v >= 6.5) return 'text-blue-600'
  if (v >= 5) return 'text-amber-600'
  return 'text-red-600'
}
function classificationClass(c) {
  return {
    'Xuất sắc': 'bg-purple-100 text-purple-700',
    'Giỏi': 'bg-green-100 text-green-700',
    'Khá': 'bg-blue-100 text-blue-700',
    'Trung bình': 'bg-amber-100 text-amber-700',
    'Yếu': 'bg-red-100 text-red-600',
  }[c] ?? 'bg-gray-100 text-gray-600'
}
</script>

<style scoped>
.drawer-enter-active { transition: opacity 0.2s ease; }
.drawer-leave-active { transition: opacity 0.2s ease; }
.drawer-enter-from, .drawer-leave-to { opacity: 0; }

.drawer-enter-active .panel { transition: transform 0.25s ease; }
.drawer-leave-active .panel { transition: transform 0.2s ease; }
.drawer-enter-from .panel { transform: translateX(100%); }
.drawer-leave-to .panel { transform: translateX(100%); }
</style>
