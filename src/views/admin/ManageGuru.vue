<script setup>
import CrudManager from '@/components/admin/CrudManager.vue'

const fields = [
  { name: 'nama', label: 'Nama', type: 'text', required: true },
  { name: 'jabatan', label: 'Jabatan', type: 'text' },
  { name: 'foto', label: 'Foto', type: 'image', dir: 'guru' },
  { name: 'urutan', label: 'Urutan', type: 'number' },
]
const columns = ['foto', 'nama', 'jabatan', 'urutan']
</script>

<template>
  <CrudManager endpoint="guru-karyawan" title="Guru & Karyawan" :fields="fields" :columns="columns" />
</template>
