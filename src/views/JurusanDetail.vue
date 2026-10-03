<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { api } from '@/services/api'

const route = useRoute()
const major = ref(null)
const notFound = ref(false)

onMounted(async () => {
  try {
    major.value = (await api(`/jurusan/${route.params.slug}`)).data
  } catch {
    notFound.value = true
  }
})
</script>

<template>
  <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6">
    <div v-if="notFound" class="py-20 text-center">
      <div class="text-6xl">😕</div>
      <h1 class="mt-4 text-2xl font-extrabold text-brand-950">Jurusan tidak ditemukan</h1>
      <RouterLink to="/jurusan" class="mt-4 inline-block font-bold text-brand-600 hover:underline">← Kembali ke Jurusan</RouterLink>
    </div>

    <template v-else-if="major">
      <RouterLink to="/jurusan" class="inline-flex items-center gap-2 text-sm font-bold text-brand-600 hover:underline">
        ← Kembali ke Jurusan
      </RouterLink>

      <div class="mt-6 overflow-hidden rounded-[2rem] glass-card">
        <div class="relative h-64 overflow-hidden sm:h-80">
          <img v-if="major.gambar_url" :src="major.gambar_url" :alt="major.nama" class="h-full w-full object-cover" />
          <div v-else class="grid h-full w-full place-items-center bg-gradient-to-br from-brand-500 to-brand-700 text-7xl text-white/80">📚</div>
          <div class="absolute inset-0 bg-gradient-to-t from-brand-950/80 to-transparent"></div>
          <div class="absolute bottom-6 left-6">
            <span class="rounded-full bg-gold-400 px-4 py-1 text-xs font-bold text-brand-950 shadow">Akreditasi {{ major.akreditasi || '—' }}</span>
            <h1 class="mt-2 text-4xl font-extrabold text-white">{{ major.nama }}</h1>
          </div>
        </div>
        <div class="p-8">
          <h2 class="mb-4 text-lg font-extrabold text-brand-900">Tentang Jurusan</h2>
          <div class="prose-cms text-gray-600" v-html="major.deskripsi || '<p>—</p>'"></div>
        </div>
      </div>
    </template>
  </div>
</template>
