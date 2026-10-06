<script setup>
/**
 * Moderasi komentar & balasan berita.
 *
 * Admin melihat semua; penulis hanya yang ada di berita miliknya.
 * Yang tampil di sini adalah komentar DAN balasan (termasuk balasan antar
 * pengunjung), dan keduanya bisa diubah isinya, disetujui/disembunyikan,
 * atau dihapus bila tidak pantas. Menghapus komentar utama sekaligus
 * menghapus seluruh balasannya.
 */
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useKomentarStore } from '@/stores/komentar'
import { waktuRelatif } from '@/utils/format'
import AppIcon from '@/components/ui/AppIcon.vue'

const auth = useAuthStore()
const komentarStore = useKomentarStore()

const data = ref([])
const status = ref('menunggu')
const jenis = ref('')            // '' | 'komentar' | 'balasan'
const loading = ref(false)
const galat = ref('')
const pesan = ref('')

const balasId = ref(null)        // komentar yang sedang dibalas pengelola
const isiBalasan = ref('')
const mengirim = ref(false)

const ubahId = ref(null)         // baris yang sedang diubah isinya
const isiUbah = ref('')

const TAB = [
  { key: 'menunggu', label: 'Menunggu Persetujuan' },
  { key: 'belum-dibalas', label: 'Belum Dibalas' },
  { key: 'disetujui', label: 'Sudah Tampil' },
  { key: 'semua', label: 'Semua' },
]
const JENIS = [
  { key: '', label: 'Komentar + Balasan' },
  { key: 'komentar', label: 'Komentar Saja' },
  { key: 'balasan', label: 'Balasan Saja' },
]

function kueri() {
  let q = ''
  if (status.value === 'belum-dibalas') q += '&dibalas=belum'
  else if (status.value !== 'semua') q += `&status=${status.value}`
  if (jenis.value) q += `&jenis=${jenis.value}`
  return q
}

async function muat() {
  loading.value = true
  galat.value = ''
  try {
    const res = await api(`/admin/komentar?per_page=100${kueri()}`)
    data.value = res.data || []
    await komentarStore.refresh()
  } catch (e) {
    galat.value = e.message || 'Gagal memuat komentar.'
    data.value = []
  } finally {
    loading.value = false
  }
}

function pilihTab(k) {
  status.value = k
  muat()
}

function pilihJenis(k) {
  jenis.value = k
  muat()
}

function bukaBalas(k) {
  balasId.value = balasId.value === k.id ? null : k.id
  isiBalasan.value = ''
  ubahId.value = null
  pesan.value = ''
}

async function kirimBalasan(k) {
  mengirim.value = true
  pesan.value = ''
  try {
    const res = await api(`/admin/komentar/${k.id}/balas`, { method: 'POST', body: { balasan: isiBalasan.value } })
    pesan.value = res.message || 'Balasan terkirim.'
    balasId.value = null
    isiBalasan.value = ''
    await muat()
  } catch (e) {
    alert(e.message || 'Balasan gagal dikirim.')
  } finally {
    mengirim.value = false
  }
}

function bukaUbah(k) {
  ubahId.value = ubahId.value === k.id ? null : k.id
  isiUbah.value = k.isi
  balasId.value = null
}

async function simpanUbah(k) {
  try {
    await api(`/admin/komentar/${k.id}`, { method: 'PUT', body: { isi: isiUbah.value } })
    ubahId.value = null
    pesan.value = 'Isi berhasil diperbarui.'
    await muat()
  } catch (e) {
    alert(e.message || 'Gagal menyimpan perubahan.')
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
  const adaBalasan = !k.parent_id && k.children_count > 0
  const tanya = adaBalasan
    ? `Hapus komentar "${k.nama}" beserta ${k.children_count} balasannya?`
    : `Hapus ${k.parent_id ? 'balasan' : 'komentar'} dari "${k.nama}"?`
  if (!confirm(tanya)) return
  try {
    const res = await api(`/admin/komentar/${k.id}`, { method: 'DELETE' })
    pesan.value = res.message || 'Dihapus.'
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
      <p class="text-sm text-gray-500">
        <template v-if="auth.isAdmin">
          Semua komentar &amp; balasan dari seluruh berita. Komentar tampil di situs setelah disetujui.
        </template>
        <template v-else>
          Komentar &amp; balasan pada berita <strong>milik Anda</strong>. Anda bisa membalas, menyunting, atau menghapus yang tidak pantas.
        </template>
      </p>
    </div>

    <!-- Filter -->
    <div class="mb-3 flex flex-wrap gap-2">
      <button
        v-for="t in TAB" :key="t.key"
        class="rounded-xl px-4 py-2 text-sm font-bold transition-colors"
        :class="status === t.key ? 'bg-brand-600 text-white' : 'bg-brand-50 text-brand-700 hover:bg-brand-100'"
        @click="pilihTab(t.key)"
      >
        {{ t.label }}
      </button>
    </div>
    <div class="mb-5 flex flex-wrap gap-2">
      <button
        v-for="j in JENIS" :key="j.key"
        class="rounded-lg px-3 py-1.5 text-xs font-bold transition-colors"
        :class="jenis === j.key ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
        @click="pilihJenis(j.key)"
      >
        {{ j.label }}
      </button>
    </div>

    <p v-if="pesan" class="mb-4 rounded-xl bg-brand-50 px-4 py-2.5 text-sm font-semibold text-brand-700">{{ pesan }}</p>
    <p v-if="galat" class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-600">{{ galat }}</p>
    <p v-if="loading" class="py-16 text-center text-gray-400">Memuat…</p>
    <p v-else-if="!data.length" class="rounded-2xl bg-white py-16 text-center text-gray-400 shadow-sm">
      Tidak ada data pada bagian ini.
    </p>

    <ul v-else class="space-y-3">
      <li
        v-for="k in data" :key="k.id"
        class="rounded-2xl border bg-white p-5 shadow-sm"
        :class="k.parent_id ? 'border-l-4 border-l-gray-300' : 'border-gray-100'"
      >
        <!-- Penanda jenis -->
        <div class="mb-2 flex flex-wrap items-center gap-2 text-xs">
          <span
            class="rounded-full px-2.5 py-0.5 font-bold"
            :class="k.parent_id ? 'bg-gray-100 text-gray-600' : 'bg-brand-50 text-brand-700'"
          >
            {{ k.parent_id ? 'Balasan' : 'Komentar' }}
          </span>
          <span v-if="k.is_pengelola" class="rounded-full bg-brand-600 px-2.5 py-0.5 font-bold text-white">Pengelola</span>
          <span v-if="k.balas_ke" class="text-gray-500">
            untuk <strong class="text-brand-700">@{{ k.balas_ke }}</strong>
          </span>
          <span v-if="k.parent_id && k.parent" class="truncate text-gray-400" :title="k.parent.isi">
            dari komentar {{ k.parent.nama }}
          </span>
        </div>

        <!-- Identitas + status -->
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

        <!-- Isi: tampil atau sedang diubah -->
        <div v-if="ubahId === k.id" class="mb-3">
          <textarea
            v-model="isiUbah" rows="3" maxlength="1500"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
          ></textarea>
          <div class="mt-2 flex justify-end gap-2">
            <button class="rounded-lg px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-100" @click="ubahId = null">
              Batal
            </button>
            <button
              class="rounded-lg bg-brand-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-brand-700"
              @click="simpanUbah(k)"
            >
              Simpan Perubahan
            </button>
          </div>
        </div>
        <p v-else class="mb-3 text-sm leading-relaxed text-gray-700">{{ k.isi }}</p>

        <!-- Form balasan pengelola -->
        <div v-if="balasId === k.id" class="mb-3">
          <textarea
            v-model="isiBalasan" rows="3" maxlength="1000"
            placeholder="Tulis balasan resmi dari pengelola…"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
          ></textarea>
          <div class="mt-2 flex justify-end gap-2">
            <button class="rounded-lg px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-100" @click="balasId = null">
              Batal
            </button>
            <button
              :disabled="mengirim || isiBalasan.trim().length < 2"
              class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-brand-700 disabled:opacity-40"
              @click="kirimBalasan(k)"
            >
              <AppIcon name="chat" :size="13" /> {{ mengirim ? 'Mengirim…' : 'Kirim Balasan' }}
            </button>
          </div>
        </div>

        <!-- Aksi -->
        <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-3 text-xs">
          <RouterLink
            v-if="k.post"
            :to="`/berita/${k.post.slug}`" target="_blank"
            class="inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-brand-600"
          >
            <AppIcon name="newspaper" :size="14" /> {{ k.post.judul }}
          </RouterLink>

          <div class="ml-auto flex flex-wrap items-center gap-2">
            <button
              v-if="!k.parent_id && balasId !== k.id"
              class="inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-1.5 font-bold text-brand-700 hover:bg-brand-100"
              @click="bukaBalas(k)"
            >
              <AppIcon name="chat" :size="14" /> Balas
            </button>
            <button
              class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 font-bold text-gray-600 hover:bg-gray-200"
              @click="bukaUbah(k)"
            >
              <AppIcon name="pencil" :size="14" /> Ubah
            </button>
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
