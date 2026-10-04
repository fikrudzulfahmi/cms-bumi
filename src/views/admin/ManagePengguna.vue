<script setup>
import CrudManager from '@/components/admin/CrudManager.vue'

const fields = [
  { name: 'name', label: 'Nama', type: 'text', required: true },
  { name: 'email', label: 'Email', type: 'text', required: true },
  {
    name: 'role',
    label: 'Peran',
    type: 'select',
    required: true,
    default: 'penulis',
    options: [
      { value: 'admin', label: 'Admin — akses penuh' },
      { value: 'penulis', label: 'Penulis — hanya berita miliknya' },
    ],
  },
  { name: 'password', label: 'Password (kosongkan bila tidak diubah)', type: 'password' },
]
</script>

<template>
  <CrudManager endpoint="pengguna" title="Akun Pengguna" :fields="fields" :columns="['name', 'email', 'role']" />
</template>
