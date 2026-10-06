/**
 * Bantuan meta SEO di sisi klien.
 *
 * Meta utama sudah disusun SERVER lewat front controller (public/index.php) —
 * jadi crawler yang tidak menjalankan JavaScript pun membacanya dengan benar.
 * Berkas ini menyelaraskan nilai yang sama ketika pengunjung berpindah halaman
 * di dalam aplikasi (SPA), supaya judul tab dan meta tidak kembali ke nilai umum.
 */
export function setJudul(teks) {
  if (teks) document.title = teks
}

export function setDeskripsi(teks) {
  if (!teks) return
  let el = document.querySelector('meta[name="description"]')
  if (!el) {
    el = document.createElement('meta')
    el.setAttribute('name', 'description')
    document.head.appendChild(el)
  }
  el.setAttribute('content', teks)
}

/** Selaraskan og:title / og:description saat berpindah halaman. */
export function setOpenGraph({ judul, deskripsi, gambar } = {}) {
  const pasang = (prop, nilai) => {
    if (!nilai) return
    let el = document.querySelector(`meta[property="${prop}"]`)
    if (!el) {
      el = document.createElement('meta')
      el.setAttribute('property', prop)
      document.head.appendChild(el)
    }
    el.setAttribute('content', nilai)
  }

  pasang('og:title', judul)
  pasang('og:description', deskripsi)
  pasang('og:image', gambar)
  pasang('og:url', typeof window !== 'undefined' ? window.location.href : '')
  document.title = judul || document.title
}
