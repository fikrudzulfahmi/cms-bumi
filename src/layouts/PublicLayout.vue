<script setup>
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import NavBar from '@/components/public/NavBar.vue'
import FooterBar from '@/components/public/FooterBar.vue'
import { useCmsStore } from '@/stores/cms'

const cms = useCmsStore()
onMounted(() => cms.load())
</script>

<template>
  <div class="flex min-h-screen flex-col bg-white">
    <NavBar />
    <main class="flex-1">
      <RouterView />
    </main>
    <FooterBar />
  </div>
</template>
