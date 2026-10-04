<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import AppIcon from '@/components/ui/AppIcon.vue'

const auth = useAuthStore()
const counts = ref({})

// adminOnly = hanya tampil untuk peran 'admin'
const allCards = [
  { key: 'berita', label: 'Berita & Postingan', icon: 'newspaper', to: '/admin/berita', color: 'from-gold-400 to-gold-600', adminOnly: false },
  { key: 'guru-karyawan', label: 'Guru & Karyawan', icon: 'users', to: '/admin/guru', color: 'from-brand-500 to-brand-700', adminOnly: true },
  { key: 'jurusan', label: 'Jurusan', icon: 'book', to: '/admin/jurusan', color: 'from-emerald-400 to-emerald-600', adminOnly: true },
  { key: 'fasilitas', label: 'Fasilitas', icon: 'landmark', to: '/admin/fasilitas', color: 'from-sky-400 to-sky-600', adminOnly: true },
  { key: 'ekstrakurikuler', label: 'Ekstrakurikuler', icon: 'drama', to: '/admin/ekstrakurikuler', color: 'from-violet-400 to-violet-600', adminOnly: true },
  { key: 'galeri', label: 'Galeri', icon: 'images', to: '/admin/galeri', color: 'from-rose-400 to-rose-600', adminOnly: true },
  { key: 'umpan-balik', label: 'Umpan Balik', icon: 'chat', to: '/admin/umpan-balik', color: 'from-amber-400 to-amber-600', adminOnly: true },
]

const cards = computed(() => allCards.filter((c) => !c.adminOnly || auth.isAdmin))

onMounted(async () => {
  const entries = await Promise.all(
    cards.value.map(async (c) => {
      try {
        const res = await api(`/admin/${c.key}`)
        return [c.key, res.data?.length || 0]
      } catch {
        return [c.key, null]
      }
    })
  )
  counts.value = Object.fromEntries(entries)
})
</script>

<template>
  <div>
    <div class="mb-6 rounded-3xl bg-gradient-to-r from-brand-600 to-brand-800 p-6 text-white shadow-lg">
      <h2 class="flex items-center gap-2 text-2xl font-extrabold">
        Selamat datang di Panel Admin <AppIcon name="hand" :size="24" />
      </h2>
      <p class="mt-1 text-brand-100/80">
        {{ auth.isAdmin ? 'Kelola seluruh konten website madrasah dari satu tempat.' : 'Kelola berita dan pengumuman madrasah.' }}
      </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <RouterLink v-for="c in cards" :key="c.key" :to="c.to"
        class="group rounded-3xl bg-white p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
        <div class="mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br text-white" :class="c.color">
          <AppIcon :name="c.icon" :size="24" />
        </div>
        <div class="text-3xl font-extrabold text-brand-950">{{ counts[c.key] ?? '—' }}</div>
        <div class="text-sm font-semibold text-gray-500">{{ c.label }}</div>
      </RouterLink>
    </div>
  </div>
</template>
