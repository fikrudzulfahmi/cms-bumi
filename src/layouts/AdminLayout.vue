<script setup>
import { ref, computed } from 'vue'
import { RouterLink, useRoute, RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LogoMark from '@/components/public/LogoMark.vue'
import AppIcon from '@/components/ui/AppIcon.vue'

const auth = useAuthStore()
const route = useRoute()
const open = ref(false)

const menus = [
  { label: 'Dashboard', to: '/admin', icon: 'dashboard' },
  { label: 'Pengaturan', to: '/admin/pengaturan', icon: 'settings' },
  { label: 'Profil Sekolah', to: '/admin/profil', icon: 'school' },
  { label: 'Guru & Karyawan', to: '/admin/guru', icon: 'users' },
  { label: 'Berita', to: '/admin/berita', icon: 'newspaper' },
  { label: 'Umpan Balik', to: '/admin/umpan-balik', icon: 'chat' },
  { label: 'Jurusan', to: '/admin/jurusan', icon: 'book' },
  { label: 'Fasilitas', to: '/admin/fasilitas', icon: 'landmark' },
  { label: 'Ekstrakurikuler', to: '/admin/ekstrakurikuler', icon: 'drama' },
  { label: 'Galeri', to: '/admin/galeri', icon: 'images' },
]

const pageTitle = computed(() => {
  const m = menus.find((m) => m.to === route.path)
  return m ? m.label : 'Dashboard'
})

async function logout() {
  await auth.logout()
}
</script>

<template>
  <div class="flex min-h-screen bg-brand-50/40">
    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-brand-950 transition-transform lg:static lg:translate-x-0"
      :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex items-center gap-3 px-5 py-5">
        <LogoMark :size="40" />
        <div class="leading-tight">
          <div class="text-sm font-extrabold text-white">Admin CMS</div>
          <div class="text-[11px] text-brand-300">MA Bustanul Muta'allimin</div>
        </div>
      </div>

      <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-3">
        <RouterLink
          v-for="m in menus"
          :key="m.to"
          :to="m.to"
          class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition-colors"
          :class="route.path === m.to ? 'bg-brand-600 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white'"
          @click="open = false"
        >
          <AppIcon :name="m.icon" :size="18" />{{ m.label }}
        </RouterLink>
      </nav>

      <div class="space-y-2 border-t border-white/10 p-4">
        <RouterLink to="/" target="_blank" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-brand-200 hover:bg-white/10 hover:text-white">
          <AppIcon name="globe" :size="18" /> Lihat Situs
        </RouterLink>
        <button @click="logout" class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-red-300 hover:bg-white/10">
          <AppIcon name="logout" :size="18" /> Keluar
        </button>
      </div>
    </aside>

    <!-- Overlay mobile -->
    <div v-if="open" class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="open = false"></div>

    <!-- Main -->
    <div class="flex min-w-0 flex-1 flex-col">
      <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-brand-100 bg-white/80 px-4 backdrop-blur sm:px-6">
        <div class="flex items-center gap-3">
          <button class="grid h-10 w-10 place-items-center rounded-xl text-brand-800 hover:bg-brand-50 lg:hidden" @click="open = true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
          </button>
          <h1 class="text-lg font-extrabold text-brand-950">{{ pageTitle }}</h1>
        </div>
        <div class="flex items-center gap-3">
          <div class="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-bold text-white">
            {{ (auth.user?.name || 'A')[0] }}
          </div>
          <div class="hidden leading-tight sm:block">
            <div class="text-sm font-bold text-brand-950">{{ auth.user?.name }}</div>
            <div class="text-xs text-gray-400">Administrator</div>
          </div>
        </div>
      </header>

      <main class="flex-1 p-4 sm:p-6">
        <RouterView />
      </main>
    </div>
  </div>
</template>
