import { defineStore } from 'pinia'
import { api } from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('cms_token') || '',
    user: JSON.parse(localStorage.getItem('cms_user') || 'null'),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
  },

  actions: {
    async login(email, password) {
      const { data } = await api('/admin/login', {
        method: 'POST',
        body: { email, password },
      })
      this.token = data.token
      this.user = data.user
      localStorage.setItem('cms_token', data.token)
      localStorage.setItem('cms_user', JSON.stringify(data.user))
      return data
    },

    async logout() {
      try {
        await api('/admin/logout', { method: 'POST' })
      } catch {
        /* abaikan */
      }
      this.clear()
    },

    /** Ubah nama/email/password akun yang sedang login. */
    async updateAccount(payload) {
      const { data } = await api('/admin/akun', { method: 'PUT', body: payload })
      this.user = data
      localStorage.setItem('cms_user', JSON.stringify(data))
      return data
    },

    clear() {
      this.token = ''
      this.user = null
      localStorage.removeItem('cms_token')
      localStorage.removeItem('cms_user')
    },
  },
})
