<script setup>
import { ref, onMounted } from 'vue'
import { api } from '@/services/api'
import ImageUpload from '@/components/admin/ImageUpload.vue'
import AppIcon from '@/components/ui/AppIcon.vue'
import { useCmsStore } from '@/stores/cms'

const cms = useCmsStore()

const form = ref({})
const saving = ref(false)
const saved = ref(false)

const sections = [
  {
    title: 'Identitas Sekolah',
    fields: [
      { key: 'nama_sekolah', label: 'Nama Sekolah', type: 'text' },
      { key: 'nama_panjang', label: 'Nama Panjang', type: 'text' },
      { key: 'akronim', label: 'Akronim', type: 'text' },
      { key: 'motto', label: 'Motto', type: 'text' },
      { key: 'tagline', label: 'Tagline', type: 'text' },
      { key: 'deskripsi_singkat', label: 'Deskripsi Singkat', type: 'textarea' },
    ],
  },
  {
    title: 'Kontak & Alamat',
    fields: [
      { key: 'alamat', label: 'Alamat', type: 'text' },
      { key: 'telepon', label: 'Telepon', type: 'text' },
      { key: 'whatsapp', label: 'WhatsApp (format 628xxx)', type: 'text' },
      { key: 'email', label: 'Email', type: 'text' },
      { key: 'jam_operasional', label: 'Jam Operasional', type: 'text' },
    ],
  },
  {
    title: 'Statistik',
    fields: [
      { key: 'tahun_berdiri', label: 'Tahun Berdiri', type: 'text' },
      { key: 'akreditasi', label: 'Akreditasi', type: 'text' },
      { key: 'jumlah_siswa', label: 'Jumlah Siswa', type: 'text' },
      { key: 'jumlah_guru', label: 'Jumlah Guru', type: 'text' },
      { key: 'jumlah_ekstra', label: 'Jumlah Ekstrakurikuler', type: 'text' },
    ],
  },
  {
    title: 'Informasi Pendaftaran (PPDB)',
    fields: [
      { key: 'informasi_pendaftaran', label: 'Informasi Pendaftaran (boleh HTML)', type: 'richtext' },
    ],
  },
  {
    title: 'Media Sosial',
    fields: [
      { key: 'sosmed_facebook', label: 'Facebook URL', type: 'text' },
      { key: 'sosmed_instagram', label: 'Instagram URL', type: 'text' },
      { key: 'sosmed_youtube', label: 'YouTube URL', type: 'text' },
      { key: 'sosmed_tiktok', label: 'TikTok URL', type: 'text' },
    ],
  },
]

onMounted(async () => {
  form.value = (await api('/settings')).data || {}
})

async function save() {
  saving.value = true
  saved.value = false
  try {
    await api('/admin/settings', { method: 'PUT', body: { settings: form.value } })
    await cms.reload() // logo & identitas langsung ter-update di navbar/footer
    saved.value = true
    setTimeout(() => (saved.value = false), 2500)
  } catch (e) {
    alert(e.message)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-brand-950">Pengaturan</h1>
        <p class="text-sm text-gray-500">Informasi umum dan identitas sekolah.</p>
      </div>
      <button :disabled="saving"
        class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 disabled:opacity-50"
        @click="save">
        {{ saving ? 'Menyimpan…' : 'Simpan Perubahan' }}
      </button>
    </div>

    <p v-if="saved" class="mb-4 flex items-center gap-2 rounded-xl bg-brand-100 px-4 py-2.5 text-sm font-bold text-brand-700">
      <AppIcon name="check" :size="16" /> Tersimpan!
    </p>

    <div class="space-y-6">
      <div v-for="s in sections" :key="s.title" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-extrabold text-brand-900">{{ s.title }}</h2>
        <div class="grid gap-4 sm:grid-cols-2">
          <div v-for="f in s.fields" :key="f.key" :class="f.type === 'richtext' || f.type === 'textarea' ? 'sm:col-span-2' : ''">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">{{ f.label }}</label>
            <textarea
              v-if="f.type === 'textarea' || f.type === 'richtext'"
              v-model="form[f.key]"
              :rows="f.type === 'richtext' ? 8 : 3"
              class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            ></textarea>
            <input
              v-else
              v-model="form[f.key]"
              type="text"
              class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
            />
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-extrabold text-brand-900">Logo</h2>
        <ImageUpload v-model="form.logo" dir="logo" />
      </div>
    </div>
  </div>
</template>
