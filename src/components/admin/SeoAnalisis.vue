<script setup>
/**
 * Analisis SEO untuk berita — dihitung langsung di browser saat admin menulis,
 * jadi hasilnya langsung terlihat tanpa perlu menyimpan dulu.
 *
 * Yang diperiksa mengikuti kebiasaan alat SEO populer (Yoast/Rank Math):
 * panjang judul, ringkasan (meta description), panjang isi, kata kunci,
 * gambar, sub-judul, tautan, dan struktur daftar.
 */
import { computed, ref } from 'vue'
import { stripHtml } from '@/utils/format'
import AppIcon from '@/components/ui/AppIcon.vue'

const props = defineProps({
  form: { type: Object, required: true },
})

const kataKunci = ref('')

const teks = (html) => stripHtml(html || '')

const jumlahKata = computed(() => {
  const t = teks(props.form.konten).trim()
  return t ? t.split(/\s+/).length : 0
})

const ringkasan = computed(() => teks(props.form.ringkasan))
const judul = computed(() => teks(props.form.judul))

/** Cari kemunculan kata kunci (mengabaikan besar-kecil huruf). */
function mengandung(teksnya, kunci) {
  if (!kunci) return false
  return String(teksnya || '').toLowerCase().includes(kunci.toLowerCase())
}

function hitungKemunculan(teksnya, kunci) {
  if (!kunci) return 0
  const pola = new RegExp(kunci.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi')
  return (String(teksnya || '').match(pola) || []).length
}

const kontenHtml = computed(() => String(props.form.konten || ''))

const pemeriksaan = computed(() => {
  const k = kataKunci.value.trim()
  const daftar = []

  const tambah = (label, lulus, tips) => daftar.push({ label, lulus, tips })

  // 1. Judul
  tambah(
    `Judul ${judul.value.length} karakter (idealnya 40–60)`,
    judul.value.length >= 40 && judul.value.length <= 60,
    judul.value.length < 40
      ? 'Judul terlalu pendek — tambahkan keterangan agar lebih jelas di hasil pencarian.'
      : 'Judul terlalu panjang — Google memotongnya di sekitar 60 karakter.'
  )

  // 2. Kata kunci di judul
  if (k) {
    tambah(`Kata kunci "${k}" ada di judul`, mengandung(judul.value, k), 'Sisipkan kata kunci utama pada judul.')
    tambah('Kata kunci ada di ringkasan', mengandung(ringkasan.value, k), 'Sebut kata kunci di ringkasan/meta description.')
    tambah('Kata kunci ada di isi berita', mengandung(teks(kontenHtml.value), k), 'Gunakan kata kunci secara wajar di dalam isi.')
    const muncul = hitungKemunculan(teks(kontenHtml.value), k)
    tambah(
      `Kepadatan kata kunci wajar (${muncul}× kemunculan)`,
      muncul > 0 && muncul <= Math.max(6, Math.ceil(jumlahKata.value / 60)),
      'Terlalu sering mengulang kata kunci bisa dianggap spam oleh Google.'
    )
  }

  // 3. Ringkasan / meta description
  tambah(
    `Ringkasan ${ringkasan.value.length} karakter (idealnya 120–160)`,
    ringkasan.value.length >= 120 && ringkasan.value.length <= 160,
    ringkasan.value.length < 120
      ? 'Ringkasan terlalu pendek — tulis 120–160 karakter agar menarik diklik.'
      : 'Ringkasan terlalu panjang — Google memotongnya di sekitar 160 karakter.'
  )

  // 4. Panjang isi
  tambah(
    `Isi ${jumlahKata.value} kata (minimal 300)`,
    jumlahKata.value >= 300,
    'Berita yang lebih lengkap (minimal 300 kata) lebih mudah menempati peringkat.'
  )

  // 5. Gambar
  tambah('Ada gambar utama', !!(props.form.gambar || '').trim(), 'Tambahkan gambar — berita bergambar lebih sering diklik.')

  // 6. Sub-judul (h2/h3)
  tambah(
    'Isi punya sub-judul (Heading 2/3)',
    /<h[23][\s>]/i.test(kontenHtml.value),
    'Pecah isi dengan sub-judul agar mudah dibaca mesin pencari.'
  )

  // 7. Tautan
  tambah(
    'Ada tautan keluar/masuk',
    /<a\s[^>]*href=/i.test(kontenHtml.value),
    'Tautan ke sumber lain menambah kepercayaan (E-E-A-T).'
  )

  // 8. Daftar
  tambah('Ada daftar bernomor/titik', /<(ul|ol)[\s>]/i.test(kontenHtml.value), 'Daftar membuat informasi lebih mudah dipindai.')

  return daftar
})

const skor = computed(() => {
  const p = pemeriksaan.value
  if (!p.length) return 0
  return Math.round((p.filter((x) => x.lulus).length / p.length) * 100)
})

const warnaSkor = computed(() => {
  if (skor.value >= 80) return { teks: 'text-emerald-600', bg: 'bg-emerald-50', label: 'Bagus' }
  if (skor.value >= 50) return { teks: 'text-amber-600', bg: 'bg-amber-50', label: 'Cukup' }
  return { teks: 'text-red-600', bg: 'bg-red-50', label: 'Perlu diperbaiki' }
})

/** Contoh tampilan di hasil pencarian Google. */
const pratinjau = computed(() => ({
  judul: judul.value || 'Judul berita',
  url: `bumi.sch.id › berita › ${(props.form.slug || props.form.judul || 'judul').toString().toLowerCase().replace(/[^a-z0-9]+/g, '-').slice(0, 40)}`,
  deskripsi: ringkasan.value || 'Ringkasan berita akan tampil di sini sebagai keterangan di hasil pencarian Google.',
}))
</script>

<template>
  <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
      <h4 class="flex items-center gap-2 font-extrabold text-brand-950">
        <AppIcon name="search" :size="18" /> Analisis SEO
      </h4>
      <span class="rounded-full px-3 py-1 text-xs font-bold" :class="[warnaSkor.bg, warnaSkor.teks]">
        Skor {{ skor }}/100 · {{ warnaSkor.label }}
      </span>
    </div>

    <label class="mb-1.5 block text-xs font-semibold text-gray-600">
      Kata kunci utama (yang ingin dicari orang di Google)
    </label>
    <input
      v-model="kataKunci"
      type="text"
      placeholder="mis. pendaftaran siswa baru"
      class="mb-4 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
    />

    <!-- Hasil pemeriksaan -->
    <ul class="mb-4 space-y-1.5">
      <li v-for="p in pemeriksaan" :key="p.label" class="flex items-start gap-2 text-xs">
        <span
          class="mt-0.5 grid h-4 w-4 shrink-0 place-items-center rounded-full text-white"
          :class="p.lulus ? 'bg-emerald-500' : 'bg-amber-400'"
        >
          <AppIcon :name="p.lulus ? 'check' : 'alert'" :size="11" />
        </span>
        <span :class="p.lulus ? 'text-gray-500' : 'text-gray-700'">
          {{ p.label }}
          <em v-if="!p.lulus" class="block not-italic text-amber-700">{{ p.tips }}</em>
        </span>
      </li>
    </ul>

    <!-- Pratinjau di Google -->
    <div class="rounded-xl border border-gray-200 bg-white p-3">
      <p class="mb-1 text-[11px] font-bold uppercase tracking-wide text-gray-400">Pratinjau di Google</p>
      <p class="text-xs text-emerald-700">{{ pratinjau.url }}</p>
      <p class="text-base font-medium leading-snug text-blue-700">{{ pratinjau.judul }}</p>
      <p class="text-xs leading-snug text-gray-600">{{ pratinjau.deskripsi }}</p>
    </div>
  </div>
</template>
