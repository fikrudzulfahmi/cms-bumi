/**
 * Pasang favicon secara dinamis — mengikuti logo yang di-upload di Pengaturan.
 * Dipanggil setiap kali pengaturan situs dimuat, jadi favicon ikut berubah
 * tanpa perlu build/deploy ulang.
 */
export function applyFavicon(url, fallback = '/favicon.svg') {
  const href = url || fallback

  let link = document.querySelector("link[rel='icon']")
  if (!link) {
    link = document.createElement('link')
    link.rel = 'icon'
    document.head.appendChild(link)
  }

  // buang link ikon lain supaya tidak ada yang menang duluan
  document.querySelectorAll("link[rel='icon']").forEach((l) => {
    if (l !== link) l.remove()
  })

  link.type = /\.svg(\?|$)/i.test(href) ? 'image/svg+xml' : 'image/png'
  link.href = href
}
