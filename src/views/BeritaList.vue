<script setup>
import { ref, onMounted, watch } from 'vue'
import { api } from '@/services/api'
import PageHero from '@/components/public/PageHero.vue'
import NewsCard from '@/components/public/NewsCard.vue'
import AppIcon from '@/components/ui/AppIcon.vue'

const tabs = [
  { key: 'berita', label: 'Berita' },
  { key: 'pengumuman', label: 'Pengumuman' },
  { key: 'prestasi', label: 'Prestasi' },
  { key: 'umpan-balik', label: 'Umpan Balik' },
]

const active = ref('berita')
const posts = ref([])
const feedbacks = ref([])
const loading = ref(false)

async function load() {
  loading.value = true
  try {
    if (active.value === 'umpan-balik') {
      feedbacks.value = (await api('/umpan-balik')).data
    } else {
      posts.value = (await api(`/berita?kategori=${active.value}`)).data
    }
  } finally {
    loading.value = false
  }
}

onMounted(load)
watch(active, load)
</script>

<template>
  <div>
    <PageHero eyebrow="Informasi" title="Berita &amp; Pengumuman" subtitle="Kabar terbaru, pengumuman, prestasi, dan testimoni lulusan." />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <!-- Tabs -->
      <div class="mb-10 flex flex-wrap justify-center gap-2">
        <button
          v-for="t in tabs"
          :key="t.key"
          class="rounded-2xl px-5 py-2.5 text-sm font-bold transition-all"
          :class="active === t.key
            ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/25'
            : 'bg-brand-50 text-brand-700 hover:bg-brand-100'"
          @click="active = t.key"
        >
          {{ t.label }}
        </button>
      </div>

      <div v-if="loading" class="py-20 text-center text-gray-400">Memuat…</div>

      <!-- Berita / pengumuman / prestasi -->
      <div v-else-if="active !== 'umpan-balik'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <NewsCard v-for="p in posts" :key="p.id" :post="p" class="w-auto" />
      </div>
      <p v-if="!loading && active !== 'umpan-balik' && !posts.length" class="py-20 text-center text-gray-400">
        Belum ada data pada kategori ini.
      </p>

      <!-- Umpan balik -->
      <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="f in feedbacks" :key="f.id" class="rounded-3xl glass-card p-6">
          <div class="mb-4 flex gap-1 text-gold-500">
            <AppIcon v-for="n in 5" :key="n" name="star" :size="16" class="fill-current" />
          </div>
          <p class="text-sm leading-relaxed text-gray-600">“{{ f.pesan }}”</p>
          <div class="mt-5 flex items-center gap-3">
            <img v-if="f.foto_url" :src="f.foto_url" :alt="f.nama" class="h-11 w-11 rounded-full object-cover ring-2 ring-brand-200" />
            <div v-else class="grid h-11 w-11 place-items-center rounded-full bg-brand-500 font-bold text-white">{{ (f.nama || '?')[0] }}</div>
            <div>
              <div class="text-sm font-bold text-brand-950">{{ f.nama }}</div>
              <div class="text-xs text-gray-400">{{ f.jurusan }} · Lulusan {{ f.tahun_lulus }}</div>
            </div>
          </div>
        </div>
      </div>
      <p v-if="!loading && active === 'umpan-balik' && !feedbacks.length" class="py-20 text-center text-gray-400">
        Belum ada umpan balik.
      </p>
    </section>
  </div>
</template>
