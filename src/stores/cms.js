import { defineStore } from 'pinia'
import { api } from '@/services/api'

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
  },

  actions: {
    async load() {
      if (this.loaded) return
      const [s, p] = await Promise.all([api('/settings'), api('/profil')])
      this.settings = s.data
      this.profile = p.data
      this.loaded = true
    },
  },
})
