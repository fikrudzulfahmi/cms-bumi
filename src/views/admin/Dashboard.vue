<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { waktuRelatif, jamMenit } from '@/utils/format'
import AppIcon from '@/components/ui/AppIcon.vue'
import BintangRating from '@/components/public/BintangRating.vue'

const auth = useAuthStore()
const counts = ref({})
const stat = ref(null)
const berita = ref(null)

// adminOnly = hanya tampil untuk peran 'admin'
const allCards = [
  { key: 'berita', label: 'Berita & Postingan', icon: 'newspaper', to: '/admin/berita', color: 'from-gold-400 to-gold-600', adminOnly: false },
  { key: 'guru-karyawan', label: 'Guru & Karyawan', icon: 'users', to: '/admin/guru', color: 'from-brand-500 to-brand-700', adminOnly: true },
  { key: 'jurusan', label: 'Jurusan', icon: 'book', to: '/admin/jurusan', color: 'from-emerald-400 to-emerald-600', adminOnly: true },
  { key: 'fasilitas', label: 'Fasilitas', icon: 'landmark', to: '/admin/fasilitas', color: 'from-sky-400 to-sky-600', adminOnly: true },
  { key: 'ekstrakurikuler', label: 'Ekstrakurikuler', icon: 'drama', to: '/admin/ekstrakurikuler', color: 'from-violet-400 to-violet-600', adminOnly: true },
  { key: 'galeri', label: 'Galeri', icon: 'images', to: '/admin/galeri', color: 'from-rose-400 to-rose-600', adminOnly: true },
  { key: 'umpan-balik', label: 'Umpan Balik', icon: 'chat', to: '/admin/umpan-balik', color: 'from-amber-400 to-amber-600', adminOnly: true },
]

const cards = computed(() => allCards.filter((c) => !c.adminOnly || auth.isAdmin))

/* --- Label & warna per jenis peristiwa --------------------------------- */
const EVENT = {
  buat: { label: 'Buat', ikon: 'plus', kelas: 'bg-emerald-100 text-emerald-700' },
  ubah: { label: 'Ubah', ikon: 'pencil', kelas: 'bg-sky-100 text-sky-700' },
  hapus: { label: 'Hapus', ikon: 'trash', kelas: 'bg-red-100 text-red-600' },
  login: { label: 'Login', ikon: 'lock', kelas: 'bg-brand-100 text-brand-700' },
  login_gagal: { label: 'Login gagal', ikon: 'alert', kelas: 'bg-red-100 text-red-600' },
  logout: { label: 'Logout', ikon: 'logout', kelas: 'bg-gray-100 text-gray-600' },
  unggah_berkas: { label: 'Unggah', ikon: 'upload', kelas: 'bg-violet-100 text-violet-700' },
  akses_ditolak: { label: 'Akses ditolak', ikon: 'shield', kelas: 'bg-red-100 text-red-600' },
  rute_tidak_ditemukan: { label: 'Alamat ditebak', ikon: 'search', kelas: 'bg-amber-100 text-amber-700' },
  error_server: { label: 'Error server', ikon: 'alert', kelas: 'bg-red-100 text-red-600' },
  ekspor_log: { label: 'Ekspor log', ikon: 'download', kelas: 'bg-amber-100 text-amber-700' },
  komentar_baru: { label: 'Komentar', ikon: 'chat', kelas: 'bg-sky-100 text-sky-700' },
}
const ev = (kode) => EVENT[kode] || { label: kode, ikon: 'activity', kelas: 'bg-gray-100 text-gray-600' }

/* --- Kartu pemantauan 24 jam ------------------------------------------- */
const pantau = computed(() => {
  const p = stat.value?.pantau || {}
  return [
    { label: 'Aktivitas 24 jam', nilai: p.total_24jam, ikon: 'activity', warna: 'from-brand-500 to-brand-700' },
    { label: 'Pengguna aktif', nilai: p.pengguna_24jam, ikon: 'users', warna: 'from-emerald-400 to-emerald-600' },
    { label: 'Perlu diperiksa', nilai: p.critical_24jam, ikon: 'alert', warna: 'from-red-500 to-red-700', penting: true },
    { label: 'Perhatian', nilai: p.warning_24jam, ikon: 'info', warna: 'from-amber-400 to-amber-600' },
    { label: 'Login gagal', nilai: p.login_gagal_24jam, ikon: 'lock', warna: 'from-rose-400 to-rose-600' },
    { label: 'Berkas diunggah', nilai: p.unggah_24jam, ikon: 'upload', warna: 'from-violet-400 to-violet-600' },
  ]
})

const maksHarian = computed(() => {
  const h = stat.value?.harian || []
  return Math.max(1, ...h.map((x) => x.jumlah))
})

const hariTeks = (tgl) => String(Number(String(tgl).slice(8, 10)))

onMounted(async () => {
  const entries = await Promise.all(
    cards.value.map(async (c) => {
      try {
        const res = await api(`/admin/${c.key}`)
        return [c.key, res.data?.length || 0]
      } catch {
        return [c.key, null]
      }
    })
  )
  counts.value = Object.fromEntries(entries)

  // Statistik aktivitas & analisis berita hanya untuk admin.
  if (auth.isAdmin) {
    try {
      stat.value = (await api('/admin/log-aktivitas/statistik?hari=30')).data
    } catch {
      stat.value = null
    }
    try {
      berita.value = (await api('/admin/berita/analitik')).data
    } catch {
      berita.value = null
    }
  }
})
</script>

<template>
  <div class="space-y-6">
    <!-- Sambutan -->
    <div class="rounded-3xl bg-gradient-to-r from-brand-600 to-brand-800 p-6 text-white shadow-lg">
      <h2 class="flex items-center gap-2 text-2xl font-extrabold">
        Selamat datang di Panel Admin <AppIcon name="hand" :size="24" />
      </h2>
      <p class="mt-1 text-brand-100/80">
        {{ auth.isAdmin ? 'Kelola seluruh konten website madrasah dari satu tempat.' : 'Kelola berita dan pengumuman madrasah.' }}
      </p>
    </div>

    <!-- Rekap konten website (paling atas) -->
    <section>
      <h3 class="mb-3 text-lg font-extrabold text-brand-950">Rekap Konten Website</h3>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <RouterLink v-for="c in cards" :key="c.key" :to="c.to"
          class="group rounded-3xl bg-white p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
          <div class="mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br text-white" :class="c.color">
            <AppIcon :name="c.icon" :size="24" />
          </div>
          <div class="text-3xl font-extrabold text-brand-950">{{ counts[c.key] ?? '—' }}</div>
          <div class="text-sm font-semibold text-gray-500">{{ c.label }}</div>
        </RouterLink>
      </div>
    </section>

    <!-- Analisis berita + 5 terpopuler (admin) -->
    <section v-if="auth.isAdmin && berita" class="grid gap-4 lg:grid-cols-2">
      <div class="rounded-2xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 font-extrabold text-brand-950">
          <AppIcon name="newspaper" :size="18" /> Performa Berita
        </h3>

        <div class="grid gap-3 sm:grid-cols-3">
          <div class="rounded-xl bg-sky-50 p-3">
            <div class="text-2xl font-extrabold text-sky-700">{{ berita.total.pengunjung.toLocaleString('id-ID') }}</div>
            <div class="text-[11px] font-semibold text-sky-600">Total pengunjung</div>
          </div>
          <div class="rounded-xl bg-emerald-50 p-3">
            <div class="text-2xl font-extrabold text-emerald-700">{{ berita.total.like }}</div>
            <div class="text-[11px] font-semibold text-emerald-600">Suka</div>
          </div>
          <div class="rounded-xl bg-red-50 p-3">
            <div class="text-2xl font-extrabold text-red-600">{{ berita.total.dislike }}</div>
            <div class="text-[11px] font-semibold text-red-500">Tidak suka</div>
          </div>
        </div>

        <div class="mt-3 grid gap-3 sm:grid-cols-3">
          <div class="rounded-xl bg-violet-50 p-3">
            <div class="text-2xl font-extrabold text-violet-700">{{ berita.total.komentar }}</div>
            <div class="text-[11px] font-semibold text-violet-600">Komentar tampil</div>
          </div>
          <div class="rounded-xl bg-gold-50 p-3">
            <div class="text-2xl font-extrabold text-gold-700">{{ berita.total.rating_rata }}</div>
            <BintangRating :nilai="berita.total.rating_rata" :ukuran="12" :tampilkan-angka="false" />
          </div>
          <div class="rounded-xl bg-gray-50 p-3">
            <div class="text-2xl font-extrabold text-gray-700">{{ berita.total.terbit }}<span class="text-sm text-gray-400">/{{ berita.total.berita }}</span></div>
            <div class="text-[11px] font-semibold text-gray-500">Berita terbit</div>
          </div>
        </div>

        <p v-if="berita.total.komentar_menunggu > 0" class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
          {{ berita.total.komentar_menunggu }} komentar menunggu persetujuan —
          <RouterLink to="/admin/komentar" class="underline">buka moderasi</RouterLink>
        </p>
      </div>

      <div class="rounded-2xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 font-extrabold text-brand-950">
          <AppIcon name="star" :size="18" /> 5 Berita Terpopuler
        </h3>
        <p v-if="!berita.terpopuler.length" class="py-6 text-center text-sm text-gray-400">Belum ada data pengunjung.</p>
        <ul v-else class="space-y-2.5">
          <li v-for="(p, i) in berita.terpopuler" :key="p.id" class="flex items-center gap-3">
            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-brand-50 text-xs font-extrabold text-brand-700">{{ i + 1 }}</span>
            <span class="min-w-0 flex-1">
              <RouterLink :to="`/berita/${p.slug}`" target="_blank" class="block truncate text-sm font-semibold text-gray-700 hover:text-brand-600">
                {{ p.judul }}
              </RouterLink>
              <span class="text-xs text-gray-400">
                {{ p.views.toLocaleString('id-ID') }} pengunjung · {{ p.jumlah_like }} suka · {{ p.jumlah_komentar }} komentar
              </span>
            </span>
            <BintangRating :nilai="p.rating" :ukuran="13" />
          </li>
        </ul>
      </div>
    </section>

    <!-- Pemantauan aktivitas (admin) -->
    <section v-if="auth.isAdmin">
      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-lg font-extrabold text-brand-950">Aktivitas 24 Jam Terakhir</h3>
        <RouterLink to="/admin/log-aktivitas" class="text-sm font-bold text-brand-700 hover:underline">
          Log lengkap →
        </RouterLink>
      </div>

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div v-for="p in pantau" :key="p.label"
          class="rounded-2xl bg-white p-4 shadow-sm"
          :class="p.penting && p.nilai > 0 ? 'ring-2 ring-red-300' : ''">
          <div class="mb-2 grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br text-white" :class="p.warna">
            <AppIcon :name="p.ikon" :size="20" />
          </div>
          <div class="text-2xl font-extrabold text-brand-950">{{ p.nilai ?? '—' }}</div>
          <div class="text-xs font-semibold text-gray-500">{{ p.label }}</div>
        </div>
      </div>
    </section>

    <!-- Aktivitas terbaru + rekap perubahan -->
    <section v-if="auth.isAdmin" class="grid gap-4 lg:grid-cols-3">
      <div class="rounded-2xl bg-white p-5 shadow-sm lg:col-span-2">
        <h3 class="mb-4 font-extrabold text-brand-950">Aktivitas Terbaru</h3>

        <p v-if="!stat" class="py-6 text-center text-sm text-gray-400">Memuat…</p>
        <p v-else-if="!stat.terbaru.length" class="py-6 text-center text-sm text-gray-400">Belum ada aktivitas.</p>

        <ul v-else class="space-y-2">
          <li v-for="l in stat.terbaru" :key="l.id" class="flex items-start gap-3 rounded-xl px-2 py-2 hover:bg-gray-50">
            <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-lg" :class="ev(l.event).kelas">
              <AppIcon :name="ev(l.event).ikon" :size="15" />
            </span>
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-semibold text-gray-700" :title="l.description">{{ l.description }}</p>
              <p class="text-xs text-gray-400">
                {{ l.actor || l.actor_email || 'Sistem' }}
                <span v-if="l.actor && l.actor_email" class="text-gray-300"> · {{ l.actor_email }}</span>
              </p>
            </div>
            <span class="shrink-0 text-right text-xs text-gray-400" :title="jamMenit(l.created_at)">
              {{ waktuRelatif(l.created_at) }}
            </span>
          </li>
        </ul>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm">
          <h3 class="mb-1 font-extrabold text-brand-950">Perubahan Konten</h3>
          <p class="mb-4 text-xs text-gray-400">{{ stat?.rentang_hari ?? 30 }} hari terakhir</p>

          <div class="mb-4 grid grid-cols-3 gap-2 text-center">
            <div class="rounded-xl bg-emerald-50 py-2">
              <div class="text-lg font-extrabold text-emerald-700">{{ stat?.total?.buat ?? 0 }}</div>
              <div class="text-[11px] font-semibold text-emerald-600">Dibuat</div>
            </div>
            <div class="rounded-xl bg-sky-50 py-2">
              <div class="text-lg font-extrabold text-sky-700">{{ stat?.total?.ubah ?? 0 }}</div>
              <div class="text-[11px] font-semibold text-sky-600">Diubah</div>
            </div>
            <div class="rounded-xl bg-red-50 py-2">
              <div class="text-lg font-extrabold text-red-600">{{ stat?.total?.hapus ?? 0 }}</div>
              <div class="text-[11px] font-semibold text-red-500">Dihapus</div>
            </div>
          </div>

          <p v-if="!stat?.per_jenis?.length" class="py-3 text-center text-sm text-gray-400">
            Belum ada perubahan konten.
          </p>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="text-left text-[11px] uppercase tracking-wide text-gray-400">
                <th class="pb-1 font-bold">Jenis</th>
                <th class="pb-1 text-center font-bold">Buat</th>
                <th class="pb-1 text-center font-bold">Ubah</th>
                <th class="pb-1 text-center font-bold">Hapus</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="j in stat.per_jenis" :key="j.kode">
                <td class="py-2 pr-2 font-semibold text-gray-700">{{ j.jenis }}</td>
                <td class="py-2 text-center text-emerald-700">{{ j.buat || '·' }}</td>
                <td class="py-2 text-center text-sky-700">{{ j.ubah || '·' }}</td>
                <td class="py-2 text-center text-red-500">{{ j.hapus || '·' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="rounded-2xl p-5 shadow-sm"
          :class="stat?.rantai?.ok === false ? 'bg-red-50 ring-2 ring-red-200' : 'bg-white'">
          <h3 class="mb-1 flex items-center gap-2 font-extrabold text-brand-950">
            <AppIcon name="shield" :size="18" /> Keutuhan Log
          </h3>
          <p v-if="!stat" class="text-xs text-gray-400">Memuat…</p>
          <template v-else>
            <p class="text-sm font-bold" :class="stat.rantai.ok ? 'text-emerald-700' : 'text-red-600'">
              {{ stat.rantai.ok ? 'Rantai utuh — tidak ada manipulasi' : `Rantai RUSAK di baris #${stat.rantai.rusak_di}` }}
            </p>
            <p class="mt-1 text-xs text-gray-400">{{ stat.rantai.total ?? 0 }} baris terverifikasi</p>
          </template>
        </div>
      </div>
    </section>

    <!-- Grafik aktivitas harian -->
    <section v-if="auth.isAdmin && stat" class="rounded-2xl bg-white p-5 shadow-sm">
      <h3 class="mb-4 font-extrabold text-brand-950">Aktivitas 14 Hari Terakhir</h3>
      <div class="flex h-32 items-end gap-1.5">
        <div v-for="h in stat.harian" :key="h.tanggal" class="group flex flex-1 flex-col items-center justify-end gap-1"
          :title="`${h.tanggal}: ${h.jumlah} aktivitas`">
          <span class="text-[10px] font-bold text-gray-400 opacity-0 transition-opacity group-hover:opacity-100">{{ h.jumlah }}</span>
          <div class="w-full rounded-t-md bg-gradient-to-t from-brand-600 to-brand-400 transition-all group-hover:from-gold-500 group-hover:to-gold-400"
            :style="{ height: Math.max(3, (h.jumlah / maksHarian) * 88) + 'px' }"></div>
          <span class="text-[10px] text-gray-400">{{ hariTeks(h.tanggal) }}</span>
        </div>
      </div>
    </section>
  </div>
</template>
