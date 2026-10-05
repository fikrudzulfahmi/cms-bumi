import { defineStore } from 'pinia'
import { api, assetUrl } from '@/services/api'
import { applyFavicon } from '@/utils/favicon'

export const useCmsStore = defineStore('cms', {
  state: () => ({
    settings: {},
    profile: null,
    loaded: false,
  }),

  getters: {
    namaSekolah: (s) => s.settings.nama_sekolah || '',
    akronim: (s) => s.settings.akronim || 'BUMI',
    motto: (s) => s.settings.motto || '',
    /** Tautan pendaftaran PPDB (diatur di Pengaturan, punya nilai bawaan). */
    linkPpdb: (s) => s.settings.link_ppdb || 'https://psb.bustanulmutaallimin.com',
  },

  actions: {
    async load() {
      if (this.loaded) return
      const [s, p] = await Promise.all([api('/settings'), api('/profil')])
      this.settings = s.data
      this.profile = p.data
      this.loaded = true
      // Favicon mengikuti logo yang di-upload (Pengaturan → Logo)
      applyFavicon(this.settings.logo ? assetUrl(this.settings.logo) : '')
    },

    /** Paksa ambil ulang (dipakai setelah admin menyimpan pengaturan). */
    async reload() {
      this.loaded = false
      await this.load()
    },
  },
})
