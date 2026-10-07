/**
 * Pengaturan gambar agar halaman tidak berat.
 *
 * Dipakai lewat direktif `v-lazikan` pada wadah yang isinya dinamis (hasil `v-html`),
 * karena gambar di dalam HTML dari editor tidak punya atribut apa pun.
 *
 * Aturan:
 *  - Gambar pertama (paling atas = elemen terbesar yang dilihat, LCP) dimuat SEGERA
 *    dengan prioritas tinggi. Melazy-kannya justru memperlambat halaman.
 *  - Gambar sisanya memakai lazy loading + decoding async, jadi hanya diunduh
 *    ketika hampir masuk layar.
 *  - Semua diberi efek muncul halus (lihat .gambar-masuk di main.css) supaya
 *    tidak terlihat "melompat" saat gambar selesai dimuat.
 *  - Ukuran dicadangkan lebih dulu supaya tata letak tidak bergeser.
 */
export function siapkanGambar(wadah, { utamaPertama = true } = {}) {
  if (!wadah || typeof wadah.querySelectorAll !== 'function') return

  const daftar = [...wadah.querySelectorAll('img')]

  daftar.forEach((img, i) => {
    if (img.dataset.gambarSiap === 'ya') return

    const utama = utamaPertama && i === 0

    if (utama) {
      img.setAttribute('loading', 'eager')
      img.setAttribute('fetchpriority', 'high')
      img.setAttribute('decoding', 'async')
    } else {
      img.setAttribute('loading', 'lazy')
      img.setAttribute('decoding', 'async')
    }

    // Cegah lompatan tata letak: bila ukuran tidak diketahui, beri rasio cadangan.
    if (!img.getAttribute('width') && !img.style.aspectRatio) {
      img.style.aspectRatio = '16 / 9'
      img.style.objectFit = 'cover'
    }

    img.dataset.gambarSiap = 'ya'
    img.classList.add('gambar-masuk')

    if (img.complete && img.naturalWidth > 0) {
      img.classList.add('gambar-termuat')
    } else {
      img.addEventListener('load', () => img.classList.add('gambar-termuat'), { once: true })
      img.addEventListener('error', () => img.classList.add('gambar-termuat'), { once: true })
    }
  })
}

/** Direktif: <div v-html="..." v-lazikan> — gambar di dalamnya dirapikan otomatis. */
export const lazikan = {
  mounted(el, binding) {
    siapkanGambar(el, binding.value || {})
  },
  updated(el, binding) {
    siapkanGambar(el, binding.value || {})
  },
}
