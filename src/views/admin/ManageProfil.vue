<script setup>
import { ref, onMounted } from 'vue'
import { api } from '@/services/api'

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

    <p v-if="saved" class="mb-4 rounded-xl bg-brand-100 px-4 py-2.5 text-sm font-bold text-brand-700">✅ Tersimpan!</p>

    <div class="space-y-6">
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Sejarah</label>
        <p class="mb-2 text-xs text-gray-400">Boleh pakai HTML: &lt;p&gt;, &lt;b&gt;, &lt;ul&gt;&lt;li&gt;</p>
        <textarea v-model="form.sejarah" rows="10"
          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Visi</label>
        <textarea v-model="form.visi" rows="3"
          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Misi</label>
        <p class="mb-2 text-xs text-gray-400">Boleh pakai HTML: &lt;ul&gt;&lt;li&gt;…&lt;/li&gt;&lt;/ul&gt;</p>
        <textarea v-model="form.misi" rows="8"
          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></textarea>
      </div>
    </div>
  </div>
</template>
