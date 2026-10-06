<script setup>
/**
 * Rating bintang 1–5 (mendukung pecahan, mis. 4,5 → bintang ke-5 separuh).
 * Angka rating dihitung backend dari like, dislike, pengunjung, dan komentar.
 *
 * Cara menumpuk: dua lapisan bintang berukuran IDENTIK — lapisan abu-abu sebagai
 * dasar, lapisan emas di atasnya yang DIPOTONG (bukan dikecilkan) sesuai persentase.
 * `shrink-0` pada setiap ikon wajib: tanpa itu flexbox mengecilkan bintang emas
 * di dalam wadah yang sempit sehingga tampak lebih kecil dan tidak sejajar.
 */
import { computed } from 'vue'

const props = defineProps({
  nilai: { type: [Number, String], default: 0 },
  ukuran: { type: Number, default: 16 },
  tampilkanAngka: { type: Boolean, default: true },
})

const WARNA = '#f59e0b' // amber-500
const KOSONG = '#e5e7eb' // gray-200
const JUMLAH = 5

// Tanpa celah antar bintang agar hitungan persen tepat; jarak visual datang dari
// bentuk bintang itu sendiri yang tidak menyentuh tepi kotaknya.
const lebarTotal = computed(() => props.ukuran * JUMLAH)

const angka = computed(() => Math.max(0, Math.min(5, Number(props.nilai) || 0)))
const persen = computed(() => `${(angka.value / JUMLAH) * 100}%`)

const PATH = 'M12 2.6l2.95 5.98 6.6.96-4.77 4.65 1.13 6.57L12 17.66 6.09 20.76l1.13-6.57L2.45 9.54l6.6-.96z'
</script>

<template>
  <span class="inline-flex items-center gap-1.5" :title="`Rating ${angka.toFixed(1)} dari 5`">
    <span
      class="relative inline-block align-middle"
      :style="{ width: lebarTotal + 'px', height: ukuran + 'px' }"
      aria-hidden="true"
    >
      <!-- Lapisan dasar (bintang kosong) -->
      <span class="absolute inset-0 flex">
        <svg
          v-for="i in JUMLAH" :key="'kosong-' + i"
          class="block shrink-0" :width="ukuran" :height="ukuran" viewBox="0 0 24 24" :fill="KOSONG"
        >
          <path :d="PATH" />
        </svg>
      </span>

      <!-- Lapisan terisi (dipotong sesuai persen, ukuran tetap sama) -->
      <span class="absolute inset-y-0 left-0 overflow-hidden" :style="{ width: persen }">
        <span class="flex h-full" :style="{ width: lebarTotal + 'px' }">
          <svg
            v-for="i in JUMLAH" :key="'isi-' + i"
            class="block shrink-0" :width="ukuran" :height="ukuran" viewBox="0 0 24 24" :fill="WARNA"
          >
            <path :d="PATH" />
          </svg>
        </span>
      </span>
    </span>

    <span v-if="tampilkanAngka" class="text-xs font-bold text-gray-500">{{ angka.toFixed(1) }}</span>
  </span>
</template>
