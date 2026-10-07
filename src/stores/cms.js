import { defineStore } from 'pinia'
import { api, assetUrl } from '@/services/api'
import { applyFavicon } from '@/utils/favicon'

export const useCmsStore = defineStore('cms', {
  state: () => ({
    settings: {},
    profile: null,
    kategori: [],
    loaded: false,
  }),

  getters: {
    namaSekolah: (s) => s.settings.nama_sekolah || '',
    akronim: (s) => s.settings.akronim || 'BUMI',
    motto: (s) => s.settings.motto || '',
    /** Tautan pendaftaran PPDB (diatur di Pengaturan, punya nilai bawaan). */
    linkPpdb: (s) => s.settings.link_ppdb || 'https://psb.bustanulmutaallimin.com',

    /** Tautan Sistem Presensi (diatur di Pengaturan) — tombol di navbar. */
    linkPresensi: (s) => s.settings.link_presensi || 'https://sistem.bustanulmutaallimin.com',

    /** Nama kategori dari slug — mendukung kategori yang ditambahkan admin. */
    namaKategori: (s) => (slug) => {
      const k = s.kategori.find((x) => x.slug === slug)
      return k ? k.nama : (slug ? slug.charAt(0).toUpperCase() + slug.slice(1) : '')
    },
  },

  actions: {
    async load() {
      if (this.loaded) return
      const [s, p] = await Promise.all([api('/settings'), api('/profil')])
      this.settings = s.data
      this.profile = p.data
      try {
        this.kategori = (await api('/kategori')).data || []
      } catch {
        this.kategori = []
      }
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
