const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8011/api'

/**
 * Wrapper fetch sederhana untuk API CMS.
 * Token dibaca otomatis dari localStorage.
 */
export async function api(path, { method = 'GET', body, isForm = false } = {}) {
  const token = localStorage.getItem('cms_token') || ''
  const headers = { Accept: 'application/json' }

  if (!isForm) headers['Content-Type'] = 'application/json'
  if (token) headers['Authorization'] = `Bearer ${token}`

  const res = await fetch(API_BASE + path, {
    method,
    headers,
    body: isForm ? body : body !== undefined ? JSON.stringify(body) : undefined,
  })

  let data = null
  try {
    data = await res.json()
  } catch {
    data = null
  }

  if (!res.ok) {
    const err = new Error((data && data.message) || 'Terjadi kesalahan.')
    err.status = res.status
    err.data = data
    throw err
  }

  return data
}

/** Ambil URL gambar lengkap (path relatif storage). */
export function assetUrl(path) {
  if (!path) return ''
  if (/^https?:\/\//i.test(path)) return path
  const base = (import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8011').replace(/\/api\/?$/, '')
  return `${base}/storage/${path.replace(/^\/+/, '')}`
}

export { API_BASE }
