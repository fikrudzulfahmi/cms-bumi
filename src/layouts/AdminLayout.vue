<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { RouterLink, useRoute, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LogoMark from '@/components/public/LogoMark.vue'
import AppIcon from '@/components/ui/AppIcon.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const open = ref(false) // sidebar mobile
const userMenu = ref(false) // dropdown akun

// adminOnly = hanya terlihat untuk peran 'admin'
const menus = [
  { label: 'Dashboard', to: '/admin', icon: 'dashboard', adminOnly: false },
  { label: 'Berita', to: '/admin/berita', icon: 'newspaper', adminOnly: false },
  { label: 'Pengaturan', to: '/admin/pengaturan', icon: 'settings', adminOnly: true },
  { label: 'Profil Sekolah', to: '/admin/profil', icon: 'school', adminOnly: true },
  { label: 'Guru & Karyawan', to: '/admin/guru', icon: 'users', adminOnly: true },
  { label: 'Umpan Balik', to: '/admin/umpan-balik', icon: 'chat', adminOnly: true },
  { label: 'Jurusan', to: '/admin/jurusan', icon: 'book', adminOnly: true },
  { label: 'Fasilitas', to: '/admin/fasilitas', icon: 'landmark', adminOnly: true },
  { label: 'Ekstrakurikuler', to: '/admin/ekstrakurikuler', icon: 'drama', adminOnly: true },
  { label: 'Galeri', to: '/admin/galeri', icon: 'images', adminOnly: true },
  { label: 'Akun Pengguna', to: '/admin/pengguna', icon: 'shieldUser', adminOnly: true },
  { label: 'Log Aktivitas', to: '/admin/log-aktivitas', icon: 'shield', adminOnly: true },
]

const visibleMenus = computed(() => menus.filter((m) => !m.adminOnly || auth.isAdmin))

const pageTitle = computed(() => {
  const m = menus.find((m) => m.to === route.path)
  return m ? m.label : 'Dashboard'
})

async function logout() {
  userMenu.value = false
  await auth.logout()
  router.push('/admin/login')
}

function goAccount() {
  userMenu.value = false
  router.push('/admin/akun')
}

function onDocClick(e) {
  if (!e.target.closest('[data-usermenu]')) userMenu.value = false
}

onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))
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
          v-for="m in visibleMenus"
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
            <AppIcon name="menu" :size="22" />
          </button>
          <h1 class="text-lg font-extrabold text-brand-950">{{ pageTitle }}</h1>
        </div>

        <!-- Menu akun -->
        <div class="relative" data-usermenu>
          <button
            class="flex items-center gap-2.5 rounded-xl p-1.5 pr-2 transition-colors hover:bg-brand-50"
            @click="userMenu = !userMenu"
          >
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-bold text-white">
              {{ (auth.user?.name || 'A')[0] }}
            </div>
            <div class="hidden text-left leading-tight sm:block">
              <div class="text-sm font-bold text-brand-950">{{ auth.user?.name }}</div>
              <div class="text-xs text-gray-400">{{ auth.isAdmin ? 'Administrator' : 'Penulis' }}</div>
            </div>
            <AppIcon name="chevronDown" :size="16" class="hidden text-gray-400 sm:block" />
          </button>

          <div
            v-if="userMenu"
            class="absolute right-0 z-30 mt-2 w-60 overflow-hidden rounded-2xl border border-gray-100 bg-white py-1.5 shadow-2xl"
          >
            <div class="border-b border-gray-100 px-4 py-2.5">
              <div class="truncate text-sm font-bold text-brand-950">{{ auth.user?.name }}</div>
              <div class="truncate text-xs text-gray-400">{{ auth.user?.email }}</div>
            </div>
            <button class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-brand-50" @click="goAccount">
              <AppIcon name="userCog" :size="16" /> Pengaturan Akun
            </button>
            <button class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50" @click="logout">
              <AppIcon name="logout" :size="16" /> Keluar
            </button>
          </div>
        </div>
      </header>

      <main class="flex-1 p-4 sm:p-6">
        <RouterView />
      </main>
    </div>
  </div>
</template>
