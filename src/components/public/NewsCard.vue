<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { assetUrl } from '@/services/api'
import { formatDate, truncate } from '@/utils/format'
import AppIcon from '@/components/ui/AppIcon.vue'

const props = defineProps({
  post: { type: Object, required: true },
})

const kategoriLabel = computed(() => {
  switch (props.post.kategori) {
    case 'pengumuman': return 'Pengumuman'
    case 'prestasi': return 'Prestasi'
    default: return 'Berita'
  }
})

const kategoriColor = computed(() => {
  switch (props.post.kategori) {
    case 'pengumuman': return 'bg-amber-100 text-amber-700'
    case 'prestasi': return 'bg-gold-100 text-gold-700'
    default: return 'bg-brand-100 text-brand-700'
  }
})
</script>

<template>
  <RouterLink :to="`/berita/${post.slug}`" class="group block h-full overflow-hidden rounded-3xl glass-card transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl">
    <div class="relative h-44 overflow-hidden">
      <img
        v-if="post.gambar_url"
        :src="post.gambar_url"
        :alt="post.judul"
        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
        loading="lazy"
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
      </div>
      <h3 class="line-clamp-2 font-bold leading-snug text-brand-950 transition-colors group-hover:text-brand-600">
        {{ post.judul }}
      </h3>
      <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-gray-500">{{ truncate(post.ringkasan || post.konten, 140) }}</p>
      <span class="mt-3 inline-flex items-center gap-1 text-sm font-bold text-gold-600">
        Baca selengkapnya
        <AppIcon name="arrowRight" :size="16" class="transition-transform group-hover:translate-x-1" />
      </span>
    </div>
  </RouterLink>
</template>
