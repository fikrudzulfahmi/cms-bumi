const BULAN = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
]

/** Format tanggal "YYYY-MM-DD" → "18 September 2026" (tanpa masalah timezone). */
export function formatDate(value) {
  if (!value) return ''
  const s = String(value)
  const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/)
  if (m) {
    return `${parseInt(m[3], 10)} ${BULAN[parseInt(m[2], 10) - 1]} ${m[1]}`
  }
  return s
}

/** Hapus tag HTML. */
export function stripHtml(html) {
  if (!html) return ''
  return String(html).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()
}

/** Potong teks (HTML-safe). */
export function truncate(text, n = 120) {
  const t = stripHtml(text)
  return t.length > n ? `${t.slice(0, n).trim()}…` : t
}
