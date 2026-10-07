<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { assetUrl } from '@/services/api'
import { formatDate, truncate } from '@/utils/format'
import { warnaKategori } from '@/utils/kategori'
import { useCmsStore } from '@/stores/cms'
import AppIcon from '@/components/ui/AppIcon.vue'
import BintangRating from './BintangRating.vue'
import TombolBagikan from './TombolBagikan.vue'

const props = defineProps({
  post: { type: Object, required: true },
})

const cms = useCmsStore()

const kategoriLabel = computed(() => cms.namaKategori(props.post.kategori))
const kategoriColor = computed(() => warnaKategori(props.post.kategori))

/** Alamat lengkap berita ini — dipakai tombol bagikan. */
const tautan = computed(() => {
  const dasar = typeof window !== 'undefined' ? window.location.origin : ''
  return `${dasar}/berita/${props.post.slug}`
})
</script>

<template>
  <div class="relative h-full">
    <!-- Tombol bagikan: di luar tautan kartu supaya klik tidak ikut membuka berita -->
    <div class="absolute right-3 top-3 z-10">
      <TombolBagikan ringkas :judul="post.judul" :url="tautan" terang />
    </div>

    <RouterLink :to="`/berita/${post.slug}`" class="group block h-full overflow-hidden rounded-3xl glass-card transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl">
      <div class="relative h-44 overflow-hidden">
        <img
          v-if="post.gambar_url"
          :src="post.gambar_url"
          :srcset="post.gambar_srcset || undefined"
          sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 400px"
          :alt="post.judul"
          class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
          loading="lazy"
          decoding="async"
        />
        <div v-else class="grid h-full w-full place-items-center bg-gradient-to-br from-brand-500 to-brand-700 text-white/70">
          <AppIcon name="newspaper" :size="40" />
        </div>
        <span class="absolute left-4 top-4 rounded-full px-3 py-1 text-xs font-bold shadow" :class="kategoriColor">
          {{ kategoriLabel }}
        </span>
      </div>
      <div class="p-5">
      <div class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-400">
        <AppIcon name="calendar" :size="14" />
        <span>{{ formatDate(post.tanggal) }}</span>
        <template v-if="post.author_name">
          <span class="text-gray-300">·</span>
          <AppIcon name="users" :size="14" />
          <span>{{ post.author_name }}</span>
        </template>
      </div>
      <h3 class="line-clamp-2 font-bold leading-snug text-brand-950 transition-colors group-hover:text-brand-600">
        {{ post.judul }}
      </h3>
      <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-gray-500">{{ truncate(post.ringkasan || post.konten, 140) }}</p>

      <div class="mt-3 flex items-center justify-between gap-2 border-t border-gray-100 pt-3">
        <BintangRating :nilai="post.rating" :ukuran="14" />
        <span class="flex items-center gap-1.5 text-xs font-semibold text-gray-400">
          <AppIcon name="eye" :size="14" /> {{ (post.views || 0).toLocaleString('id-ID') }}
          <span class="text-gray-300">·</span>
          <AppIcon name="heart" :size="14" /> {{ post.jumlah_like || 0 }}
        </span>
      </div>

      <span class="mt-3 inline-flex items-center gap-1 text-sm font-bold text-gold-600">
        Baca selengkapnya
        <AppIcon name="arrowRight" :size="16" class="transition-transform group-hover:translate-x-1" />
      </span>
      </div>
      </RouterLink>
      </div>
      </template>
