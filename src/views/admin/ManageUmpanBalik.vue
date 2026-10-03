<script setup>
import CrudManager from '@/components/admin/CrudManager.vue'

const fields = [
  { name: 'nama', label: 'Nama', type: 'text', required: true },
  { name: 'tahun_lulus', label: 'Tahun Lulus', type: 'text' },
  { name: 'jurusan', label: 'Jurusan', type: 'text' },
  { name: 'pesan', label: 'Pesan', type: 'textarea' },
  { name: 'foto', label: 'Foto', type: 'image', dir: 'umpan' },
]
const columns = ['foto', 'nama', 'tahun_lulus', 'jurusan']
</script>

<template>
  <CrudManager endpoint="umpan-balik" title="Umpan Balik Lulusan" :fields="fields" :columns="columns" />
</template>
