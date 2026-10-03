<script setup>
import CrudManager from '@/components/admin/CrudManager.vue'

const fields = [
  { name: 'judul', label: 'Judul', type: 'text' },
  { name: 'kategori', label: 'Kategori', type: 'select', required: true, options: [
    { value: 'kegiatan', label: 'Kegiatan' },
    { value: 'murid', label: 'Murid' },
    { value: 'fasilitas', label: 'Fasilitas' },
  ] },
  { name: 'gambar', label: 'Gambar', type: 'image', required: true, dir: 'galeri' },
]
const columns = ['gambar', 'judul', 'kategori']
</script>

<template>
  <CrudManager endpoint="galeri" title="Galeri" :fields="fields" :columns="columns" />
</template>
