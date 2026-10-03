<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LogoMark from '@/components/public/LogoMark.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push(route.query.redirect || '/admin')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-brand-950 px-4">
    <div class="bg-grid absolute inset-0 opacity-15"></div>
    <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-gold-400/20 blur-3xl"></div>

    <div class="relative w-full max-w-md animate-fade-up">
      <div class="rounded-[2rem] bg-white p-8 shadow-2xl">
        <div class="mb-6 flex flex-col items-center text-center">
          <LogoMark :size="56" />
          <h1 class="mt-4 text-2xl font-extrabold text-brand-950">Panel Admin</h1>
          <p class="text-sm text-gray-500">Kelola konten website madrasah</p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
          <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Email</label>
            <input v-model="email" type="email" required placeholder="admin@…"
              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100" />
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Password</label>
            <input v-model="password" type="password" required placeholder="••••••••"
              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100" />
          </div>

          <p v-if="error" class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600">{{ error }}</p>

          <button :disabled="loading"
            class="w-full rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 py-3 font-bold text-white shadow-lg shadow-brand-600/30 transition-transform hover:scale-[1.01] disabled:opacity-50">
            {{ loading ? 'Memproses…' : 'Masuk' }}
          </button>
        </form>

        <RouterLink to="/" class="mt-5 block text-center text-sm font-semibold text-gray-400 hover:text-brand-600">← Kembali ke beranda</RouterLink>
      </div>
    </div>
  </div>
</template>
