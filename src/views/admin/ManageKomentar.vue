<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useKomentarStore } from '@/stores/komentar'
import { waktuRelatif } from '@/utils/format'
import AppIcon from '@/components/ui/AppIcon.vue'

const auth = useAuthStore()
const komentar = useKomentarStore()

const data = ref([])
const status = ref('menunggu')
const loading = ref(false)
const galat = ref('')

// id komentar yang sedang dibalas + isi balasannya
const balasId = ref(null)
const isiBalasan = ref('')
const mengirim = ref(false)
const pesan = ref('')

const TAB = [
  { key: 'menunggu', label: 'Menunggu Persetujuan' },
  { key: 'belum-dibalas', label: 'Belum Dibalas' },
  { key: 'disetujui', label: 'Sudah Tampil' },
  { key: 'semua', label: 'Semua' },
]

function kueri() {
  if (status.value === 'semua') return ''
  if (status.value === 'belum-dibalas') return '&dibalas=belum'
  return `&status=${status.value}`
}

async function muat() {
  loading.value = true
  galat.value = ''
  try {
    const res = await api(`/admin/komentar?per_page=100${kueri()}`)
    data.value = res.data || []
    await komentar.refresh()          // badge sidebar ikut menyegar
  } catch (e) {
    galat.value = e.message || 'Gagal memuat komentar.'
    data.value = []
  } finally {
    loading.value = false
  }
}

function bukaBalas(k) {
  balasId.value = balasId.value === k.id ? null : k.id
  isiBalasan.value = k.balasan || ''
  pesan.value = ''
}

async function kirimBalasan(k) {
  mengirim.value = true
  pesan.value = ''
  try {
    const res = await api(`/admin/komentar/${k.id}/balas`, {
      method: 'POST',
      body: { balasan: isiBalasan.value },
    })
    pesan.value = res.message || 'Balasan terkirim.'
    balasId.value = null
    await muat()
  } catch (e) {
    alert(e.message || 'Balasan gagal dikirim.')
  } finally {
    mengirim.value = false
  }
}

async function hapusBalasan(k) {
  if (!confirm('Hapus balasan ini? Komentar pengunjung tetap ada.')) return
  try {
    await api(`/admin/komentar/${k.id}/balasan`, { method: 'DELETE' })
    await muat()
  } catch (e) {
    alert(e.message)
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
      <p class="text-sm text-gray-500">
        <template v-if="auth.isAdmin">
          Semua komentar dari seluruh berita. Komentar tampil di situs setelah disetujui.
        </template>
        <template v-else>
          Komentar pada berita <strong>milik Anda</strong>. Membalas komentar otomatis menampilkannya di situs.
        </template>
      </p>
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
        <!-- Komentar pengunjung -->
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
          <span
            v-if="k.balasan"
            class="rounded-full bg-brand-100 px-2.5 py-0.5 text-[11px] font-bold text-brand-700"
          >
            Sudah dibalas
          </span>
        </div>

        <p class="mb-3 text-sm leading-relaxed text-gray-700">{{ k.isi }}</p>

        <!-- Balasan yang sudah ada -->
        <div v-if="k.balasan" class="mb-3 rounded-xl border-l-4 border-brand-400 bg-brand-50/60 p-3">
          <p class="mb-1 flex items-center gap-1.5 text-xs font-bold text-brand-700">
            <AppIcon name="chat" :size="13" /> Balasan pengelola — {{ k.balasan_oleh }}
            <span class="font-normal text-gray-400">· {{ waktuRelatif(k.balasan_at) }}</span>
          </p>
          <p class="text-sm leading-relaxed text-gray-700">{{ k.balasan }}</p>
          <div class="mt-2 flex gap-2">
            <button class="text-xs font-bold text-brand-700 hover:underline" @click="bukaBalas(k)">Ubah</button>
            <button class="text-xs font-bold text-red-500 hover:underline" @click="hapusBalasan(k)">Hapus balasan</button>
          </div>
        </div>

        <!-- Form balasan -->
        <div v-if="balasId === k.id" class="mb-3">
          <textarea
            v-model="isiBalasan" rows="3" maxlength="1000"
            placeholder="Tulis balasan Anda…"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
          ></textarea>
          <div class="mt-2 flex justify-end gap-2">
            <button class="rounded-lg px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-100" @click="balasId = null">
              Batal
            </button>
            <button
              :disabled="mengirim"
              class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-brand-700 disabled:opacity-50"
              @click="kirimBalasan(k)"
            >
              <AppIcon name="chat" :size="13" /> {{ mengirim ? 'Mengirim…' : 'Kirim Balasan' }}
            </button>
          </div>
        </div>

        <p v-if="pesan" class="mb-3 rounded-lg bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-700">{{ pesan }}</p>

        <!-- Aksi -->
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
              v-if="!k.balasan && balasId !== k.id"
              class="inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-1.5 font-bold text-brand-700 hover:bg-brand-100"
              @click="bukaBalas(k)"
            >
              <AppIcon name="chat" :size="14" /> Balas
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
