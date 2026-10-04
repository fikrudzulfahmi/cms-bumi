<script setup>
import { computed, onMounted } from 'vue'
import { assetUrl } from '@/services/api'
import { useCmsStore } from '@/stores/cms'

defineProps({
  size: { type: Number, default: 44 },
  withText: { type: Boolean, default: false },
  dark: { type: Boolean, default: false },
})

const cms = useCmsStore()

const logoUrl = computed(() => (cms.settings && cms.settings.logo ? assetUrl(cms.settings.logo) : ''))

onMounted(() => {
  // Panel admin tidak memuat store; pastikan logo ikut terambil.
  if (!cms.loaded) cms.load().catch(() => {})
})
</script>

<template>
  <div class="flex items-center gap-3">
    <!-- Logo hasil upload (Pengaturan → Logo) -->
    <img
      v-if="logoUrl"
      :src="logoUrl"
      alt="Logo"
      class="shrink-0 rounded-2xl object-contain"
      :style="{ width: size + 'px', height: size + 'px' }"
    />

    <!-- Logo bawaan (bila belum ada upload) -->
    <div
      v-else
      class="grid shrink-0 place-items-center rounded-2xl shadow-lg"
      :style="{ width: size + 'px', height: size + 'px' }"
      :class="dark ? 'shadow-black/30' : 'shadow-brand-900/20'"
    >
      <svg viewBox="0 0 64 64" :width="size" :height="size">
        <defs>
          <linearGradient id="lg1" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#17663a" />
            <stop offset="1" stop-color="#2f9e5c" />
          </linearGradient>
        </defs>
        <rect x="4" y="4" width="56" height="56" rx="14" fill="url(#lg1)" />
        <path d="M32 12 L48 22 L32 32 L16 22 Z" fill="#f2dd95" />
        <path d="M20 26 v12 c0 4 5 6 12 6 s12 -2 12 -6 v-12" fill="none" stroke="#fdf9ec" stroke-width="3" stroke-linecap="round" />
        <rect x="30" y="44" width="4" height="8" rx="1.5" fill="#fdf9ec" />
      </svg>
    </div>

    <div v-if="withText" class="leading-tight">
      <div class="text-sm font-extrabold tracking-tight" :class="dark ? 'text-white' : 'text-brand-900'">
        MA Bustanul Muta'allimin
      </div>
      <div class="text-[11px] font-semibold text-gold-600">Madrasah Hebat · Generasi Bermartabat</div>
    </div>
  </div>
</template>
