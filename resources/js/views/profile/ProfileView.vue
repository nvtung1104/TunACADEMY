<template>
  <div class="max-w-4xl mx-auto">
    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-xl font-bold text-gray-900">Tài khoản</h1>
      <p class="text-sm text-gray-500 mt-0.5">Quản lý thông tin cá nhân và bảo mật tài khoản</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <!-- Tabs -->
      <div class="flex border-b border-gray-100">
        <button
          v-for="tab in tabs" :key="tab.key"
          @click="activeTab = tab.key"
          class="flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition-colors"
          :class="activeTab === tab.key
            ? 'border-indigo-600 text-indigo-600'
            : 'border-transparent text-gray-500 hover:text-gray-700'"
        >
          <span v-html="tab.icon" />
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab: Thông tin cá nhân -->
      <div v-if="activeTab === 'profile'" class="p-8">
        <!-- Avatar + Frame row -->
        <div class="flex flex-col items-center gap-6 mb-8">
          <!-- Avatar preview with current selected frame -->
          <div class="relative">
            <div class="ring-4 ring-white shadow-md rounded-full">
              <AvatarFrame
                :src="avatarPreview"
                :name="auth.user?.name"
                :gender="auth.user?.gender"
                :frame="selectedFrame"
                :size="96"
              />
            </div>
            <button @click="$refs.avatarInput.click()"
              :disabled="avatarUploading"
              class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shadow-md transition-colors disabled:opacity-60">
              <svg v-if="avatarUploading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </button>
            <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="handleAvatarChange" />
          </div>

          <!-- Frame picker -->
          <div class="flex flex-col items-center gap-3">
            <div class="flex items-center gap-1.5">
              <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span class="text-sm font-semibold text-gray-700">Chọn khung viền</span>
              <Transition name="fade">
                <span v-if="frameSaved" class="flex items-center gap-1 text-xs text-green-600 font-medium">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                  </svg>
                  Đã lưu
                </span>
              </Transition>
            </div>

            <div class="flex items-end gap-4">
              <button
                v-for="opt in frameOptions"
                :key="opt.value"
                @click="handleFrameSelect(opt.value)"
                :disabled="frameLoading"
                class="flex flex-col items-center gap-2 group disabled:opacity-60"
              >
                <!-- Preview circle -->
                <div
                  class="rounded-full transition-all duration-200"
                  :class="selectedFrame === opt.value
                    ? 'ring-2 ring-offset-2 ring-indigo-500 shadow-md'
                    : 'ring-1 ring-gray-200 hover:ring-indigo-300'"
                >
                  <AvatarFrame
                    :src="avatarPreview"
                    :name="auth.user?.name"
                    :frame="opt.value"
                    :size="56"
                  />
                </div>
                <!-- Label -->
                <span
                  class="text-xs font-medium transition-colors"
                  :class="selectedFrame === opt.value ? 'text-indigo-600' : 'text-gray-500 group-hover:text-gray-700'"
                >
                  {{ opt.label }}
                </span>
                <!-- Selected dot -->
                <div
                  class="w-1.5 h-1.5 rounded-full transition-all"
                  :class="selectedFrame === opt.value ? 'bg-indigo-500' : 'bg-transparent'"
                />
              </button>
            </div>
          </div>
        </div>

        <!-- Success/error alert -->
        <Transition name="fade">
          <div v-if="profileAlert.show" class="mb-6 flex items-center gap-2.5 p-4 rounded-xl text-sm"
            :class="profileAlert.success ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="profileAlert.success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            {{ profileAlert.message }}
          </div>
        </Transition>

        <!-- Form grid -->
        <form @submit.prevent="handleUpdateProfile">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
            <!-- Họ và tên -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Họ và tên <span class="text-red-500">*</span>
              </label>
              <input v-model="profileForm.name" type="text" required
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                placeholder="Nhập họ và tên" />
            </div>

            <!-- Email (readonly) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
              <input :value="auth.user?.email" type="email" readonly
                class="w-full px-4 py-2.5 rounded-xl border border-gray-100 bg-gray-50 text-gray-500 text-sm cursor-not-allowed" />
            </div>

            <!-- Số điện thoại -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Số điện thoại</label>
              <input v-model="profileForm.phone" type="tel"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                placeholder="Nhập số điện thoại" />
            </div>

            <!-- Ngày sinh -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Ngày sinh</label>
              <input v-model="profileForm.date_of_birth" type="date"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all" />
            </div>

            <!-- Giới tính -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Giới tính</label>
              <select v-model="profileForm.gender"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm bg-white transition-all">
                <option value="">Chọn giới tính</option>
                <option value="male">Nam</option>
                <option value="female">Nữ</option>
                <option value="other">Khác</option>
              </select>
            </div>

            <!-- Bằng cấp (chỉ hiện với teacher) -->
            <div v-if="auth.isTeacher">
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Bằng cấp / Chuyên môn</label>
              <input v-model="profileForm.qualification" type="text"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                placeholder="VD: Cử nhân Sư phạm Toán" />
            </div>

            <!-- Địa chỉ (full width) -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Địa chỉ</label>
              <textarea v-model="profileForm.address" rows="2"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all resize-none"
                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố" />
            </div>

            <!-- Thông tin phụ huynh (chỉ học sinh) -->
            <template v-if="auth.isStudent">
              <div class="md:col-span-2 pt-2 border-t border-gray-100">
                <p class="text-sm font-semibold text-gray-700">Thông tin phụ huynh</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Họ tên phụ huynh</label>
                <input v-model="profileForm.parent_name" type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                  placeholder="Nhập họ tên phụ huynh" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">SĐT phụ huynh</label>
                <input v-model="profileForm.parent_phone" type="tel"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                  placeholder="Nhập số điện thoại phụ huynh" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Địa chỉ phụ huynh</label>
                <textarea v-model="profileForm.parent_address" rows="2"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all resize-none"
                  placeholder="Địa chỉ của phụ huynh" />
              </div>
            </template>
          </div>

          <div class="flex justify-end mt-8 pt-6 border-t border-gray-100">
            <button type="submit" :disabled="profileLoading"
              class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
              <svg v-if="profileLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ profileLoading ? 'Đang lưu...' : 'Cập nhật' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Tab: Đổi mật khẩu -->
      <div v-if="activeTab === 'password'" class="p-8">
        <div class="max-w-md mx-auto">
          <!-- Alert -->
          <Transition name="fade">
            <div v-if="pwAlert.show" class="mb-6 flex items-center gap-2.5 p-4 rounded-xl text-sm"
              :class="pwAlert.success ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'">
              <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path v-if="pwAlert.success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              {{ pwAlert.message }}
            </div>
          </Transition>

          <form @submit.prevent="handleChangePassword" class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Mật khẩu hiện tại <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input v-model="pwForm.current_password"
                  :type="showPw.current ? 'text' : 'password'"
                  required
                  class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                  placeholder="Nhập mật khẩu hiện tại" />
                <button type="button" @click="showPw.current = !showPw.current"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="showPw.current" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Mật khẩu mới <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input v-model="pwForm.password"
                  :type="showPw.new ? 'text' : 'password'"
                  required minlength="8"
                  class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                  placeholder="Tối thiểu 8 ký tự" />
                <button type="button" @click="showPw.new = !showPw.new"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="showPw.new" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>

              <!-- Strength indicator -->
              <div v-if="pwForm.password" class="mt-2 flex gap-1">
                <div v-for="i in 4" :key="i"
                  class="h-1 flex-1 rounded-full transition-colors"
                  :class="i <= pwStrength ? pwStrengthColor : 'bg-gray-200'" />
              </div>
              <p v-if="pwForm.password" class="text-xs mt-1" :class="pwStrengthTextColor">
                {{ pwStrengthLabel }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Xác nhận mật khẩu mới <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input v-model="pwForm.password_confirmation"
                  :type="showPw.confirm ? 'text' : 'password'"
                  required
                  class="w-full px-4 py-2.5 pr-11 rounded-xl border focus:outline-none focus:ring-2 focus:border-transparent text-sm transition-all"
                  :class="pwMismatch ? 'border-red-300 focus:ring-red-400' : 'border-gray-200 focus:ring-indigo-500'"
                  placeholder="Nhập lại mật khẩu mới" />
                <button type="button" @click="showPw.confirm = !showPw.confirm"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="showPw.confirm" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              <p v-if="pwMismatch" class="text-xs text-red-500 mt-1">Mật khẩu xác nhận không khớp</p>
            </div>

            <button type="submit" :disabled="pwLoading || pwMismatch"
              class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition-colors disabled:opacity-60 disabled:cursor-not-allowed mt-2">
              <svg v-if="pwLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ pwLoading ? 'Đang lưu...' : 'Đổi mật khẩu' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@stores/auth'
import authApi from '@api/auth'
import AvatarFrame from '@components/common/AvatarFrame.vue'

const auth = useAuthStore()
const { user: authUser } = storeToRefs(auth)

const activeTab = ref('profile')
const tabs = [
  {
    key: 'profile',
    label: 'Thông tin cá nhân',
    icon: `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>`,
  },
  {
    key: 'password',
    label: 'Đổi mật khẩu',
    icon: `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>`,
  },
]

// ─── Avatar ───────────────────────────────────────────────────────────────────
const avatarPreview = ref(auth.user?.avatar ?? null)
const avatarUploading = ref(false)

async function handleAvatarChange(e) {
  const file = e.target.files?.[0]
  if (!file) return
  avatarPreview.value = URL.createObjectURL(file)
  avatarUploading.value = true
  try {
    const { data } = await authApi.updateAvatar(file)
    auth.user.avatar = data.data.avatar_url
  } catch {
    avatarPreview.value = auth.user?.avatar ?? null
  } finally {
    avatarUploading.value = false
    e.target.value = ''
  }
}

// ─── Frame picker ─────────────────────────────────────────────────────────────
const frameOptions = [
  { value: 'none',   label: 'Không viền' },
  { value: 'male',   label: 'Nam' },
  { value: 'female', label: 'Nữ' },
]

function initFrame() {
  const saved = auth.user?.frame
  if (saved) return saved
  if (auth.user?.gender === 'male')   return 'male'
  if (auth.user?.gender === 'female') return 'female'
  return 'none'
}

const selectedFrame = ref(initFrame())
const frameLoading = ref(false)
const frameSaved = ref(false)

async function handleFrameSelect(value) {
  if (selectedFrame.value === value || frameLoading.value) return
  selectedFrame.value = value
  frameLoading.value = true
  try {
    const { data } = await authApi.updateProfile({ frame: value })
    auth.user = { ...auth.user, ...data.data }
    frameSaved.value = true
    setTimeout(() => { frameSaved.value = false }, 2000)
  } catch {
    selectedFrame.value = initFrame()
  } finally {
    frameLoading.value = false
  }
}

// ─── Profile form ─────────────────────────────────────────────────────────────
const profileForm = reactive({
  name: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  address: '',
  qualification: '',
  parent_name: '',
  parent_phone: '',
  parent_address: '',
})

const profileLoading = ref(false)
const profileAlert = reactive({ show: false, success: false, message: '' })

function fillProfileForm() {
  const u = auth.user
  if (!u) return
  profileForm.name           = u.name ?? ''
  profileForm.phone          = u.phone ?? ''
  profileForm.date_of_birth  = u.date_of_birth ?? ''
  profileForm.gender         = u.gender ?? ''
  profileForm.address        = u.address ?? ''
  profileForm.qualification  = u.qualification ?? ''
  profileForm.parent_name    = u.parent_name ?? ''
  profileForm.parent_phone   = u.parent_phone ?? ''
  profileForm.parent_address = u.parent_address ?? ''
}

function showProfileAlert(success, message) {
  profileAlert.show = true
  profileAlert.success = success
  profileAlert.message = message
  setTimeout(() => { profileAlert.show = false }, 4000)
}

async function handleUpdateProfile() {
  profileLoading.value = true
  try {
    const { data } = await authApi.updateProfile({ ...profileForm })
    auth.user = { ...auth.user, ...data.data }
    showProfileAlert(true, 'Cập nhật thông tin thành công!')
  } catch (e) {
    showProfileAlert(false, e.response?.data?.message ?? 'Cập nhật thất bại, vui lòng thử lại.')
  } finally {
    profileLoading.value = false
  }
}

// ─── Change password ──────────────────────────────────────────────────────────
const pwForm = reactive({ current_password: '', password: '', password_confirmation: '' })
const pwLoading = ref(false)
const showPw = reactive({ current: false, new: false, confirm: false })
const pwAlert = reactive({ show: false, success: false, message: '' })

const pwMismatch = computed(() =>
  pwForm.password_confirmation.length > 0 && pwForm.password !== pwForm.password_confirmation
)

const pwStrength = computed(() => {
  const pw = pwForm.password
  if (!pw) return 0
  let score = 0
  if (pw.length >= 8) score++
  if (/[A-Z]/.test(pw)) score++
  if (/[0-9]/.test(pw)) score++
  if (/[^A-Za-z0-9]/.test(pw)) score++
  return score
})

const pwStrengthColor = computed(() => {
  return ['bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-green-500'][pwStrength.value - 1] ?? 'bg-gray-200'
})

const pwStrengthTextColor = computed(() => {
  return ['text-red-500', 'text-orange-500', 'text-yellow-600', 'text-green-600'][pwStrength.value - 1] ?? 'text-gray-400'
})

const pwStrengthLabel = computed(() => {
  return ['Yếu', 'Trung bình', 'Khá mạnh', 'Mạnh'][pwStrength.value - 1] ?? ''
})

function showPwAlert(success, message) {
  pwAlert.show = true
  pwAlert.success = success
  pwAlert.message = message
  setTimeout(() => { pwAlert.show = false }, 4000)
}

async function handleChangePassword() {
  if (pwMismatch.value) return
  pwLoading.value = true
  try {
    await authApi.changePassword({
      current_password: pwForm.current_password,
      password: pwForm.password,
      password_confirmation: pwForm.password_confirmation,
    })
    showPwAlert(true, 'Đổi mật khẩu thành công!')
    pwForm.current_password = ''
    pwForm.password = ''
    pwForm.password_confirmation = ''
  } catch (e) {
    showPwAlert(false, e.response?.data?.message ?? 'Đổi mật khẩu thất bại, vui lòng thử lại.')
  } finally {
    pwLoading.value = false
  }
}

watch(authUser, (user) => {
  if (!user) return
  avatarPreview.value = user.avatar ?? null
  selectedFrame.value = initFrame()
  fillProfileForm()
}, { immediate: true })

onMounted(async () => {
  if (!auth.user && auth.token) {
    await auth.fetchUser().catch(() => {})
  }
  fetchFrames()
})
</script>
