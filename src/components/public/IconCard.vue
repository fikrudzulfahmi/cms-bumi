<script setup>
import { assetUrl } from '@/services/api'
import { truncate } from '@/utils/format'
import AppIcon from '@/components/ui/AppIcon.vue'

defineProps({
  item: { type: Object, required: true },
  icon: { type: String, default: 'sparkles' },
})
</script>

<template>
  <div class="group relative h-full overflow-hidden rounded-3xl glass-card transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl">
    <div class="relative h-36 overflow-hidden">
      <img
        v-if="item.gambar_url"
        :src="item.gambar_url"
        :alt="item.nama"
        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
        loading="lazy"
        decoding="async"
      />
      <div v-else class="grid h-full w-full place-items-center bg-gradient-to-br from-brand-500 to-brand-700 text-white/80">
        <AppIcon :name="icon" :size="40" />
      </div>
      <div class="absolute inset-0 bg-gradient-to-t from-black/45 to-transparent"></div>
      <span class="absolute bottom-3 left-4 grid h-9 w-9 place-items-center rounded-xl bg-white/15 text-white shadow-lg backdrop-blur">
        <AppIcon :name="icon" :size="18" />
      </span>
    </div>
    <div class="p-4">
      <h3 class="font-bold text-brand-950">{{ item.nama }}</h3>
      <p v-if="item.deskripsi" class="mt-1 line-clamp-2 text-sm leading-relaxed text-gray-500">{{ truncate(item.deskripsi, 110) }}</p>
      <p v-if="item.pembina" class="mt-1 text-xs font-semibold text-gold-600">Pembina: {{ item.pembina }}</p>
    </div>
  </div>
</template>
