<script setup>
import { ref, computed, onMounted } from 'vue'
import { api, API_BASE } from '@/services/api'
import AppIcon from '@/components/ui/AppIcon.vue'

const LABEL = {
  login: 'Login',
  login_gagal: 'Login gagal',
  logout: 'Logout',
  buat: 'Buat data',
  ubah: 'Ubah data',
  hapus: 'Hapus data',
  unggah_berkas: 'Unggah berkas',
  akses_ditolak: 'Akses ditolak',
  rute_tidak_ditemukan: 'Alamat ditebak',
  error_server: 'Error server',
  ekspor_log: 'Ekspor log',
}

const SEVERITY_STYLE = {
  critical: 'bg-red-100 text-red-700',
  warning: 'bg-amber-100 text-amber-700',
  info: 'bg-gray-100 text-gray-600',
}

const ringkasan = ref({})
const baris = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const rantai = ref({ ok: true, keterangan: '' })
const memuat = ref(false)
const terbuka = ref(null)

const filter = ref({ event: '', severity: '', dari: '', sampai: '', q: '' })

const kejadian = Object.entries(LABEL)

function tgl(v) {
  if (!v) return '—'
  const d = new Date(v)
  return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

function qs(extra = {}) {
  const p = new URLSearchParams()
  Object.entries({ ...filter.value, ...extra }).forEach(([k, v]) => {
    if (v !== '' && v !== null && v !== undefined) p.set(k, v)
  })
  return p.toString()
}

async function muat(page = 1) {
  memuat.value = true
  try {
    const [l, r, v] = await Promise.all([
      api('/admin/log-aktivitas?' + qs({ page, per_page: 25 })),
      api('/admin/log-aktivitas/ringkasan'),
      api('/admin/log-aktivitas/verifikasi'),
    ])
    baris.value = l.data || []
    meta.value = { current_page: l.current_page, last_page: l.last_page, total: l.total }
    ringkasan.value = r.data || {}
    rantai.value = v.data || {}
  } catch (e) {
    alert(e.message)
  } finally {
    memuat.value = false
  }
}

function reset() {
  filter.value = { event: '', severity: '', dari: '', sampai: '', q: '' }
  muat(1)
}

async function ekspor() {
  const token = localStorage.getItem('cms_token') || ''
  const res = await fetch(`${API_BASE}/admin/log-aktivitas/ekspor?` + qs(), {
    headers: { Authorization: `Bearer ${token}`, Accept: 'text/csv' },
  })
  if (!res.ok) {
    alert('Gagal mengunduh log.')
    return
  }
  const blob = await res.blob()
  const a = document.createElement('a')
  a.href = URL.createObjectURL(blob)
  a.download = `log-aktivitas-${new Date().toISOString().slice(0, 10)}.csv`
  a.click()
  URL.revokeObjectURL(a.href)
}

const kartu = computed(() => [
  { label: 'Aktivitas 24 jam', nilai: ringkasan.value.total_24jam ?? '—', icon: 'listChecks', warna: 'from-brand-500 to-brand-700' },
  { label: 'Perlu diperiksa', nilai: ringkasan.value.critical_24jam ?? '—', icon: 'alert', warna: 'from-red-500 to-red-700' },
  { label: 'Perhatian', nilai: ringkasan.value.warning_24jam ?? '—', icon: 'info', warna: 'from-amber-400 to-amber-600' },
  { label: 'Login gagal', nilai: ringkasan.value.login_gagal_24jam ?? '—', icon: 'lock', warna: 'from-slate-500 to-slate-700' },
])

onMounted(() => muat(1))
</script>

<template>
  <div>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-brand-950">Log Aktivitas</h1>
        <p class="text-sm text-gray-500">
          Jejak audit panel admin — tersimpan permanen dan <strong>tidak bisa dihapus</strong> dari aplikasi.
        </p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <span
          class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-bold"
          :class="rantai.ok ? 'bg-brand-100 text-brand-700' : 'bg-red-100 text-red-700'"
          :title="rantai.keterangan"
        >
          <AppIcon :name="rantai.ok ? 'shield' : 'alert'" :size="14" />
          {{ rantai.ok ? 'Rantai log utuh' : 'Rantai log RUSAK' }}
        </span>
        <button
          class="inline-flex items-center gap-2 rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm font-bold text-brand-700 hover:bg-brand-50"
          @click="ekspor"
        >
          <AppIcon name="download" :size="16" /> Ekspor CSV
        </button>
      </div>
    </div>

    <p v-if="!rantai.ok" class="mb-4 flex items-start gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
      <AppIcon name="circleAlert" :size="18" class="mt-0.5 shrink-0" />
      <span>{{ rantai.keterangan }} Segera ganti semua password admin dan periksa akun pengguna.</span>
    </p>

    <!-- Ringkasan -->
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div v-for="k in kartu" :key="k.label" class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="mb-3 grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br text-white" :class="k.warna">
          <AppIcon :name="k.icon" :size="22" />
        </div>
        <div class="text-2xl font-extrabold text-brand-950">{{ k.nilai }}</div>
        <div class="text-sm font-semibold text-gray-500">{{ k.label }}</div>
      </div>
    </div>

    <!-- Filter -->
    <div class="mb-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <input v-model="filter.q" placeholder="Cari aktor / deskripsi / IP…" class="rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:outline-none" @keyup.enter="muat(1)" />
        <select v-model="filter.event" class="rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:outline-none">
          <option value="">Semua peristiwa</option>
          <option v-for="[k, v] in kejadian" :key="k" :value="k">{{ v }}</option>
        </select>
        <select v-model="filter.severity" class="rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:outline-none">
          <option value="">Semua tingkat</option>
          <option value="critical">Perlu diperiksa (critical)</option>
          <option value="warning">Perhatian (warning)</option>
          <option value="info">Biasa (info)</option>
        </select>
        <input v-model="filter.dari" type="date" class="rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:outline-none" />
        <input v-model="filter.sampai" type="date" class="rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:outline-none" />
      </div>
      <div class="mt-3 flex gap-2">
        <button class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="muat(1)">Terapkan</button>
        <button class="rounded-xl border border-gray-200 px-5 py-2 text-sm font-bold text-gray-600" @click="reset">Reset</button>
      </div>
    </div>

    <!-- Tabel -->
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-brand-50/70 text-xs uppercase tracking-wider text-brand-800">
            <tr>
              <th class="px-4 py-3">Waktu</th>
              <th class="px-4 py-3">Tingkat</th>
              <th class="px-4 py-3">Peristiwa</th>
              <th class="px-4 py-3">Keterangan</th>
              <th class="px-4 py-3">Aktor</th>
              <th class="px-4 py-3">IP</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-for="b in baris" :key="b.id">
              <tr class="cursor-pointer hover:bg-brand-50/40" @click="terbuka = terbuka === b.id ? null : b.id">
                <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ tgl(b.created_at) }}</td>
                <td class="px-4 py-3">
                  <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase" :class="SEVERITY_STYLE[b.severity]">
                    {{ b.severity }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 font-semibold text-brand-900">{{ LABEL[b.event] || b.event }}</td>
                <td class="px-4 py-3 text-gray-700">{{ b.description }}</td>
                <td class="px-4 py-3">
                  <div class="font-semibold text-gray-800">{{ b.actor || '—' }}</div>
                  <div class="text-xs text-gray-400">{{ b.actor_email || '' }}</div>
                </td>
                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-600">{{ b.ip || '—' }}</td>
                <td class="px-4 py-3 text-gray-400">
                  <AppIcon :name="terbuka === b.id ? 'chevronDown' : 'chevronRight'" :size="16" />
                </td>
              </tr>
              <tr v-if="terbuka === b.id" class="bg-brand-50/30">
                <td colspan="7" class="px-4 py-4">
                  <div class="grid gap-3 text-xs sm:grid-cols-2">
                    <div><span class="font-bold text-gray-500">Metode/Path:</span> <span class="font-mono text-gray-700">{{ b.method }} {{ b.path }}</span></div>
                    <div><span class="font-bold text-gray-500">Status HTTP:</span> <span class="font-mono text-gray-700">{{ b.status || '—' }}</span></div>
                    <div><span class="font-bold text-gray-500">Objek:</span> <span class="font-mono text-gray-700">{{ b.subject_type || '—' }} #{{ b.subject_id || '—' }}</span></div>
                    <div><span class="font-bold text-gray-500">Forwarded-for:</span> <span class="font-mono text-gray-700">{{ b.forwarded_for || '—' }}</span></div>
                    <div class="sm:col-span-2"><span class="font-bold text-gray-500">Perangkat:</span> <span class="text-gray-700">{{ b.user_agent || '—' }}</span></div>
                    <div class="sm:col-span-2">
                      <div class="mb-1 font-bold text-gray-500">Rincian perubahan:</div>
                      <pre class="max-h-56 overflow-auto rounded-xl bg-brand-950/95 p-3 text-[11px] leading-relaxed text-brand-100">{{ JSON.stringify(b.data, null, 2) }}</pre>
                    </div>
                    <div class="sm:col-span-2 break-all font-mono text-[10px] text-gray-400">hash: {{ b.hash }}</div>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-if="!baris.length && !memuat">
              <td colspan="7" class="px-4 py-10 text-center text-gray-400">Belum ada aktivitas yang cocok dengan filter.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t border-gray-100 px-4 py-3 text-sm text-gray-500">
        <span>{{ meta.total }} baris · halaman {{ meta.current_page }} dari {{ meta.last_page }}</span>
        <div class="flex gap-2">
          <button class="rounded-lg border border-gray-200 px-3 py-1.5 font-semibold disabled:opacity-40" :disabled="meta.current_page <= 1" @click="muat(meta.current_page - 1)">Sebelumnya</button>
          <button class="rounded-lg border border-gray-200 px-3 py-1.5 font-semibold disabled:opacity-40" :disabled="meta.current_page >= meta.last_page" @click="muat(meta.current_page + 1)">Berikutnya</button>
        </div>
      </div>
    </div>
  </div>
</template>
