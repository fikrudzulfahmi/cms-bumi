<script setup>
import CrudManager from '@/components/admin/CrudManager.vue'

const fields = [
  { name: 'nama', label: 'Nama Ekstrakurikuler', type: 'text', required: true },
  { name: 'pembina', label: 'Pembina', type: 'text' },
  { name: 'deskripsi', label: 'Deskripsi', type: 'textarea' },
  { name: 'gambar', label: 'Gambar', type: 'image', dir: 'ekstra' },
]
const columns = ['gambar', 'nama', 'pembina']
</script>

<template>
  <CrudManager endpoint="ekstrakurikuler" title="Ekstrakurikuler" :fields="fields" :columns="columns" />
</template>
