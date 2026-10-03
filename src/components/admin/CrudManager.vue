<script setup>
import { ref, onMounted } from 'vue'
import { api, assetUrl } from '@/services/api'
import ImageUpload from './ImageUpload.vue'
import { formatDate, stripHtml } from '@/utils/format'

const props = defineProps({
  endpoint: { type: String, required: true },
  title: { type: String, required: true },
  fields: { type: Array, required: true },
  columns: { type: Array, required: true },
})

const items = ref([])
const loading = ref(false)
const showForm = ref(false)
const editing = ref(null)
const form = ref({})
const saving = ref(false)
const search = ref('')

async function load() {
  loading.value = true
  try {
    const res = await api(`/admin/${props.endpoint}`)
    items.value = res.data || []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = null
  form.value = {}
  props.fields.forEach((f) => {
    form.value[f.name] = f.type === 'boolean' ? true : ''
  })
  showForm.value = true
}

function openEdit(item) {
  editing.value = item
  form.value = { ...item }
  props.fields.forEach((f) => {
    if (f.type === 'date' && form.value[f.name]) {
      form.value[f.name] = String(form.value[f.name]).slice(0, 10)
    }
  })
  showForm.value = true
}

async function save() {
  saving.value = true
  try {
    const payload = { ...form.value }
    props.fields.forEach((f) => {
      if (f.type !== 'boolean' && payload[f.name] === '') payload[f.name] = null
    })
    if (editing.value) {
      await api(`/admin/${props.endpoint}/${editing.value.id}`, { method: 'PUT', body: payload })
    } else {
      await api(`/admin/${props.endpoint}`, { method: 'POST', body: payload })
    }
    showForm.value = false
    await load()
  } catch (err) {
    alert(err.message)
  } finally {
    saving.value = false
  }
}

async function remove(item) {
  if (!confirm('Hapus data ini?')) return
  try {
    await api(`/admin/${props.endpoint}/${item.id}`, { method: 'DELETE' })
    await load()
  } catch (err) {
    alert(err.message)
  }
}

function field(name) {
  return props.fields.find((f) => f.name === name)
}

function display(item, name) {
  const f = field(name)
  const v = item[name]
  if (!f) return v ?? '—'
  if (f.type === 'image') {
    return v
      ? `<img src="${assetUrl(v)}" class="h-10 w-10 rounded-lg object-cover" />`
      : '<span class="text-gray-300">—</span>'
  }
  if (f.type === 'boolean') return v ? '✅' : '—'
  if (f.type === 'select') {
    const opt = (f.options || []).find((o) => o.value === v)
    return opt ? opt.label : (v || '—')
  }
  if (f.type === 'date') return formatDate(v)
  if (v === null || v === undefined || v === '') return '—'
  return stripHtml(String(v)).slice(0, 70)
}

onMounted(load)
</script>

<template>
  <div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold text-brand-950">{{ title }}</h1>
        <p class="text-sm text-gray-500">Kelola data {{ title.toLowerCase() }} madrasah.</p>
      </div>
      <button
        class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition-transform hover:scale-[1.03]"
        @click="openCreate"
      >
        + Tambah
      </button>
    </div>

    <!-- Tabel -->
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
      <div v-if="loading" class="py-16 text-center text-gray-400">Memuat…</div>
      <table v-else class="w-full text-left text-sm">
        <thead class="bg-brand-50 text-xs uppercase tracking-wide text-brand-700">
          <tr>
            <th v-for="c in columns" :key="c" class="px-4 py-3 font-bold">{{ (field(c) && field(c).label) || c }}</th>
            <th class="px-4 py-3 text-right font-bold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="item in items" :key="item.id" class="hover:bg-brand-50/40">
            <td v-for="c in columns" :key="c" class="px-4 py-3 align-middle text-gray-700">
              <span v-if="field(c) && field(c).type === 'image'" v-html="display(item, c)"></span>
              <span v-else>{{ display(item, c) }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <button class="rounded-lg bg-brand-100 px-3 py-1.5 text-xs font-bold text-brand-700 hover:bg-brand-200" @click="openEdit(item)">Edit</button>
              <button class="ml-2 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-100" @click="remove(item)">Hapus</button>
            </td>
          </tr>
          <tr v-if="!items.length">
            <td :colspan="columns.length + 1" class="px-4 py-16 text-center text-gray-400">Belum ada data.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form -->
    <div v-if="showForm" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-brand-950/50 p-4 backdrop-blur-sm">
      <div class="my-8 w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="text-xl font-extrabold text-brand-950">{{ editing ? 'Edit' : 'Tambah' }} {{ title }}</h2>
          <button class="grid h-9 w-9 place-items-center rounded-lg hover:bg-gray-100" @click="showForm = false">✕</button>
        </div>

        <div class="grid gap-4">
          <template v-for="f in fields" :key="f.name">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-700">
                {{ f.label }} <span v-if="f.required" class="text-red-500">*</span>
              </label>

              <input
                v-if="f.type === 'text' || f.type === 'number'"
                v-model="form[f.name]"
                :type="f.type === 'number' ? 'number' : 'text'"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
              />

              <input
                v-else-if="f.type === 'date'"
                v-model="form[f.name]"
                type="date"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
              />

              <textarea
                v-else-if="f.type === 'textarea' || f.type === 'richtext'"
                v-model="form[f.name]"
                :rows="f.type === 'richtext' ? 8 : 4"
                :placeholder="f.placeholder || (f.type === 'richtext' ? 'Boleh pakai HTML sederhana: <p>, <b>, <ul><li>' : '')"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
              ></textarea>

              <select
                v-else-if="f.type === 'select'"
                v-model="form[f.name]"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none"
              >
                <option v-for="o in f.options" :key="o.value" :value="o.value">{{ o.label }}</option>
              </select>

              <label v-else-if="f.type === 'boolean'" class="flex cursor-pointer items-center gap-3">
                <input v-model="form[f.name]" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                <span class="text-sm font-medium text-gray-700">Ya, publikasikan</span>
              </label>

              <ImageUpload v-else-if="f.type === 'image'" v-model="form[f.name]" :dir="f.dir || 'umum'" />
            </div>
          </template>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button class="rounded-xl px-5 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-100" @click="showForm = false">Batal</button>
          <button
            :disabled="saving"
            class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 disabled:opacity-50"
            @click="save"
          >
            {{ saving ? 'Menyimpan…' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
