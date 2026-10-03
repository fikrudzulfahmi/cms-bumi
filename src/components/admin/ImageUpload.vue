<script setup>
import { ref } from 'vue'
import { api, assetUrl } from '@/services/api'

const props = defineProps({
  modelValue: { type: String, default: '' },
  dir: { type: String, default: 'umum' },
})
const emit = defineEmits(['update:modelValue'])

const uploading = ref(false)
const input = ref(null)

async function onFile(e) {
  const file = e.target.files && e.target.files[0]
  if (!file) return
  uploading.value = true
  try {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('dir', props.dir)
    const res = await api('/admin/upload', { method: 'POST', body: fd, isForm: true })
    emit('update:modelValue', res.data.path)
  } catch (err) {
    alert(err.message)
  } finally {
    uploading.value = false
    if (input.value) input.value.value = ''
  }
}
</script>

<template>
  <div>
    <div class="flex items-start gap-3">
      <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
        <img v-if="modelValue" :src="assetUrl(modelValue)" class="h-full w-full object-cover" />
        <div v-else class="grid h-full w-full place-items-center text-3xl text-gray-300">🖼️</div>
      </div>
      <div class="flex-1 space-y-2">
        <button
          type="button"
          :disabled="uploading"
          class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-3 py-2 text-xs font-bold text-white hover:bg-brand-700 disabled:opacity-50"
          @click="input && input.click()"
        >
          {{ uploading ? 'Mengunggah…' : '⬆️ Unggah Gambar' }}
        </button>
        <input ref="input" type="file" accept="image/*" class="hidden" @change="onFile" />
        <button
          v-if="modelValue"
          type="button"
          class="block text-xs font-semibold text-red-500 hover:underline"
          @click="emit('update:modelValue', '')"
        >
          Hapus gambar
        </button>
        <input
          type="text"
          :value="modelValue"
          placeholder="atau tempel URL/path gambar"
          class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs"
          @input="emit('update:modelValue', $event.target.value)"
        />
      </div>
    </div>
  </div>
</template>
