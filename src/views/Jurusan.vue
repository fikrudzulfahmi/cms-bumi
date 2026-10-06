<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '@/services/api'
import PageHero from '@/components/public/PageHero.vue'
import { truncate } from '@/utils/format'
import AppIcon from '@/components/ui/AppIcon.vue'

const majors = ref([])

onMounted(async () => {
  majors.value = (await api('/jurusan')).data
})
</script>

<template>
  <div>
    <PageHero eyebrow="Program Keahlian" title="Jurusan" subtitle="Pilihan program keahlian untuk masa depan peserta didik." />

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <div class="grid gap-6 md:grid-cols-2">
        <RouterLink
          v-for="m in majors"
          :key="m.id"
          :to="`/jurusan/${m.slug}`"
          class="group overflow-hidden rounded-3xl glass-card transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl"
        >
          <div class="relative h-52 overflow-hidden">
            <img loading="lazy" decoding="async" v-if="m.gambar_url" :src="m.gambar_url" :alt="m.nama" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
            <div v-else class="grid h-full w-full place-items-center bg-gradient-to-br from-brand-500 to-brand-700 text-white/80">
              <AppIcon name="book" :size="52" />
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-brand-950/70 to-transparent"></div>
            <span class="absolute right-4 top-4 rounded-full bg-gold-400 px-3 py-1 text-xs font-bold text-brand-950 shadow">Akreditasi {{ m.akreditasi || '—' }}</span>
            <h2 class="absolute bottom-4 left-5 text-2xl font-extrabold text-white">{{ m.nama }}</h2>
          </div>
          <div class="p-6">
            <p class="text-sm leading-relaxed text-gray-600">{{ truncate(m.deskripsi, 140) }}</p>
            <span class="mt-4 inline-flex items-center gap-1 font-bold text-gold-600">
              Selengkapnya
              <AppIcon name="arrowRight" :size="16" class="transition-transform group-hover:translate-x-1" />
            </span>
          </div>
        </RouterLink>
      </div>
      <p v-if="!majors.length" class="py-20 text-center text-gray-400">Belum ada data jurusan.</p>
    </section>
  </div>
</template>
