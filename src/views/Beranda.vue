<script setup>
import { ref, onMounted, computed } from 'vue'
import { api, assetUrl } from '@/services/api'
import { useCmsStore } from '@/stores/cms'
import { stripHtml, truncate } from '@/utils/format'
import SectionHeader from '@/components/public/SectionHeader.vue'
import AppSlider from '@/components/public/AppSlider.vue'
import NewsCard from '@/components/public/NewsCard.vue'
import IconCard from '@/components/public/IconCard.vue'
import AppIcon from '@/components/ui/AppIcon.vue'

const cms = useCmsStore()

const berita = ref([])
const prestasi = ref([])
const umpanBalik = ref([])
const ekstra = ref([])
const galeri = ref([])

// Gambar hero: pakai Pengaturan → Gambar Hero; kalau kosong, jatuh ke foto galeri pertama.
const heroImage = computed(() =>
  cms.settings.hero_gambar ? assetUrl(cms.settings.hero_gambar) : galeri.value[0]?.gambar_url || ''
)

const stats = computed(() => [
  { value: cms.settings.jumlah_siswa || '450', label: 'Peserta Didik' },
  { value: cms.settings.jumlah_guru || '35', label: 'Guru & Karyawan' },
  { value: cms.settings.jumlah_ekstra || '12', label: 'Ekstrakurikuler' },
  { value: cms.settings.akreditasi || 'A', label: 'Akreditasi' },
])

const waLink = computed(() => {
  const wa = (cms.settings.whatsapp || '').replace(/\D/g, '')
  return wa ? `https://wa.me/${wa}` : '#'
})

onMounted(async () => {
  await cms.load()
  const [b, p, u, e, g] = await Promise.all([
    api('/berita?kategori=berita&limit=8'),
    api('/berita?kategori=prestasi&limit=8'),
    api('/umpan-balik?limit=8'),
    api('/ekstrakurikuler?limit=8'),
    api('/galeri?limit=8'),
  ])
  berita.value = b.data
  prestasi.value = p.data
  umpanBalik.value = u.data
  ekstra.value = e.data
  galeri.value = g.data
})
</script>

<template>
  <div>
    <!-- ============ HERO ============ -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-brand-950">
      <div class="bg-grid absolute inset-0 opacity-20"></div>
      <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-gold-400/20 blur-3xl"></div>
      <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-brand-400/20 blur-3xl"></div>

      <!-- kotak-kotak dekoratif -->
      <div class="pointer-events-none absolute right-[8%] top-16 hidden gap-3 lg:flex">
        <div class="h-16 w-16 rounded-2xl bg-white/10 backdrop-blur animate-float"></div>
        <div class="h-16 w-16 rounded-2xl bg-gold-400/30 backdrop-blur animate-float" style="animation-delay: 1.2s"></div>
      </div>
      <div class="pointer-events-none absolute left-[12%] bottom-24 hidden gap-3 lg:flex">
        <div class="h-10 w-10 rounded-xl bg-gold-400/30 backdrop-blur animate-float" style="animation-delay: 0.6s"></div>
        <div class="h-10 w-10 rounded-xl bg-white/10 backdrop-blur animate-float" style="animation-delay: 1.8s"></div>
      </div>

      <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:py-28 lg:px-8">
        <div class="animate-fade-up">
          <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-gold-300 backdrop-blur">
            <span class="h-2 w-2 rounded-full bg-gold-400"></span>
            Madrasah Aliyah · Terakreditasi {{ cms.settings.akreditasi || 'A' }}
          </span>

          <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
            {{ cms.namaSekolah || "MA Bustanul Muta'allimin" }}
          </h1>

          <p class="mt-4 text-xl font-semibold text-gradient-gold sm:text-2xl">
            “{{ cms.motto }}”
          </p>

          <p class="mt-5 max-w-xl text-base leading-relaxed text-brand-100/80">
            {{ truncate(cms.settings.deskripsi_singkat, 180) }}
          </p>

          <div class="mt-8 flex flex-wrap gap-3">
            <a :href="cms.linkPpdb" target="_blank" rel="noopener"
              class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-gold-500 to-gold-400 px-6 py-3.5 font-bold text-white shadow-lg shadow-gold-500/30 transition-transform hover:scale-[1.03]">
              <AppIcon name="clipboard" :size="18" /> Info Pendaftaran
            </a>
            <RouterLink to="/profil"
              class="inline-flex items-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-6 py-3.5 font-bold text-white backdrop-blur transition-colors hover:bg-white/20">
              Kenali Kami <AppIcon name="arrowRight" :size="18" />
            </RouterLink>
          </div>

          <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div v-for="s in stats" :key="s.label" class="glass-dark rounded-2xl px-4 py-3 text-center">
              <div class="text-2xl font-extrabold text-gold-300">{{ s.value }}</div>
              <div class="text-[11px] font-semibold text-brand-100/70">{{ s.label }}</div>
            </div>
          </div>
        </div>

        <!-- Hero visual -->
        <div class="relative hidden lg:block">
          <div class="animate-float">
            <div class="relative mx-auto max-w-md overflow-hidden rounded-[2rem] border-4 border-white/20 bg-gradient-to-br from-brand-500 to-brand-800 shadow-2xl">
              <img v-if="heroImage" :src="heroImage" alt="Peserta didik madrasah" class="h-[440px] w-full object-contain" />
              <div v-else class="grid h-[440px] w-full place-items-center text-white/70">
                <AppIcon name="landmark" :size="64" />
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-brand-950/60 to-transparent"></div>
              <div class="absolute bottom-4 left-4 right-4 rounded-2xl glass-dark px-5 py-4">
                <div class="text-sm font-extrabold text-white">{{ cms.settings.tahun_berdiri ? 'Berdiri sejak ' + cms.settings.tahun_berdiri : '' }}</div>
                <div class="text-xs text-brand-100/80">Mendidik dengan Iman, Ilmu &amp; Amal</div>
              </div>
            </div>
          </div>
          <div class="absolute -left-6 top-10 rounded-2xl glass px-4 py-3 shadow-xl animate-float" style="animation-delay: 0.8s">
            <AppIcon name="trophy" :size="24" class="text-gold-500" />
            <div class="text-xs font-bold text-brand-800">Berprestasi</div>
          </div>
          <div class="absolute -right-4 bottom-16 rounded-2xl glass px-4 py-3 shadow-xl animate-float" style="animation-delay: 1.5s">
            <AppIcon name="book" :size="24" class="text-brand-600" />
            <div class="text-xs font-bold text-brand-800">Tahfidz Qur'an</div>
          </div>
        </div>
      </div>

      <!-- gelombang -->
      <svg class="block w-full text-white" viewBox="0 0 1440 80" fill="currentColor" preserveAspectRatio="none">
        <path d="M0,40 C360,90 1080,-10 1440,40 L1440,80 L0,80 Z"></path>
      </svg>
    </section>

    <!-- ============ VISI MISI ============ -->
    <section class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
      <SectionHeader eyebrow="Landasan Kami" title="Visi &amp; Misi" subtitle="Arah dan komitmen madrasah dalam membentuk generasi unggul." />
      <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl glass-card p-8">
          <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white">
            <AppIcon name="target" :size="24" />
          </div>
          <h3 class="mb-2 text-lg font-extrabold text-brand-900">Visi</h3>
          <p class="leading-relaxed text-gray-600">{{ cms.profile?.visi || '—' }}</p>
        </div>
        <div class="rounded-3xl glass-card p-8">
          <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-gold-400 to-gold-600 text-white">
            <AppIcon name="clipboard" :size="24" />
          </div>
          <h3 class="mb-2 text-lg font-extrabold text-brand-900">Misi</h3>
          <div class="prose-cms text-gray-600" v-html="cms.profile?.misi || '<p>—</p>'"></div>
        </div>
      </div>
    </section>

    <!-- ============ SLIDE BERITA ============ -->
    <section class="relative overflow-hidden bg-brand-50/50 py-20">
      <div class="bg-grid absolute inset-0 opacity-40"></div>
      <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeader eyebrow="Kabar Terkini" title="Berita Madrasah" subtitle="Informasi dan kegiatan terbaru seputar madrasah." />
        <AppSlider v-if="berita.length" :items="berita">
          <template #default="{ item }"><div class="w-80"><NewsCard :post="item" /></div></template>
        </AppSlider>
        <p v-else class="text-center text-gray-400">Belum ada berita.</p>
        <div class="mt-8 text-center">
          <RouterLink to="/berita" class="inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-6 py-3 font-bold text-white shadow-lg shadow-brand-600/25 transition-transform hover:scale-[1.03]">
            Lihat Semua Berita <AppIcon name="arrowRight" :size="18" />
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- ============ SLIDE PRESTASI ============ -->
    <section class="relative overflow-hidden bg-brand-950 py-20">
      <div class="bg-grid absolute inset-0 opacity-[0.12]"></div>
      <div class="absolute -left-20 top-10 h-72 w-72 rounded-full bg-gold-500/10 blur-3xl"></div>
      <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeader eyebrow="Prestasi" title="Raihan Prestasi" subtitle="Pencapaian membanggakan peserta didik kami." light />
        <AppSlider v-if="prestasi.length" :items="prestasi" dark>
          <template #default="{ item }"><div class="w-80"><NewsCard :post="item" /></div></template>
        </AppSlider>
        <p v-else class="text-center text-brand-200/60">Belum ada prestasi.</p>
      </div>
    </section>

    <!-- ============ FOTO MURID / GALERI ============ -->
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
      <SectionHeader eyebrow="Galeri" title="Potret Kegiatan" subtitle="Dokumentasi kegiatan belajar dan aktivitas peserta didik." />
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div v-for="(g, i) in galeri" :key="g.id" class="group relative overflow-hidden rounded-3xl"
          :class="i === 0 ? 'col-span-2 row-span-2' : ''">
          <img :src="g.gambar_url" :alt="g.judul" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" />
          <div class="absolute inset-0 bg-gradient-to-t from-brand-950/70 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
          <div class="absolute bottom-3 left-4 translate-y-2 font-bold text-white opacity-0 transition-all group-hover:translate-y-0 group-hover:opacity-100">{{ g.judul }}</div>
        </div>
      </div>
    </section>

    <!-- ============ SLIDE UMPAN BALIK ============ -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 to-brand-900 py-20">
      <div class="bg-grid-gold absolute inset-0 opacity-20"></div>
      <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeader eyebrow="Testimoni" title="Umpan Balik Lulusan" subtitle="Kata mereka tentang pengalaman belajar di madrasah." light />
        <AppSlider v-if="umpanBalik.length" :items="umpanBalik" dark>
          <template #default="{ item }">
            <div class="glass-dark w-[340px] rounded-3xl p-6">
              <div class="mb-4 flex gap-1 text-gold-300">
                <AppIcon v-for="n in 5" :key="n" name="star" :size="16" class="fill-current" />
              </div>
              <p class="text-sm leading-relaxed text-brand-50/90">“{{ item.pesan }}”</p>
              <div class="mt-5 flex items-center gap-3">
                <img v-if="item.foto_url" :src="item.foto_url" :alt="item.nama" class="h-11 w-11 rounded-full object-cover ring-2 ring-gold-400/50" />
                <div v-else class="grid h-11 w-11 place-items-center rounded-full bg-gold-400 font-bold text-white">{{ (item.nama || '?')[0] }}</div>
                <div>
                  <div class="text-sm font-bold text-white">{{ item.nama }}</div>
                  <div class="text-xs text-brand-100/70">{{ item.jurusan }} · Lulusan {{ item.tahun_lulus }}</div>
                </div>
              </div>
            </div>
          </template>
        </AppSlider>
        <p v-else class="text-center text-brand-200/60">Belum ada testimoni.</p>
      </div>
    </section>

    <!-- ============ SLIDE EKSTRAKURIKULER ============ -->
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
      <SectionHeader eyebrow="Pengembangan Diri" title="Kegiatan Ekstrakurikuler" subtitle="Wadah pengembangan minat dan bakat peserta didik." />
      <AppSlider v-if="ekstra.length" :items="ekstra">
        <template #default="{ item }">
          <div class="w-64"><IconCard :item="item" icon="drama" /></div>
        </template>
      </AppSlider>
      <p v-else class="text-center text-gray-400">Belum ada ekstrakurikuler.</p>
      <div class="mt-8 text-center">
        <RouterLink to="/layanan" class="inline-flex items-center gap-2 rounded-2xl border-2 border-brand-600 px-6 py-3 font-bold text-brand-700 transition-colors hover:bg-brand-600 hover:text-white">
          Lihat Semua Layanan <AppIcon name="arrowRight" :size="18" />
        </RouterLink>
      </div>
    </section>

    <!-- ============ PPDB / INFORMASI PENDAFTARAN ============ -->
    <section id="ppdb" class="relative overflow-hidden bg-brand-50/60 py-20">
      <div class="bg-checker absolute inset-0 opacity-40"></div>
      <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-700 to-brand-900 shadow-2xl">
          <div class="grid gap-8 p-8 sm:p-12 lg:grid-cols-2 lg:items-center">
            <div>
              <span class="inline-block rounded-full bg-gold-400/20 px-4 py-1 text-xs font-bold uppercase tracking-widest text-gold-300">PPDB</span>
              <h2 class="mt-4 text-3xl font-extrabold text-white sm:text-4xl">Informasi Pendaftaran</h2>
              <div class="prose-cms mt-4 text-sm leading-relaxed text-brand-100/85" v-html="cms.settings.informasi_pendaftaran || '<p>—</p>'"></div>
            </div>
            <div class="flex flex-col items-center gap-4">
              <div class="glass-dark w-full rounded-2xl p-6 text-center">
                <div class="text-sm text-brand-100/80">Jam Operasional</div>
                <div class="mt-1 text-lg font-bold text-white">{{ cms.settings.jam_operasional || '—' }}</div>
              </div>
              <a :href="cms.linkPpdb" target="_blank" rel="noopener"
                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-gold-500 to-gold-400 px-6 py-4 text-lg font-extrabold text-white shadow-lg shadow-gold-500/30 transition-transform hover:scale-[1.02]">
                <AppIcon name="clipboard" :size="20" /> Daftar PPDB Online
              </a>
              <a :href="waLink" target="_blank" rel="noopener"
                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-6 py-3.5 text-base font-bold text-white backdrop-blur transition-colors hover:bg-white/20">
                <AppIcon name="chat" :size="18" /> Hubungi via WhatsApp
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
