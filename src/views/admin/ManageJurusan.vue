<script setup>
import CrudManager from '@/components/admin/CrudManager.vue'

const fields = [
  { name: 'nama', label: 'Nama Jurusan', type: 'text', required: true },
  { name: 'akreditasi', label: 'Akreditasi', type: 'text' },
  { name: 'gambar', label: 'Gambar', type: 'image', dir: 'jurusan' },
  { name: 'deskripsi', label: 'Deskripsi (HTML)', type: 'richtext' },
]
const columns = ['gambar', 'nama', 'akreditasi']
</script>

<template>
  <CrudManager endpoint="jurusan" title="Jurusan" :fields="fields" :columns="columns" />
</template>
