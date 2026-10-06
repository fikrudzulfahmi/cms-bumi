<script setup>
/**
 * Analisis SEO untuk berita — gaya Yoast: menilai JUDUL SEO & DESKRIPSI META
 * (bukan sekadar judul berita), karena itulah yang dibaca Google.
 *
 * Bila kolom Judul SEO / Deskripsi Meta dikosongkan, penilaian memakai
 * judul berita & ringkasan sebagai cadangan — sama seperti perilaku Yoast.
 * Semua perhitungan berjalan di browser, jadi hasilnya langsung terlihat
 * tanpa perlu menyimpan dulu.
 */
import { computed } from 'vue'
import { stripHtml } from '@/utils/format'
import AppIcon from '@/components/ui/AppIcon.vue'

const props = defineProps({
  form: { type: Object, required: true },
})

const teks = (html) => stripHtml(html || '')

// Kata kunci melekat pada field formulir supaya ikut tersimpan ke server.
const kataKunci = computed({
  get: () => props.form.kata_kunci || '',
  set: (v) => {
    props.form.kata_kunci = v
  },
})

const judulBerita = computed(() => teks(props.form.judul))
const ringkasanBerita = computed(() => teks(props.form.ringkasan))
const kontenHtml = computed(() => String(props.form.konten || ''))

// Nilai EFEKTIF yang benar-benar dipakai halaman (lihat index.php).
const judulSeo = computed(() => (props.form.meta_judul || '').trim() || judulBerita.value)
const deskripsiMeta = computed(() => (props.form.meta_deskripsi || '').trim() || ringkasanBerita.value)

const pakaiJudulKhusus = computed(() => !!(props.form.meta_judul || '').trim())
const pakaiDeskripsiKhusus = computed(() => !!(props.form.meta_deskripsi || '').trim())

const jumlahKata = computed(() => {
  const t = teks(props.form.konten).trim()
  return t ? t.split(/\s+/).length : 0
})

/** URL ramalan dari judul (untuk menilai kata kunci di slug). */
const slugEfektif = computed(() => {
  const sumber = (props.form.slug || '').trim() || props.form.judul || ''
  return String(sumber)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')
})

function mengandung(teksnya, kunci) {
  if (!kunci) return false
  return String(teksnya || '').toLowerCase().includes(kunci.toLowerCase())
}

function hitungKemunculan(teksnya, kunci) {
  if (!kunci) return 0
  const pola = new RegExp(kunci.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi')
  return (String(teksnya || '').match(pola) || []).length
}

const pemeriksaan = computed(() => {
  const k = kataKunci.value.trim()
  const daftar = []
  const tambah = (label, lulus, tips) => daftar.push({ label, lulus, tips })

  // 1. Panjang judul SEO (yang dibaca Google)
  const pj = judulSeo.value.length
  tambah(
    `Judul SEO: ${pj} karakter (idealnya 40–60)`,
    pj >= 40 && pj <= 60,
    pj < 40
      ? 'Terlalu pendek — tambahkan keterangan agar jelas di hasil pencarian.'
      : 'Terlalu panjang — Google memotongnya sekitar 60 karakter.'
  )

  // 2. Kata kunci (bila diisi)
  if (k) {
    tambah(`Kata kunci "${k}" ada di judul SEO`, mengandung(judulSeo.value, k), 'Sisipkan kata kunci utama pada Judul SEO.')
    tambah('Kata kunci ada di deskripsi meta', mengandung(deskripsiMeta.value, k), 'Sebut kata kunci pada Deskripsi Meta.')
    tambah('Kata kunci ada di isi berita', mengandung(teks(kontenHtml.value), k), 'Gunakan kata kunci secara wajar di dalam isi.')
    tambah('Kata kunci ada di URL (slug)', mengandung(slugEfektif.value, k), 'Sertakan kata kunci pada judul agar masuk ke URL.')
    const muncul = hitungKemunculan(teks(kontenHtml.value), k)
    tambah(
      `Kepadatan kata kunci wajar (${muncul}× kemunculan)`,
      muncul > 0 && muncul <= Math.max(6, Math.ceil(jumlahKata.value / 60)),
      'Terlalu sering mengulang kata kunci bisa dianggap spam oleh Google.'
    )
  }

  // 3. Panjang deskripsi meta
  const pd = deskripsiMeta.value.length
  tambah(
    `Deskripsi meta: ${pd} karakter (idealnya 120–160)`,
    pd >= 120 && pd <= 160,
    pd === 0
      ? 'Belum ada deskripsi — tulislah 120–160 karakter yang mengundang orang mengklik.'
      : pd < 120
        ? 'Terlalu pendek — tambahkan sampai 120–160 karakter.'
        : 'Terlalu panjang — Google memotongnya sekitar 160 karakter.'
  )

  // 4. Panjang isi
  tambah(`Isi ${jumlahKata.value} kata (minimal 300)`, jumlahKata.value >= 300, 'Berita yang lengkap lebih mudah menempati peringkat.')

  // 5. Gambar (dipakai og:image saat dibagikan ke WhatsApp)
  tambah('Ada gambar utama', !!(props.form.gambar || '').trim(), 'Gambar muncul sebagai pratinjau saat tautan dibagikan di WhatsApp.')

  // 6–8. Struktur isi
  tambah('Isi punya sub-judul (Heading 2/3)', /<h[23][\s>]/i.test(kontenHtml.value), 'Pecah isi dengan sub-judul agar mudah dibaca mesin pencari.')
  tambah('Ada tautan keluar/masuk', /<a\s[^>]*href=/i.test(kontenHtml.value), 'Tautan ke sumber lain menambah kepercayaan (E-E-A-T).')
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

// Contoh tampilan di hasil pencarian Google (memakai nilai efektif).
const pratinjau = computed(() => ({
  url: `ma-bumi.sch.id › berita › ${slugEfektif.value.slice(0, 42) || 'judul-berita'}`,
  judul: judulSeo.value || 'Judul berita',
  deskripsi:
    deskripsiMeta.value ||
    'Deskripsi meta akan tampil di sini sebagai keterangan di hasil pencarian Google.',
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
      class="mb-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
    />
    <p class="mb-4 text-[11px] text-gray-500">
      Judul SEO:
      <strong :class="pakaiJudulKhusus ? 'text-brand-700' : 'text-gray-500'">
        {{ pakaiJudulKhusus ? 'dari kolom Judul SEO' : 'memakai judul berita' }}
      </strong>
      · Deskripsi:
      <strong :class="pakaiDeskripsiKhusus ? 'text-brand-700' : 'text-gray-500'">
        {{ pakaiDeskripsiKhusus ? 'dari kolom Deskripsi Meta' : 'memakai ringkasan berita' }}
      </strong>
    </p>

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
      <p class="mb-1 text-[11px] font-bold uppercase tracking-wide text-gray-400">
        Pratinjau di Google <span class="text-gray-300">·</span> begini juga tampilannya saat dibagikan ke WhatsApp
      </p>
      <p class="text-xs text-emerald-700">{{ pratinjau.url }}</p>
      <p class="text-base font-medium leading-snug text-blue-700">{{ pratinjau.judul }}</p>
      <p class="text-xs leading-snug text-gray-600">{{ pratinjau.deskripsi }}</p>
    </div>
  </div>
</template>
