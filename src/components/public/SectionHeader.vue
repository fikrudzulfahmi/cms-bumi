<script setup>
defineProps({
  eyebrow: { type: String, default: '' },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  align: { type: String, default: 'center' }, // center | left
  light: { type: Boolean, default: false },
})
</script>

<template>
  <div class="mb-10" :class="align === 'center' ? 'text-center' : 'text-left'">
    <span
      v-if="eyebrow"
      class="mb-3 inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest"
      :class="light ? 'bg-white/15 text-gold-300' : 'bg-brand-100 text-brand-700'"
    >
      {{ eyebrow }}
    </span>
    <h2
      class="text-3xl font-extrabold tracking-tight sm:text-4xl"
      :class="light ? 'text-white' : 'text-brand-950'"
    >
      {{ title }}
    </h2>
    <p v-if="subtitle" class="mt-3 max-w-2xl text-base leading-relaxed"
      :class="[align === 'center' ? 'mx-auto' : '', light ? 'text-brand-200/80' : 'text-gray-500']">
      {{ subtitle }}
    </p>
    <div v-if="align === 'center'" class="mx-auto mt-4 h-1 w-16 rounded-full bg-gradient-to-r from-brand-500 to-gold-400"></div>
  </div>
</template>
