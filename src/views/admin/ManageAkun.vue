<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppIcon from '@/components/ui/AppIcon.vue'

const auth = useAuthStore()

const form = ref({
  name: auth.user?.name || '',
  email: auth.user?.email || '',
  current_password: '',
  password: '',
  password_confirmation: '',
})
const saving = ref(false)
const saved = ref(false)
const error = ref('')

async function save() {
  error.value = ''
  saved.value = false

  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    error.value = 'Konfirmasi password tidak sama.'
    return
  }

  const payload = { name: form.value.name, email: form.value.email }
  if (form.value.password) {
    payload.current_password = form.value.current_password
    payload.password = form.value.password
    payload.password_confirmation = form.value.password_confirmation
  }

  saving.value = true
  try {
    await auth.updateAccount(payload)
    form.value.current_password = ''
    form.value.password = ''
    form.value.password_confirmation = ''
    saved.value = true
    setTimeout(() => (saved.value = false), 3000)
  } catch (e) {
    error.value = e.message
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold text-brand-950">Pengaturan Akun</h1>
        <p class="text-sm text-gray-500">Ubah nama, email, dan password akunmu.</p>
      </div>
      <span
        class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-bold"
        :class="auth.isAdmin ? 'bg-brand-100 text-brand-700' : 'bg-gold-100 text-gold-700'"
      >
        <AppIcon :name="auth.isAdmin ? 'shieldUser' : 'newspaper'" :size="14" />
        {{ auth.isAdmin ? 'Administrator' : 'Penulis' }}
      </span>
    </div>

    <p v-if="saved" class="mb-4 flex items-center gap-2 rounded-xl bg-brand-100 px-4 py-2.5 text-sm font-bold text-brand-700">
      <AppIcon name="check" :size="16" /> Tersimpan!
    </p>
    <p v-if="error" class="mb-4 rounded-xl bg-red-50 px-4 py-2.5 text-sm font-bold text-red-600">{{ error }}</p>

    <form class="space-y-6" @submit.prevent="save">
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-extrabold text-brand-900">Data Akun</h2>
        <div class="grid gap-4">
          <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            />
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            />
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-1 font-extrabold text-brand-900">Ganti Password</h2>
        <p class="mb-4 text-xs text-gray-500">Kosongkan bila tidak ingin mengganti password.</p>
        <div class="grid gap-4">
          <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Password Lama</label>
            <input
              v-model="form.current_password"
              type="password"
              autocomplete="current-password"
              placeholder="••••••••"
              class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            />
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-700">Password Baru</label>
              <input
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                placeholder="min. 6 karakter"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
              />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-700">Ulangi Password Baru</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                placeholder="••••••••"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
              />
            </div>
          </div>
        </div>
      </div>

      <button
        type="submit"
        :disabled="saving"
        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 disabled:opacity-50"
      >
        <AppIcon name="check" :size="16" /> {{ saving ? 'Menyimpan…' : 'Simpan' }}
      </button>
    </form>
  </div>
</template>
