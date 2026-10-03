<script setup>
import { ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useCmsStore } from '@/stores/cms'
import LogoMark from './LogoMark.vue'

const cms = useCmsStore()
const route = useRoute()
const open = ref(false)

const links = [
  { label: 'Beranda', to: '/' },
  { label: 'Profil', to: '/profil' },
  { label: 'Berita', to: '/berita' },
  { label: 'Jurusan', to: '/jurusan' },
  { label: 'Layanan', to: '/layanan' },
]

function isActive(to) {
  if (to === '/') return route.path === '/'
  return route.path.startsWith(to)
}
</script>

<template>
  <header class="sticky top-0 z-50">
    <div class="glass border-b border-brand-100/60">
      <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <RouterLink to="/" class="flex items-center gap-3">
          <LogoMark :size="40" />
          <div class="leading-tight">
            <div class="text-[13px] font-extrabold tracking-tight text-brand-900 sm:text-sm">
              {{ cms.namaSekolah || "MA Bustanul Muta'allimin" }}
            </div>
            <div class="hidden text-[10px] font-semibold text-gold-600 sm:block">Madrasah Hebat · Generasi Bermartabat</div>
          </div>
        </RouterLink>

        <!-- Desktop nav -->
        <div class="hidden items-center gap-1 lg:flex">
          <RouterLink
            v-for="l in links"
            :key="l.to"
            :to="l.to"
            class="rounded-xl px-4 py-2 text-sm font-semibold transition-colors"
            :class="isActive(l.to)
              ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25'
              : 'text-brand-900/80 hover:bg-brand-50 hover:text-brand-700'"
          >
            {{ l.label }}
          </RouterLink>
        </div>

        <div class="flex items-center gap-2">
          <a
            href="#ppdb"
            class="hidden rounded-xl bg-gradient-to-r from-gold-500 to-gold-400 px-4 py-2 text-sm font-bold text-white shadow-md shadow-gold-500/30 transition-transform hover:scale-[1.03] sm:inline-flex"
          >
            Info PPDB
          </a>
          <button
            class="grid h-10 w-10 place-items-center rounded-xl text-brand-800 hover:bg-brand-50 lg:hidden"
            @click="open = !open"
            aria-label="Menu"
          >
            <svg v-if="!open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
            </svg>
            <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
            </svg>
          </button>
        </div>
      </nav>

      <!-- Mobile menu -->
      <transition name="fade">
        <div v-if="open" class="border-t border-brand-100/60 px-4 pb-4 pt-2 lg:hidden">
          <RouterLink
            v-for="l in links"
            :key="l.to"
            :to="l.to"
            class="block rounded-xl px-4 py-2.5 text-sm font-semibold"
            :class="isActive(l.to) ? 'bg-brand-600 text-white' : 'text-brand-900/80 hover:bg-brand-50'"
            @click="open = false"
          >
            {{ l.label }}
          </RouterLink>
          <a href="#ppdb" @click="open = false"
            class="mt-2 block rounded-xl bg-gradient-to-r from-gold-500 to-gold-400 px-4 py-2.5 text-center text-sm font-bold text-white">
            Info PPDB
          </a>
        </div>
      </transition>
    </div>
  </header>
</template>
