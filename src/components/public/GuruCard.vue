<script setup>
import { assetUrl } from '@/services/api'

defineProps({
  teacher: { type: Object, required: true },
})

function initials(name) {
  return (name || '?')
    .replace(/^(Ust\.|Ustzh\.|KH\.|Hj\.|Dra\.|Drs\.|Ir\.|Bpk\.|Ibu)\s*/i, '')
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0])
    .join('')
    .toUpperCase()
}
</script>

<template>
  <div class="group w-44 shrink-0 text-center">
    <div class="relative mx-auto mb-3 h-40 w-40 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-100 to-brand-200 shadow-lg">
      <img
        v-if="teacher.foto_url"
        :src="teacher.foto_url"
        :alt="teacher.nama"
        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
        loading="lazy"
        decoding="async"
      />
      <div v-else class="grid h-full w-full place-items-center text-5xl font-extrabold text-brand-600">
        {{ initials(teacher.nama) }}
      </div>
    </div>
    <h4 class="text-sm font-bold leading-snug text-brand-950">{{ teacher.nama }}</h4>
    <p class="mt-1 text-xs font-semibold text-gold-600">{{ teacher.jabatan || '—' }}</p>
  </div>
</template>
