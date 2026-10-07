<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { api } from '@/services/api'
import { formatDate } from '@/utils/format'
import { useCmsStore } from '@/stores/cms'
import { setOpenGraph } from '@/utils/seo'
import NewsCard from '@/components/public/NewsCard.vue'
import InteraksiBerita from '@/components/public/InteraksiBerita.vue'
import TombolBagikan from '@/components/public/TombolBagikan.vue'
import AppIcon from '@/components/ui/AppIcon.vue'

const route = useRoute()
const cms = useCmsStore()
const post = ref(null)
const terkait = ref([])
const notFound = ref(false)
const views = ref(0)

// Kategori bisa ditambah admin, jadi namanya diambil dari daftar kategori.
const kategoriLabel = computed(() => (post.value ? cms.namaKategori(post.value.kategori) : ''))

/** Alamat lengkap berita ini — untuk tombol bagikan. */
const tautanBerita = computed(() => {
  const dasar = typeof window !== 'undefined' ? window.location.origin : ''
  return post.value ? `${dasar}/berita/${post.value.slug}` : dasar
})

onMounted(async () => {
  try {
    post.value = (await api(`/berita/${route.params.slug}`)).data
    views.value = post.value.views || 0

    // Selaraskan judul & meta dengan nilai SEO berita (judul SEO menimpa judul berita).
    const nama = cms.namaSekolah || "MA Bustanul Muta'allimin"
    setOpenGraph({
      judul: (post.value.meta_judul || '').trim() || `${post.value.judul} | ${nama}`,
      deskripsi: post.value.seo_deskripsi || post.value.ringkasan || '',
      gambar: post.value.gambar_url || '',
    })

    terkait.value = (await api(`/berita?kategori=${post.value.kategori}&limit=3`)).data
      .filter((p) => p.id !== post.value.id)

    // Catat kunjungan — server hanya menghitung sekali per pengunjung per hari.
    api(`/berita/${post.value.slug}/view`, { method: 'POST' })
      .then((res) => {
        views.value = res.data.views
      })
      .catch(() => {})
  } catch {
    notFound.value = true
  }
})
</script>

<template>
  <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
    <div v-if="notFound" class="py-20 text-center">
      <AppIcon name="info" :size="56" class="mx-auto text-brand-300" />
      <h1 class="mt-4 text-2xl font-extrabold text-brand-950">Berita tidak ditemukan</h1>
      <RouterLink to="/berita" class="mt-4 inline-flex items-center gap-1.5 font-bold text-brand-600 hover:underline">
        <AppIcon name="arrowLeft" :size="18" /> Kembali ke Berita
      </RouterLink>
    </div>

    <template v-else-if="post">
      <RouterLink to="/berita" class="inline-flex items-center gap-2 text-sm font-bold text-brand-600 hover:underline">
        <AppIcon name="arrowLeft" :size="18" /> Kembali ke Berita
      </RouterLink>

      <div class="mt-6">
        <span class="inline-block rounded-full bg-brand-100 px-4 py-1 text-xs font-bold text-brand-700">{{ kategoriLabel }}</span>
        <h1 class="mt-4 text-3xl font-extrabold leading-tight text-brand-950 sm:text-4xl">{{ post.judul }}</h1>
        <div class="mt-3 flex items-center gap-1.5 text-sm text-gray-400">
          <AppIcon name="calendar" :size="15" /> {{ formatDate(post.tanggal) }}
          <template v-if="post.author_name">
            <span class="mx-1 text-gray-300">·</span>
            <AppIcon name="users" :size="15" /> Oleh: {{ post.author_name }}
          </template>
        </div>
      </div>

      <!-- Gambar utama = elemen terbesar yang dilihat (LCP): dimuat segera, bukan lazy -->
      <img
        v-if="post.gambar_url"
        loading="eager"
        fetchpriority="high"
        decoding="async"
        width="1200"
        height="675"
        :src="post.gambar_url"
        :srcset="post.gambar_srcset || undefined"
        sizes="(max-width: 896px) 100vw, 896px"
        :alt="post.judul"
        class="mt-8 aspect-video w-full rounded-3xl object-cover shadow-lg"
      />

      <div
        class="prose-cms mt-8 text-[17px] leading-relaxed text-gray-700"
        v-html="post.konten || post.ringkasan"
        v-lazikan="{ utamaPertama: false }"
      ></div>

      <!-- Bagikan berita ini (WhatsApp, Facebook, Telegram, salin tautan) -->
      <div class="mt-8 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <p class="mb-3 flex items-center gap-2 text-sm font-bold text-brand-950">
          <AppIcon name="share" :size="16" /> Bagikan berita ini
        </p>
        <TombolBagikan :judul="post.judul" :url="tautanBerita" />
      </div>

      <!-- Pengunjung, rating, suka/tidak suka, dan komentar -->
      <InteraksiBerita :slug="post.slug" :views="views" />

      <div v-if="terkait.length" class="mt-12 border-t border-gray-100 pt-8">
        <h3 class="mb-5 text-lg font-extrabold text-brand-950">Berita lainnya</h3>
        <div class="flex flex-wrap gap-6">
          <NewsCard v-for="p in terkait" :key="p.id" :post="p" class="w-auto" />
        </div>
      </div>
    </template>
  </div>
</template>
