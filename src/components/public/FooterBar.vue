<script setup>
import { useCmsStore } from '@/stores/cms'
import LogoMark from './LogoMark.vue'
import AppIcon from '@/components/ui/AppIcon.vue'

const cms = useCmsStore()
const tahun = new Date().getFullYear()
</script>

<template>
  <footer class="relative mt-20 overflow-hidden bg-brand-950 text-brand-100">
    <div class="bg-grid absolute inset-0 opacity-[0.15]"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
      <div class="grid gap-10 md:grid-cols-4">
        <div class="md:col-span-2">
          <LogoMark :size="48" :with-text="true" dark />
          <p class="mt-4 max-w-md text-sm leading-relaxed text-brand-200/80">
            {{ cms.settings.deskripsi_singkat || 'Lembaga pendidikan Islam yang memadukan ilmu pengetahuan umum, ilmu agama, dan pembentukan karakter mulia.' }}
          </p>
          <div class="mt-5 flex gap-3">
            <a v-if="cms.settings.sosmed_instagram" :href="cms.settings.sosmed_instagram" target="_blank" rel="noopener"
              class="grid h-9 w-9 place-items-center rounded-xl bg-white/10 transition-colors hover:bg-gold-500">
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.2 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.2 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.2-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2-.1-1.3-.1-1.7-.1-4.9s0-3.6.1-4.9c.1-1.2.2-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4 1.3-.1 1.7-.1 4.9-.1m0-1.8c-3.3 0-3.7 0-5 .1-1.3.1-2.2.3-3 .6-.8.3-1.5.7-2.1 1.4-.7.7-1.1 1.3-1.4 2.1-.3.8-.5 1.7-.6 3-.1 1.3-.1 1.7-.1 5s0 3.7.1 5c.1 1.3.3 2.2.6 3 .3.8.7 1.5 1.4 2.1.7.7 1.3 1.1 2.1 1.4.8.3 1.7.5 3 .6 1.3.1 1.7.1 5 .1s3.7 0 5-.1c1.3-.1 2.2-.3 3-.6.8-.3 1.5-.7 2.1-1.4.7-.7 1.1-1.3 1.4-2.1.3-.8.5-1.7.6-3 .1-1.3.1-1.7.1-5s0-3.7-.1-5c-.1-1.3-.3-2.2-.6-3-.3-.8-.7-1.5-1.4-2.1-.7-.7-1.3-1.1-2.1-1.4-.8-.3-1.7-.5-3-.6-1.3-.1-1.7-.1-5-.1zM12 6.9a5.1 5.1 0 1 0 0 10.2 5.1 5.1 0 0 0 0-10.2zm0 8.4a3.3 3.3 0 1 1 0-6.6 3.3 3.3 0 0 1 0 6.6zm6.4-8.6a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0z"/></svg>
            </a>
            <a v-if="cms.settings.sosmed_facebook" :href="cms.settings.sosmed_facebook" target="_blank" rel="noopener"
              class="grid h-9 w-9 place-items-center rounded-xl bg-white/10 transition-colors hover:bg-gold-500">
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 21v-7h2.4l.4-2.9h-2.8V9.2c0-.8.3-1.4 1.5-1.4h1.3V5.2c-.6-.1-1.4-.2-2.2-.2-2.2 0-3.7 1.3-3.7 3.8v2.1H8v2.9h2.4v7h3.1z"/></svg>
            </a>
            <a v-if="cms.settings.sosmed_youtube" :href="cms.settings.sosmed_youtube" target="_blank" rel="noopener"
              class="grid h-9 w-9 place-items-center rounded-xl bg-white/10 transition-colors hover:bg-gold-500">
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23 7.5s-.2-1.5-.9-2.2c-.8-.9-1.8-.9-2.2-1C17.2 4 12 4 12 4s-5.2 0-7.9.3c-.4.1-1.4.1-2.2 1-.7.7-.9 2.2-.9 2.2S.8 9.3.8 11.1v1.7c0 1.8.2 3.6.2 3.6s.2 1.5.9 2.2c.8.9 1.9.9 2.4 1 1.7.2 7.7.3 7.7.3s5.2 0 7.9-.3c.4-.1 1.4-.1 2.2-1 .7-.7.9-2.2.9-2.2s.2-1.8.2-3.6v-1.7c0-1.8-.2-3.6-.2-3.6zM9.8 15.3V8.6l6.3 3.4-6.3 3.3z"/></svg>
            </a>
            <a v-if="cms.settings.sosmed_tiktok" :href="cms.settings.sosmed_tiktok" target="_blank" rel="noopener"
              class="grid h-9 w-9 place-items-center rounded-xl bg-white/10 transition-colors hover:bg-gold-500">
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.6 7.3a4.8 4.8 0 0 1-3.5-1.6 4.8 4.8 0 0 1-1.2-3.2h-3v12.6a2.9 2.9 0 1 1-2.9-2.9c.3 0 .6 0 .9.1V9.2a6 6 0 0 0-.9-.1 5.9 5.9 0 1 0 5.9 5.9V9.6a7.7 7.7 0 0 0 4.5 1.4V8.1c-.1 0-.1 0 0 0z"/></svg>
            </a>
          </div>
        </div>

        <div>
          <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-gold-400">Menu</h4>
          <ul class="space-y-2.5 text-sm text-brand-200/80">
            <li><RouterLink to="/" class="hover:text-gold-300">Beranda</RouterLink></li>
            <li><RouterLink to="/profil" class="hover:text-gold-300">Profil</RouterLink></li>
            <li><RouterLink to="/berita" class="hover:text-gold-300">Berita</RouterLink></li>
            <li><RouterLink to="/jurusan" class="hover:text-gold-300">Jurusan</RouterLink></li>
            <li><RouterLink to="/layanan" class="hover:text-gold-300">Layanan</RouterLink></li>
          </ul>
        </div>

        <div>
          <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-gold-400">Kontak</h4>
          <ul class="space-y-2.5 text-sm text-brand-200/80">
            <li class="flex gap-2"><AppIcon name="pin" :size="16" class="mt-0.5 shrink-0 text-gold-400" />{{ cms.settings.alamat || '—' }}</li>
            <li class="flex gap-2"><AppIcon name="phone" :size="16" class="mt-0.5 shrink-0 text-gold-400" />{{ cms.settings.telepon || '—' }}</li>
            <li class="flex gap-2"><AppIcon name="mail" :size="16" class="mt-0.5 shrink-0 text-gold-400" />{{ cms.settings.email || '—' }}</li>
          </ul>
        </div>
      </div>

      <div class="mt-10 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 text-xs text-brand-300/70 sm:flex-row">
        <span>© {{ tahun }} {{ cms.namaSekolah || "MA Bustanul Muta'allimin" }}. Hak cipta dilindungi.</span>
        <span>by Bumi Production</span>
      </div>
    </div>
  </footer>
</template>
