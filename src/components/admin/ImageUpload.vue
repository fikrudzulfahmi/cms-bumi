<script setup>
import { ref } from 'vue'
import { api, assetUrl } from '@/services/api'
import AppIcon from '@/components/ui/AppIcon.vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  dir: { type: String, default: 'umum' },
})
const emit = defineEmits(['update:modelValue'])

const uploading = ref(false)
const input = ref(null)
const catatan = ref('')

/** Ukuran maksimal berkas yang diterima server (5 MB). */
const BATAS_MAKS = 5 * 1024 * 1024
/** Sisi terpanjang gambar hasil kompresi. */
const SISI_MAKS = 2000
/** Kalau berkas sudah lebih kecil dari ini, tidak perlu dikompres. */
const AMBANG_KECIL = 800 * 1024

/**
 * Kecilkan foto di browser SEBELUM dikirim.
 *
 * Foto kamera HP biasanya 3-8 MB, sedangkan batas PHP di hosting (default
 * cPanel) hanya 2 MB — itulah sebab "sebagian foto gagal diunggah".
 * Setelah dikecilkan ke sisi 2000 px + JPEG 88%, foto 8 MB menjadi ±400 KB
 * sehingga selalu lolos dan tampilannya di web tetap tajam.
 */
async function kompres(file) {
  // Format yang tidak bisa digambar ke canvas (mis. GIF animasi, SVG) dilewatkan apa adanya.
  if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) return { file, dikompres: false }
  if (file.size <= AMBANG_KECIL) return { file, dikompres: false }

  const gambar = await new Promise((selesai, gagal) => {
    const img = new Image()
    img.onload = () => selesai(img)
    img.onerror = () => gagal(new Error('gagal membaca gambar'))
    img.src = URL.createObjectURL(file)
  }).catch(() => null)

  if (!gambar) return { file, dikompres: false }

  const skala = Math.min(1, SISI_MAKS / Math.max(gambar.width, gambar.height))
  // Sudah kecil dimensinya DAN ukurannya? tidak perlu digambar ulang.
  if (skala === 1 && file.size <= AMBANG_KECIL) {
    URL.revokeObjectURL(gambar.src)
    return { file, dikompres: false }
  }

  const lebar = Math.round(gambar.width * skala)
  const tinggi = Math.round(gambar.height * skala)
  const canvas = document.createElement('canvas')
  canvas.width = lebar
  canvas.height = tinggi
  canvas.getContext('2d').drawImage(gambar, 0, 0, lebar, tinggi)
  URL.revokeObjectURL(gambar.src)

  const blob = await new Promise((selesai) => canvas.toBlob(selesai, 'image/jpeg', 0.88))
  // Kalau hasilnya justru lebih besar, pakai berkas asli.
  if (!blob || blob.size >= file.size) return { file, dikompres: false }

  const nama = file.name.replace(/\.[^.]+$/, '') + '.jpg'
  return { file: new File([blob], nama, { type: 'image/jpeg' }), dikompres: true }
}

function ukuranTeks(bytes) {
  return bytes >= 1024 * 1024
    ? (bytes / 1024 / 1024).toFixed(1) + ' MB'
    : Math.round(bytes / 1024) + ' KB'
}

async function onFile(e) {
  const asli = e.target.files && e.target.files[0]
  if (!asli) return

  uploading.value = true
  catatan.value = ''
  try {
    const { file, dikompres } = await kompres(asli)

    if (file.size > BATAS_MAKS) {
      throw new Error(
        `Gambar terlalu besar (${ukuranTeks(file.size)}). Maksimal ${ukuranTeks(BATAS_MAKS)} — mohon kecilkan dulu.`
      )
    }

    const fd = new FormData()
    fd.append('file', file)
    fd.append('dir', props.dir)
    const res = await api('/admin/upload', { method: 'POST', body: fd, isForm: true })
    emit('update:modelValue', res.data.path)

    if (dikompres) {
      catatan.value = `Gambar dikecilkan otomatis: ${ukuranTeks(asli.size)} → ${ukuranTeks(file.size)}`
    }
  } catch (err) {
    alert(err.message || 'Gagal mengunggah gambar.')
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
        <div v-else class="grid h-full w-full place-items-center text-gray-300"><AppIcon name="image" :size="32" /></div>
      </div>
      <div class="flex-1 space-y-2">
        <button
          type="button"
          :disabled="uploading"
          class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-3 py-2 text-xs font-bold text-white hover:bg-brand-700 disabled:opacity-50"
          @click="input && input.click()"
        >
          <AppIcon name="upload" :size="16" />
          {{ uploading ? 'Mengunggah…' : 'Unggah Gambar' }}
        </button>
        <input ref="input" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" @change="onFile" />
        <p v-if="catatan" class="text-xs font-medium text-green-600">{{ catatan }}</p>
        <p class="text-[11px] leading-snug text-gray-400">
          Foto dari HP otomatis dikecilkan &amp; dijadikan WebP oleh server agar cepat dimuat. Maksimal 5 MB.
        </p>
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
