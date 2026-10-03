<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '@/services/api'

const counts = ref({})

const cards = [
  { key: 'guru-karyawan', label: 'Guru & Karyawan', icon: '👥', to: '/admin/guru', color: 'from-brand-500 to-brand-700' },
  { key: 'berita', label: 'Berita & Postingan', icon: '📰', to: '/admin/berita', color: 'from-gold-400 to-gold-600' },
  { key: 'jurusan', label: 'Jurusan', icon: '📚', to: '/admin/jurusan', color: 'from-emerald-400 to-emerald-600' },
  { key: 'fasilitas', label: 'Fasilitas', icon: '🏛️', to: '/admin/fasilitas', color: 'from-sky-400 to-sky-600' },
  { key: 'ekstrakurikuler', label: 'Ekstrakurikuler', icon: '🎭', to: '/admin/ekstrakurikuler', color: 'from-violet-400 to-violet-600' },
  { key: 'galeri', label: 'Galeri', icon: '🖼️', to: '/admin/galeri', color: 'from-rose-400 to-rose-600' },
  { key: 'umpan-balik', label: 'Umpan Balik', icon: '💬', to: '/admin/umpan-balik', color: 'from-amber-400 to-amber-600' },
]

onMounted(async () => {
  const entries = await Promise.all(
    cards.map(async (c) => {
      const res = await api(`/admin/${c.key}`)
      return [c.key, res.data?.length || 0]
    })
  )
  counts.value = Object.fromEntries(entries)
})
</script>

<template>
  <div>
    <div class="mb-6 rounded-3xl bg-gradient-to-r from-brand-600 to-brand-800 p-6 text-white shadow-lg">
      <h2 class="text-2xl font-extrabold">Selamat datang di Panel Admin 👋</h2>
      <p class="mt-1 text-brand-100/80">Kelola seluruh konten website madrasah dari satu tempat.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <RouterLink v-for="c in cards" :key="c.key" :to="c.to"
        class="group rounded-3xl bg-white p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
        <div class="mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br text-2xl text-white" :class="c.color">{{ c.icon }}</div>
        <div class="text-3xl font-extrabold text-brand-950">{{ counts[c.key] ?? '—' }}</div>
        <div class="text-sm font-semibold text-gray-500">{{ c.label }}</div>
      </RouterLink>
    </div>
  </div>
</template>
