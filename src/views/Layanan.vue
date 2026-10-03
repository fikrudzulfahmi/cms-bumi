<script setup>
import { ref, onMounted } from 'vue'
import { api } from '@/services/api'
import PageHero from '@/components/public/PageHero.vue'
import SectionHeader from '@/components/public/SectionHeader.vue'
import IconCard from '@/components/public/IconCard.vue'

const fasilitas = ref([])
const ekstra = ref([])

onMounted(async () => {
  const [f, e] = await Promise.all([api('/fasilitas'), api('/ekstrakurikuler')])
  fasilitas.value = f.data
  ekstra.value = e.data
})
</script>

<template>
  <div>
    <PageHero eyebrow="Layanan" title="Fasilitas &amp; Ekstrakurikuler" subtitle="Sarana penunjang dan wadah pengembangan minat bakat peserta didik." />

    <!-- Fasilitas -->
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <SectionHeader eyebrow="Sarana Prasarana" title="Fasilitas Madrasah" subtitle="Fasilitas lengkap untuk mendukung proses belajar mengajar." />
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <IconCard v-for="f in fasilitas" :key="f.id" :item="f" icon="🏫" />
      </div>
      <p v-if="!fasilitas.length" class="py-10 text-center text-gray-400">Belum ada data fasilitas.</p>
    </section>

    <!-- Ekstrakurikuler -->
    <section class="bg-brand-50/50 py-16">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeader eyebrow="Pengembangan Diri" title="Ekstrakurikuler" subtitle="Kegiatan untuk mengasah bakat dan keterampilan peserta didik." />
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <IconCard v-for="e in ekstra" :key="e.id" :item="e" icon="🎭" />
        </div>
        <p v-if="!ekstra.length" class="py-10 text-center text-gray-400">Belum ada data ekstrakurikuler.</p>
      </div>
    </section>
  </div>
</template>
