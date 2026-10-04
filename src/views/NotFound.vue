<script setup>
import { RouterLink } from 'vue-router'
import AppIcon from '@/components/ui/AppIcon.vue'
</script>

<template>
  <section class="mx-auto flex max-w-3xl flex-col items-center px-4 py-24 text-center">
    <div class="mb-6 grid h-20 w-20 place-items-center rounded-3xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-xl shadow-brand-900/20">
      <AppIcon name="search" :size="36" />
    </div>

    <div class="text-6xl font-extrabold tracking-tight text-brand-900 sm:text-7xl">404</div>
    <h1 class="mt-3 text-2xl font-extrabold text-brand-950">Halaman Tidak Ditemukan</h1>
    <p class="mt-2 max-w-md text-sm leading-relaxed text-gray-500">
      Halaman yang Anda cari mungkin sudah dipindahkan, dihapus, atau alamatnya salah ketik.
    </p>

    <div class="mt-8 flex flex-wrap justify-center gap-3">
      <RouterLink
        to="/"
        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition-colors hover:bg-brand-700"
      >
        <AppIcon name="home" :size="16" /> Kembali ke Beranda
      </RouterLink>
      <RouterLink
        to="/berita"
        class="inline-flex items-center gap-2 rounded-xl border border-brand-200 bg-white px-5 py-2.5 text-sm font-bold text-brand-700 transition-colors hover:bg-brand-50"
      >
        <AppIcon name="newspaper" :size="16" /> Lihat Berita
      </RouterLink>
    </div>
  </section>
</template>
