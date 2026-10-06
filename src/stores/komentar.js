import { defineStore } from 'pinia'
import { api } from '@/services/api'

/**
 * Jumlah komentar yang menunggu persetujuan.
 * Disimpan di store agar badge di sidebar ikut menyegar segera setelah
 * penulis/admin menyetujui atau membalas komentar.
 */
export const useKomentarStore = defineStore('komentar', {
  state: () => ({ menunggu: 0 }),

  actions: {
    async refresh() {
      try {
        const res = await api('/admin/komentar?status=menunggu&per_page=1')
        this.menunggu = res.total ?? 0
      } catch {
        this.menunggu = 0
      }
    },
  },
})
