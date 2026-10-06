<script setup>
/**
 * RichText — editor teks kaya (WYSIWYG) seperti Word.
 *
 * Admin tidak perlu tahu tag HTML: semua pengaturan dilakukan lewat tombol —
 * tebal, miring, garis bawah, perataan, penomoran angka, dan penomoran titik.
 * Hasilnya tetap HTML biasa, jadi halaman publik (prose-cms) langsung bisa
 * menampilkannya.
 */
import { onMounted, ref, watch } from 'vue'
import AppIcon from '@/components/ui/AppIcon.vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Tulis di sini…' },
  minHeight: { type: Number, default: 220 },
})
const emit = defineEmits(['update:modelValue'])

const editor = ref(null)
const fokus = ref(false)

onMounted(() => {
  if (editor.value) editor.value.innerHTML = props.modelValue || ''
  // Paragraf baru memakai <p> (HTML lebih rapi daripada <div>).
  try {
    document.execCommand('defaultParagraphSeparator', false, 'p')
  } catch (e) {
    /* diabaikan */
  }
})

// Sinkron dari luar (mis. form dibuka untuk Edit) — jangan ganggu yang sedang mengetik.
watch(
  () => props.modelValue,
  (nilai) => {
    if (!editor.value) return
    if (document.activeElement === editor.value) return
    if ((editor.value.innerHTML || '') !== (nilai || '')) editor.value.innerHTML = nilai || ''
  }
)

function kirim() {
  emit('update:modelValue', editor.value ? editor.value.innerHTML : '')
}

/** Jalankan perintah pemformatan bawaan browser. */
function jalankan(perintah, nilai = null) {
  if (!editor.value) return
  editor.value.focus()
  document.execCommand(perintah, false, nilai)
  kirim()
}

/** Tempel sebagai teks biasa — supaya HTML dari Word/Internet tidak berantakan. */
function tempel(e) {
  e.preventDefault()
  const teks = (e.clipboardData || window.clipboardData).getData('text/plain')
  document.execCommand('insertText', false, teks)
  kirim()
}

/** Buang sisa <br> atau paragraf kosong di akhir. */
function rapikan() {
  if (!editor.value) return
  editor.value.innerHTML = editor.value.innerHTML.replace(/<p><br\s*\/?><\/p>/gi, '').trim()
  kirim()
}

const ALAT = [
  [
    { cmd: 'bold', icon: 'bold', judul: 'Tebal (Ctrl+B)' },
    { cmd: 'italic', icon: 'italic', judul: 'Miring (Ctrl+I)' },
    { cmd: 'underline', icon: 'underline', judul: 'Garis bawah (Ctrl+U)' },
  ],
  [
    { cmd: 'justifyLeft', icon: 'alignLeft', judul: 'Rata kiri' },
    { cmd: 'justifyCenter', icon: 'alignCenter', judul: 'Rata tengah' },
    { cmd: 'justifyRight', icon: 'alignRight', judul: 'Rata kanan' },
    { cmd: 'justifyFull', icon: 'alignJustify', judul: 'Rata kanan-kiri' },
  ],
  [
    { cmd: 'insertOrderedList', icon: 'listOrdered', judul: 'Penomoran angka (1. 2. 3.)' },
    { cmd: 'insertUnorderedList', icon: 'list', judul: 'Penomoran titik (•)' },
  ],
  [{ cmd: 'removeFormat', icon: 'eraser', judul: 'Hapus format' }],
]

const kosong = () => !props.modelValue || props.modelValue === '<br>' || props.modelValue === '<p></p>'
</script>

<template>
  <div
    class="overflow-hidden rounded-xl border bg-white transition-colors"
    :class="fokus ? 'border-brand-500 ring-2 ring-brand-100' : 'border-gray-200'"
  >
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-1 border-b border-gray-100 bg-gray-50/80 px-2 py-1.5">
      <template v-for="(grup, gi) in ALAT" :key="gi">
        <span v-if="gi > 0" class="mx-1 h-5 w-px bg-gray-200"></span>
        <button
          v-for="a in grup"
          :key="a.cmd"
          type="button"
          :title="a.judul"
          class="grid h-8 w-8 place-items-center rounded-lg text-gray-600 transition-colors hover:bg-white hover:text-brand-700 hover:shadow-sm"
          @mousedown.prevent
          @click="jalankan(a.cmd)"
        >
          <AppIcon :name="a.icon" :size="16" />
        </button>
      </template>
    </div>

    <!-- Area tulis -->
    <div class="relative">
      <div
        ref="editor"
        class="prose-cms max-w-none px-4 py-3 text-sm leading-relaxed text-gray-700 outline-none"
        :style="{ minHeight: minHeight + 'px' }"
        contenteditable="true"
        @input="kirim"
        @paste="tempel"
        @blur="rapikan(); fokus = false"
        @focus="fokus = true"
      ></div>
      <p
        v-if="kosong() && !fokus"
        class="pointer-events-none absolute left-4 top-3 text-sm text-gray-400"
      >
        {{ placeholder }}
      </p>
    </div>

    <div class="border-t border-gray-100 bg-gray-50/60 px-3 py-1.5 text-[11px] text-gray-400">
      Gunakan tombol di atas untuk mengatur tulisan — tidak perlu menulis tag HTML.
    </div>
  </div>
</template>
