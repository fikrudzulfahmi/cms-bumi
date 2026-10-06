<script setup>
import { ref, onMounted } from 'vue'
import { api } from '@/services/api'
import AppIcon from '@/components/ui/AppIcon.vue'
import RichText from '@/components/admin/RichText.vue'

const form = ref({ sejarah: '', visi: '', misi: '' })
const saving = ref(false)
const saved = ref(false)

onMounted(async () => {
  const d = (await api('/profil')).data
  form.value = { sejarah: d.sejarah || '', visi: d.visi || '', misi: d.misi || '' }
})

async function save() {
  saving.value = true
  saved.value = false
  try {
    await api('/admin/profil', { method: 'PUT', body: form.value })
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
        <h1 class="text-2xl font-extrabold text-brand-950">Profil Sekolah</h1>
        <p class="text-sm text-gray-500">Sejarah, visi, dan misi madrasah.</p>
      </div>
      <button :disabled="saving"
        class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 disabled:opacity-50"
        @click="save">
        {{ saving ? 'Menyimpan…' : 'Simpan' }}
      </button>
    </div>

    <p v-if="saved" class="mb-4 flex items-center gap-2 rounded-xl bg-brand-100 px-4 py-2.5 text-sm font-bold text-brand-700">
      <AppIcon name="check" :size="16" /> Tersimpan!
    </p>

    <div class="space-y-6">
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Sejarah</label>
        <RichText
          v-model="form.sejarah"
          :min-height="240"
          placeholder="Tulis sejarah madrasah di sini. Pakai tombol di atas untuk menebalkan, mengatur perataan, atau membuat daftar bernomor."
        />
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Visi</label>
        <RichText
          v-model="form.visi"
          :min-height="110"
          placeholder="Tulis visi madrasah."
        />
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Misi</label>
        <RichText
          v-model="form.misi"
          :min-height="190"
          placeholder="Tulis misi madrasah. Gunakan tombol penomoran titik atau angka untuk membuat daftar."
        />
      </div>
    </div>
  </div>
</template>
