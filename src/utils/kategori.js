/**
 * Warna label kategori — diturunkan dari slug, jadi kategori baru yang ditambahkan
 * admin otomatis dapat warna tanpa perlu diatur.
 */
const PALET = [
  'bg-brand-100 text-brand-700',
  'bg-amber-100 text-amber-700',
  'bg-gold-100 text-gold-700',
  'bg-emerald-100 text-emerald-700',
  'bg-sky-100 text-sky-700',
  'bg-violet-100 text-violet-700',
  'bg-rose-100 text-rose-700',
]

export function warnaKategori(slug) {
  const s = String(slug || '')
  if (!s) return PALET[0]

  let hash = 0
  for (let i = 0; i < s.length; i++) {
    hash = (hash * 31 + s.charCodeAt(i)) >>> 0
  }

  return PALET[hash % PALET.length]
}

/** "cerpen" → "Cerpen" (dipakai bila kategori belum termuat). */
export function judulKategori(slug) {
  const s = String(slug || '')
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
}
