<script setup>
/**
 * Tombol bagikan tautan berita.
 *
 * Dua tampilan:
 *  - `ringkas` (untuk kartu & tabel admin): satu tombol. Di HP memakai
 *    "bagikan" bawaan sistem (WhatsApp, IG, dll. langsung muncul), di desktop
 *    menyalin tautan lalu memberi tanda "Tersalin".
 *  - lengkap (untuk halaman berita): deretan tombol WhatsApp, Facebook,
 *    Telegram, dan salin tautan.
 */
import { ref } from 'vue'
import AppIcon from '@/components/ui/AppIcon.vue'

const props = defineProps({
  judul: { type: String, default: '' },
  url: { type: String, default: '' },   // kosong = pakai alamat halaman sekarang
  ringkas: { type: Boolean, default: false },
  terang: { type: Boolean, default: false }, // untuk latar gelap
})

const pesan = ref('')
const tersalin = ref(false)

const tautan = () => props.url || (typeof window !== 'undefined' ? window.location.href : '')
const judulnya = () => props.judul || (typeof document !== 'undefined' ? document.title : '')

async function salin() {
  const t = tautan()
  try {
    await navigator.clipboard.writeText(t)
  } catch {
    // Peramban lama / bukan HTTPS: pakai cara lama
    const sementara = document.createElement('input')
    sementara.value = t
    document.body.appendChild(sementara)
    sementara.select()
    document.execCommand('copy')
    document.body.removeChild(sementara)
  }
  tersalin.value = true
  pesan.value = 'Tautan disalin!'
  setTimeout(() => {
    tersalin.value = false
    pesan.value = ''
  }, 2200)
}

/** Tombol ringkas: pakai bagikan bawaan sistem bila ada, kalau tidak salin tautan. */
async function bagikanCepat() {
  if (typeof navigator !== 'undefined' && navigator.share) {
    try {
      await navigator.share({ title: judulnya(), text: judulnya(), url: tautan() })
      return
    } catch {
      return // pengguna menutup panel bagikan
    }
  }
  await salin()
}

const OPSI = [
  {
    kunci: 'whatsapp',
    label: 'WhatsApp',
    ikon: 'messageCircle',
    warna: 'bg-[#25D366] hover:bg-[#1ebe57]',
    href: () => `https://wa.me/?text=${encodeURIComponent(judulnya() + '\n' + tautan())}`,
  },
  {
    kunci: 'facebook',
    label: 'Facebook',
    ikon: 'thumbsUp',
    warna: 'bg-[#1877F2] hover:bg-[#0f66d0]',
    href: () => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(tautan())}`,
  },
  {
    kunci: 'telegram',
    label: 'Telegram',
    ikon: 'send',
    warna: 'bg-[#229ED9] hover:bg-[#1b83b5]',
    href: () => `https://t.me/share/url?url=${encodeURIComponent(tautan())}&text=${encodeURIComponent(judulnya())}`,
  },
]
</script>

<template>
  <!-- Ringkas: satu tombol -->
  <button
    v-if="ringkas"
    type="button"
    :title="tersalin ? 'Tautan disalin' : 'Bagikan tautan ini'"
    class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold transition-colors"
    :class="tersalin
      ? 'bg-emerald-100 text-emerald-700'
      : (terang ? 'bg-white/15 text-white hover:bg-white/25' : 'bg-gray-100 text-gray-600 hover:bg-gray-200')"
    @click.stop.prevent="bagikanCepat"
  >
    <AppIcon :name="tersalin ? 'check' : 'share'" :size="14" />
    <span>{{ tersalin ? 'Tersalin' : 'Bagikan' }}</span>
  </button>

  <!-- Lengkap: deretan pilihan -->
  <div v-else class="flex flex-wrap items-center gap-2">
    <span v-if="pesan" class="mr-1 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
      {{ pesan }}
    </span>
    <a
      v-for="o in OPSI" :key="o.kunci"
      :href="o.href()" target="_blank" rel="noopener"
      :title="`Bagikan ke ${o.label}`"
      class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-bold text-white shadow-sm transition-transform hover:scale-[1.04]"
      :class="o.warna"
    >
      <AppIcon :name="o.ikon" :size="16" />
      <span class="hidden sm:inline">{{ o.label }}</span>
    </a>
    <button
      type="button" title="Salin tautan"
      class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-bold shadow-sm transition-colors"
      :class="tersalin ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
      @click="salin"
    >
      <AppIcon :name="tersalin ? 'check' : 'link'" :size="16" />
      <span class="hidden sm:inline">{{ tersalin ? 'Tersalin' : 'Salin tautan' }}</span>
    </button>
  </div>
</template>
