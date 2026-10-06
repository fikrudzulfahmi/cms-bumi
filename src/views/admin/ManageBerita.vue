<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import CrudManager from '@/components/admin/CrudManager.vue'
import { api } from '@/services/api'
import { useCmsStore } from '@/stores/cms'
import AppIcon from '@/components/ui/AppIcon.vue'
import BintangRating from '@/components/public/BintangRating.vue'

const cms = useCmsStore()
const analitik = ref(null)
const gagalMuat = ref(false)

// Kategori diambil dari daftar yang diatur admin (menu Kategori Berita),
// jadi kategori baru seperti "Cerpen" langsung bisa dipilih.
const fields = computed(() => [
  { name: 'judul', label: 'Judul', type: 'text', required: true },
  {
    name: 'kategori',
    label: 'Kategori',
    type: 'select',
    required: true,
    options: cms.kategori.map((k) => ({ value: k.slug, label: k.nama })),
  },
  { name: 'tanggal', label: 'Tanggal', type: 'date' },
  { name: 'gambar', label: 'Gambar', type: 'image', dir: 'berita' },
  { name: 'ringkasan', label: 'Ringkasan', type: 'textarea' },
  // SEO ala Yoast: judul & deskripsi yang dibaca Google/WhatsApp bisa diatur terpisah.
  {
    name: 'meta_judul',
    label: 'Judul SEO — tampil di hasil Google & tab browser (kosongkan untuk memakai judul berita)',
    type: 'text',
  },
  {
    name: 'meta_deskripsi',
    label: 'Deskripsi Meta — kalimat pembuka di hasil Google (idealnya 120–160 karakter)',
    type: 'textarea',
  },
  { name: 'kata_kunci', label: 'Kata Kunci Utama — yang ingin dicari orang di Google', type: 'text' },
  { name: 'konten', label: 'Isi Berita', type: 'richtext' },
  { name: 'is_published', label: 'Publikasikan', type: 'boolean' },
])

// Kolom tabel: identitas berita + statistik pembaca (like, dislike, pengunjung, rating).
const columns = [
  'gambar', 'judul', 'kategori', 'tanggal',
  'views', 'jumlah_like', 'jumlah_dislike', 'jumlah_komentar', 'rating',
  'is_published',
]

// Label khusus untuk kolom statistik (tidak ada di formulir).
const labels = {
  views: 'Pengunjung',
  jumlah_like: 'Suka',
  jumlah_dislike: 'Tidak suka',
  jumlah_komentar: 'Komentar',
  rating: 'Rating',
}

onMounted(async () => {
  try {
    analitik.value = (await api('/admin/berita/analitik')).data
  } catch {
    gagalMuat.value = true
  }
})
</script>

<template>
  <div>
    <!-- Rekap analisis berita -->
    <div v-if="analitik" class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h2 class="flex items-center gap-2 font-extrabold text-brand-950">
          <AppIcon name="activity" :size="18" /> Analisis Berita
        </h2>
        <RouterLink
          v-if="analitik.total.komentar_menunggu > 0"
          to="/admin/komentar"
          class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700 hover:bg-amber-200"
        >
          {{ analitik.total.komentar_menunggu }} komentar menunggu persetujuan →
        </RouterLink>
      </div>

      <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-xl bg-brand-50 p-3">
          <div class="text-xl font-extrabold text-brand-800">{{ analitik.total.terbit }}</div>
          <div class="text-[11px] font-semibold text-brand-600">Berita terbit</div>
        </div>
        <div class="rounded-xl bg-sky-50 p-3">
          <div class="text-xl font-extrabold text-sky-700">{{ analitik.total.pengunjung.toLocaleString('id-ID') }}</div>
          <div class="text-[11px] font-semibold text-sky-600">Total pengunjung</div>
        </div>
        <div class="rounded-xl bg-emerald-50 p-3">
          <div class="text-xl font-extrabold text-emerald-700">{{ analitik.total.like }}</div>
          <div class="text-[11px] font-semibold text-emerald-600">Suka</div>
        </div>
        <div class="rounded-xl bg-red-50 p-3">
          <div class="text-xl font-extrabold text-red-600">{{ analitik.total.dislike }}</div>
          <div class="text-[11px] font-semibold text-red-500">Tidak suka</div>
        </div>
        <div class="rounded-xl bg-violet-50 p-3">
          <div class="text-xl font-extrabold text-violet-700">{{ analitik.total.komentar }}</div>
          <div class="text-[11px] font-semibold text-violet-600">Komentar</div>
        </div>
        <div class="rounded-xl bg-gold-50 p-3">
          <div class="text-xl font-extrabold text-gold-700">{{ analitik.total.rating_rata }}</div>
          <BintangRating :nilai="analitik.total.rating_rata" :ukuran="12" :tampilkan-angka="false" />
        </div>
      </div>

      <!-- 5 berita terpopuler -->
      <div v-if="analitik.terpopuler.length" class="mt-4 border-t border-gray-100 pt-4">
        <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-400">5 berita terpopuler</p>
        <ul class="space-y-1.5">
          <li v-for="(p, i) in analitik.terpopuler" :key="p.id" class="flex items-center gap-3 text-sm">
            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600">{{ i + 1 }}</span>
            <RouterLink :to="`/berita/${p.slug}`" target="_blank" class="min-w-0 flex-1 truncate font-semibold text-gray-700 hover:text-brand-600">
              {{ p.judul }}
            </RouterLink>
            <span class="shrink-0 text-xs font-bold text-gray-400">{{ p.views.toLocaleString('id-ID') }} pengunjung</span>
            <BintangRating :nilai="p.rating" :ukuran="12" />
          </li>
        </ul>
      </div>
    </div>

    <p v-if="gagalMuat" class="mb-4 rounded-xl bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700">
      Rekap analisis gagal dimuat (hanya admin yang boleh melihatnya).
    </p>

    <CrudManager
      endpoint="berita"
      title="Berita"
      :fields="fields"
      :columns="columns"
      :labels="labels"
      seo
    />
  </div>
</template>
