<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  eyebrow: { type: String, default: '' },
})
</script>

<template>
  <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-brand-950 py-16">
    <div class="bg-grid absolute inset-0 opacity-15"></div>
    <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold-400/15 blur-3xl"></div>
    <div class="relative mx-auto max-w-7xl px-4 text-center sm:px-6">
      <span v-if="eyebrow" class="mb-3 inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-bold uppercase tracking-widest text-gold-300">
        {{ eyebrow }}
      </span>
      <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-5xl">{{ title }}</h1>
      <p v-if="subtitle" class="mx-auto mt-3 max-w-2xl text-brand-100/80">{{ subtitle }}</p>
    </div>
  </section>
</template>
