<script setup>
import CrudManager from '@/components/admin/CrudManager.vue'

/**
 * Kategori berita — bisa ditambah/diatur sendiri.
 * Kategori yang masih dipakai berita tidak bisa dihapus (pesan jelas dari server);
 * nonaktifkan saja supaya tidak muncul di pilihan baru.
 */
const fields = [
  { name: 'nama', label: 'Nama Kategori', type: 'text', required: true },
  { name: 'slug', label: 'Slug (opsional, untuk URL)', type: 'text' },
  { name: 'urutan', label: 'Urutan Tampil', type: 'number', default: 0 },
  { name: 'is_active', label: 'Aktif', type: 'boolean' },
]
const columns = ['nama', 'slug', 'urutan', 'is_active']
</script>

<template>
  <CrudManager endpoint="kategori" title="Kategori Berita" :fields="fields" :columns="columns" />
</template>
