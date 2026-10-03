<script setup>
import CrudManager from '@/components/admin/CrudManager.vue'

const fields = [
  { name: 'judul', label: 'Judul', type: 'text', required: true },
  { name: 'kategori', label: 'Kategori', type: 'select', required: true, options: [
    { value: 'berita', label: 'Berita' },
    { value: 'pengumuman', label: 'Pengumuman' },
    { value: 'prestasi', label: 'Prestasi' },
  ] },
  { name: 'tanggal', label: 'Tanggal', type: 'date' },
  { name: 'gambar', label: 'Gambar', type: 'image', dir: 'berita' },
  { name: 'ringkasan', label: 'Ringkasan', type: 'textarea' },
  { name: 'konten', label: 'Isi Berita (HTML)', type: 'richtext' },
  { name: 'is_published', label: 'Publikasikan', type: 'boolean' },
]
const columns = ['gambar', 'judul', 'kategori', 'tanggal', 'is_published']
</script>

<template>
  <CrudManager endpoint="berita" title="Berita" :fields="fields" :columns="columns" />
</template>
