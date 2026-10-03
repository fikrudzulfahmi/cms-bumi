<script setup>
import { ref } from 'vue'

const props = defineProps({
  items: { type: Array, default: () => [] },
  dark: { type: Boolean, default: false },
})

const track = ref(null)

function scroll(dir) {
  const el = track.value
  if (!el) return
  el.scrollBy({ left: dir * el.clientWidth * 0.8, behavior: 'smooth' })
}
</script>

<template>
  <div class="relative">
    <button
      class="absolute -left-3 top-1/2 z-10 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full shadow-lg transition-transform hover:scale-105"
      :class="dark ? 'bg-white/15 text-white hover:bg-white/25' : 'bg-white text-brand-700 hover:bg-brand-50'"
      @click="scroll(-1)"
      aria-label="Sebelumnya"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
    </button>

    <div
      ref="track"
      class="flex snap-x snap-mandatory gap-6 overflow-x-auto pb-2"
      style="scrollbar-width: none; -ms-overflow-style: none;"
    >
      <div v-for="(item, i) in items" :key="i" class="snap-start shrink-0">
        <slot :item="item" :index="i" />
      </div>
    </div>

    <button
      class="absolute -right-3 top-1/2 z-10 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full shadow-lg transition-transform hover:scale-105"
      :class="dark ? 'bg-white/15 text-white hover:bg-white/25' : 'bg-white text-brand-700 hover:bg-brand-50'"
      @click="scroll(1)"
      aria-label="Berikutnya"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
    </button>
  </div>
</template>

<style scoped>
::-webkit-scrollbar { display: none; }
</style>
