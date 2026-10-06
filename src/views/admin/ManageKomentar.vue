<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '@/services/api'
import { waktuRelatif } from '@/utils/format'
import AppIcon from '@/components/ui/AppIcon.vue'

const data = ref([])
const status = ref('menunggu')
const loading = ref(false)
const galat = ref('')

const TAB = [
  { key: 'menunggu', label: 'Menunggu Persetujuan' },
  { key: 'disetujui', label: 'Sudah Tampil' },
  { key: 'semua', label: 'Semua' },
]

async function muat() {
  loading.value = true
  galat.value = ''
  try {
    const q = status.value === 'semua' ? '' : `&status=${status.value}`
    const res = await api(`/admin/komentar?per_page=100${q}`)
    data.value = res.data || []
  } catch (e) {
    galat.value = e.message || 'Gagal memuat komentar.'
    data.value = []
  } finally {
    loading.value = false
  }
}

async function setujui(k, nilai) {
  try {
    await api(`/admin/komentar/${k.id}`, { method: 'PUT', body: { is_approved: nilai } })
    await muat()
  } catch (e) {
    alert(e.message)
  }
}

async function hapus(k) {
  if (!confirm(`Hapus komentar dari "${k.nama}"?`)) return
  try {
    await api(`/admin/komentar/${k.id}`, { method: 'DELETE' })
    await muat()
  } catch (e) {
    alert(e.message)
  }
}

onMounted(muat)
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-extrabold text-brand-950">Komentar Berita</h1>
      <p class="text-sm text-gray-500">Komentar pembaca tampil di situs setelah disetujui di sini.</p>
    </div>

    <div class="mb-5 flex flex-wrap gap-2">
      <button
        v-for="t in TAB" :key="t.key"
        class="rounded-xl px-4 py-2 text-sm font-bold transition-colors"
        :class="status === t.key ? 'bg-brand-600 text-white' : 'bg-brand-50 text-brand-700 hover:bg-brand-100'"
        @click="status = t.key; muat()"
      >
        {{ t.label }}
      </button>
    </div>

    <p v-if="galat" class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-600">{{ galat }}</p>
    <p v-if="loading" class="py-16 text-center text-gray-400">Memuat…</p>
    <p v-else-if="!data.length" class="rounded-2xl bg-white py-16 text-center text-gray-400 shadow-sm">
      Tidak ada komentar pada bagian ini.
    </p>

    <ul v-else class="space-y-3">
      <li v-for="k in data" :key="k.id" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="mb-2 flex flex-wrap items-center gap-2 text-sm">
          <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-100 font-bold text-brand-700">
            {{ (k.nama || '?').charAt(0).toUpperCase() }}
          </span>
          <span class="font-bold text-brand-950">{{ k.nama }}</span>
          <span v-if="k.email" class="text-xs text-gray-400">{{ k.email }}</span>
          <span class="text-xs text-gray-400">· {{ waktuRelatif(k.created_at) }}</span>
          <span
            class="rounded-full px-2.5 py-0.5 text-[11px] font-bold"
            :class="k.is_approved ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
          >
            {{ k.is_approved ? 'Tampil' : 'Menunggu' }}
          </span>
        </div>

        <p class="mb-3 text-sm leading-relaxed text-gray-700">{{ k.isi }}</p>

        <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-3 text-xs">
          <RouterLink
            v-if="k.post"
            :to="`/berita/${k.post.slug}`" target="_blank"
            class="inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-brand-600"
          >
            <AppIcon name="newspaper" :size="14" /> {{ k.post.judul }}
          </RouterLink>

          <div class="ml-auto flex items-center gap-2">
            <button
              v-if="!k.is_approved"
              class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 font-bold text-white hover:bg-emerald-700"
              @click="setujui(k, true)"
            >
              <AppIcon name="check" :size="14" /> Setujui
            </button>
            <button
              v-else
              class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 font-bold text-gray-600 hover:bg-gray-200"
              @click="setujui(k, false)"
            >
              <AppIcon name="close" :size="14" /> Sembunyikan
            </button>
            <button
              class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 font-bold text-red-600 hover:bg-red-100"
              @click="hapus(k)"
            >
              <AppIcon name="trash" :size="14" /> Hapus
            </button>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>
