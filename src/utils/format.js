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

/** Waktu relatif berbahasa Indonesia, mis. "5 menit lalu". */
export function waktuRelatif(value) {
  if (!value) return ''
  const t = new Date(value)
  if (Number.isNaN(t.getTime())) return ''

  const detik = Math.max(0, Math.floor((Date.now() - t.getTime()) / 1000))
  if (detik < 60) return 'baru saja'

  const menit = Math.floor(detik / 60)
  if (menit < 60) return `${menit} menit lalu`

  const jam = Math.floor(menit / 60)
  if (jam < 24) return `${jam} jam lalu`

  const hari = Math.floor(jam / 24)
  if (hari < 30) return `${hari} hari lalu`

  const bulan = Math.floor(hari / 30)
  if (bulan < 12) return `${bulan} bulan lalu`

  return `${Math.floor(bulan / 12)} tahun lalu`
}

/** Jam lokal "14:35" dari timestamp ISO (UTC dari API). */
export function jamMenit(value) {
  if (!value) return ''
  const t = new Date(value)
  if (Number.isNaN(t.getTime())) return ''
  return `${String(t.getHours()).padStart(2, '0')}:${String(t.getMinutes()).padStart(2, '0')}`
}
