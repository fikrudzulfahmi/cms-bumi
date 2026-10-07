<script setup>
import { ref, onMounted } from 'vue'
import { api } from '@/services/api'
import { useCmsStore } from '@/stores/cms'
import PageHero from '@/components/public/PageHero.vue'
import SectionHeader from '@/components/public/SectionHeader.vue'
import GuruCard from '@/components/public/GuruCard.vue'
import AppIcon from '@/components/ui/AppIcon.vue'

const cms = useCmsStore()
const guru = ref([])

onMounted(async () => {
  await cms.load()
  guru.value = (await api('/guru-karyawan')).data
})
</script>

<template>
  <div>
    <PageHero eyebrow="Profil Madrasah" :title="cms.namaSekolah || 'MA Bustanul Muta\u2019allimin'" subtitle="Mengenal lebih dekat perjalanan, landasan, dan para pendidik kami." />

    <!-- Sejarah -->
    <section class="mx-auto max-w-5xl px-4 py-16 sm:px-6">
      <SectionHeader eyebrow="Sejarah" title="Perjalanan Madrasah" />
      <div class="rounded-3xl glass-card p-8 sm:p-10">
        <div class="prose-cms text-gray-600" v-html="cms.profile?.sejarah || '<p>Belum ada data sejarah.</p>'" v-lazikan="{ utamaPertama: false }"></div>
      </div>
    </section>

    <!-- Visi Misi -->
    <section class="bg-brand-50/50 py-16">
      <div class="mx-auto max-w-5xl px-4 sm:px-6">
        <SectionHeader eyebrow="Landasan" title="Visi &amp; Misi" />
        <div class="grid gap-6 md:grid-cols-2">
          <div class="rounded-3xl bg-white p-8 shadow-lg shadow-brand-900/5">
            <AppIcon name="target" :size="28" class="mb-3 text-brand-600" />
            <h3 class="mb-2 text-lg font-extrabold text-brand-900">Visi</h3>
            <p class="leading-relaxed text-gray-600">{{ cms.profile?.visi || '—' }}</p>
          </div>
          <div class="rounded-3xl bg-white p-8 shadow-lg shadow-brand-900/5">
            <AppIcon name="clipboard" :size="28" class="mb-3 text-gold-500" />
            <h3 class="mb-2 text-lg font-extrabold text-brand-900">Misi</h3>
            <div class="prose-cms text-gray-600" v-html="cms.profile?.misi || '<p>—</p>'" v-lazikan="{ utamaPertama: false }"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Guru & Karyawan -->
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <SectionHeader eyebrow="SDM" title="Guru &amp; Karyawan" subtitle="Para pendidik dan tenaga kependidikan kami." />
      <div v-if="guru.length" class="flex flex-wrap justify-center gap-8">
        <GuruCard v-for="g in guru" :key="g.id" :teacher="g" />
      </div>
      <p v-else class="text-center text-gray-400">Belum ada data guru &amp; karyawan.</p>
    </section>
  </div>
</template>
