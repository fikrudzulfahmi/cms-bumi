<script setup>
import CrudManager from '@/components/admin/CrudManager.vue'

const fields = [
  { name: 'nama', label: 'Nama Fasilitas', type: 'text', required: true },
  { name: 'deskripsi', label: 'Deskripsi', type: 'textarea' },
  { name: 'gambar', label: 'Gambar', type: 'image', dir: 'fasilitas' },
]
const columns = ['gambar', 'nama', 'deskripsi']
</script>

<template>
  <CrudManager endpoint="fasilitas" title="Fasilitas" :fields="fields" :columns="columns" />
</template>
