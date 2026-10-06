<script setup>
/**
 * Rating bintang 1–5 (mendukung pecahan, mis. 4,5 → bintang ke-5 separuh).
 * Angka rating dihitung backend dari like, dislike, pengunjung, dan komentar.
 */
import { computed } from 'vue'

const props = defineProps({
  nilai: { type: [Number, String], default: 0 },
  ukuran: { type: Number, default: 16 },
  tampilkanAngka: { type: Boolean, default: true },
})

const WARNA = '#f59e0b' // amber-500
const KOSONG = '#e5e7eb' // gray-200
const BINTANG = [0, 1, 2, 3, 4]

const angka = computed(() => Math.max(0, Math.min(5, Number(props.nilai) || 0)))
const persen = computed(() => (angka.value / 5) * 100)
</script>

<template>
  <span class="inline-flex items-center gap-1.5" :title="`Rating ${angka.toFixed(1)} dari 5`">
    <span class="relative inline-flex" aria-hidden="true">
      <span class="flex">
        <svg
          v-for="i in BINTANG" :key="'kosong-' + i"
          :width="ukuran" :height="ukuran" viewBox="0 0 24 24" :fill="KOSONG"
        >
          <path d="M12 2.6l2.95 5.98 6.6.96-4.77 4.65 1.13 6.57L12 17.66 6.09 20.76l1.13-6.57L2.45 9.54l6.6-.96z" />
        </svg>
      </span>
      <span class="absolute inset-0 flex overflow-hidden" :style="{ width: persen + '%' }">
        <svg
          v-for="i in BINTANG" :key="'isi-' + i"
          :width="ukuran" :height="ukuran" viewBox="0 0 24 24" :fill="WARNA"
        >
          <path d="M12 2.6l2.95 5.98 6.6.96-4.77 4.65 1.13 6.57L12 17.66 6.09 20.76l1.13-6.57L2.45 9.54l6.6-.96z" />
        </svg>
      </span>
    </span>
    <span v-if="tampilkanAngka" class="text-xs font-bold text-gray-500">{{ angka.toFixed(1) }}</span>
  </span>
</template>
