<script setup>
/**
 * Blok interaksi pembaca pada sebuah berita:
 * jumlah pengunjung, rating bintang, tombol suka/tidak suka, dan komentar.
 */
import { ref, onMounted } from 'vue'
import { api } from '@/services/api'
import { waktuRelatif } from '@/utils/format'
import AppIcon from '@/components/ui/AppIcon.vue'
import BintangRating from './BintangRating.vue'

const props = defineProps({
  slug: { type: String, required: true },
  views: { type: Number, default: 0 },
})

const reaksi = ref({ like: 0, dislike: 0, reaksi_saya: null, rating: 0 })
const komentar = ref([])
const form = ref({ nama: '', email: '', isi: '' })
const mengirim = ref(false)
const pesan = ref('')
const galat = ref('')

async function muat() {
  try {
    const [r, k] = await Promise.all([
      api(`/berita/${props.slug}/reaksi`),
      api(`/berita/${props.slug}/komentar`),
    ])
    reaksi.value = r.data
    komentar.value = k.data || []
  } catch {
    /* biarkan kosong bila gagal — halaman tetap tampil */
  }
}

async function pilih(tipe) {
  galat.value = ''
  try {
    const res = await api(`/berita/${props.slug}/like`, { method: 'POST', body: { tipe } })
    reaksi.value = res.data
  } catch (e) {
    galat.value = e.message || 'Gagal mengirim tanggapan.'
  }
}

async function kirimKomentar() {
  galat.value = ''
  pesan.value = ''
  mengirim.value = true
  try {
    const res = await api(`/berita/${props.slug}/komentar`, { method: 'POST', body: form.value })
    pesan.value = res.message || 'Komentar terkirim.'
    form.value.isi = ''
    localStorage.setItem('cms_nama_komentar', form.value.nama)
  } catch (e) {
    galat.value = e.message || 'Komentar gagal dikirim.'
  } finally {
    mengirim.value = false
  }
}

onMounted(() => {
  form.value.nama = localStorage.getItem('cms_nama_komentar') || ''
  muat()
})
</script>

<template>
  <section class="mt-10">
    <!-- Ringkasan: pengunjung + rating + tanggapan -->
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-3xl bg-brand-50/70 px-5 py-4">
      <div class="flex flex-wrap items-center gap-5">
        <span class="flex items-center gap-2 text-sm font-semibold text-brand-800">
          <AppIcon name="eye" :size="18" />
          {{ views.toLocaleString('id-ID') }} pengunjung
        </span>
        <span class="flex items-center gap-2">
          <BintangRating :nilai="reaksi.rating" :ukuran="18" />
        </span>
        <span class="flex items-center gap-2 text-sm font-semibold text-gray-500">
          <AppIcon name="chat" :size="17" /> {{ komentar.length }} komentar
        </span>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition-colors"
          :class="reaksi.reaksi_saya === 'like' ? 'bg-brand-600 text-white' : 'bg-white text-brand-700 hover:bg-brand-100'"
          @click="pilih('like')"
        >
          <AppIcon name="heart" :size="16" /> Suka ({{ reaksi.like }})
        </button>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition-colors"
          :class="reaksi.reaksi_saya === 'dislike' ? 'bg-red-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'"
          @click="pilih('dislike')"
        >
          <AppIcon name="alert" :size="16" /> Tidak ({{ reaksi.dislike }})
        </button>
      </div>
    </div>
    <p class="mt-2 text-xs text-gray-400">
      Rating dihitung dari suka, tidak suka, jumlah pengunjung, dan komentar.
    </p>

    <p v-if="galat" class="mt-3 rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600">{{ galat }}</p>

    <!-- Komentar -->
    <div class="mt-8">
      <h3 class="mb-4 text-lg font-extrabold text-brand-950">
        Komentar <span class="text-gray-400">({{ komentar.length }})</span>
      </h3>

      <p v-if="!komentar.length" class="rounded-2xl bg-gray-50 px-4 py-6 text-center text-sm text-gray-400">
        Belum ada komentar. Jadilah yang pertama.
      </p>

      <ul v-else class="space-y-3">
        <li v-for="k in komentar" :key="k.id" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
          <div class="mb-1 flex items-center gap-2 text-sm">
            <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-100 font-bold text-brand-700">
              {{ (k.nama || '?').charAt(0).toUpperCase() }}
            </span>
            <span class="font-bold text-brand-950">{{ k.nama }}</span>
            <span class="text-xs text-gray-400">· {{ waktuRelatif(k.created_at) }}</span>
          </div>
          <p class="pl-10 text-sm leading-relaxed text-gray-600">{{ k.isi }}</p>
        </li>
      </ul>

      <!-- Form komentar -->
      <form class="mt-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm" @submit.prevent="kirimKomentar">
        <h4 class="mb-3 font-extrabold text-brand-950">Tulis Komentar</h4>

        <div class="grid gap-3 sm:grid-cols-2">
          <input
            v-model="form.nama" required maxlength="80" placeholder="Nama Anda *"
            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
          />
          <input
            v-model="form.email" type="email" maxlength="120" placeholder="Email (opsional, tidak ditampilkan)"
            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
          />
        </div>

        <textarea
          v-model="form.isi" required minlength="5" maxlength="1500" rows="4"
          placeholder="Tulis komentar Anda…"
          class="mt-3 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
        ></textarea>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
          <p class="text-xs text-gray-400">Komentar tampil setelah disetujui pengelola.</p>
          <button
            type="submit" :disabled="mengirim"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition-transform hover:scale-[1.03] disabled:opacity-50"
          >
            <AppIcon name="chat" :size="16" /> {{ mengirim ? 'Mengirim…' : 'Kirim Komentar' }}
          </button>
        </div>

        <p v-if="pesan" class="mt-3 rounded-xl bg-brand-50 px-4 py-2.5 text-sm font-semibold text-brand-700">
          {{ pesan }}
        </p>
      </form>
    </div>
  </section>
</template>
