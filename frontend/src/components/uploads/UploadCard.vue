<script setup>
import { ref } from 'vue'
import StatusBadge from '../ui/StatusBadge.vue'

const props = defineProps({
  documentType: {
    type: String,
    required: true,
  },
  fileName: {
    type: String,
    required: true,
  },
  status: {
    type: String,
    required: true,
  },
})

const selectedFile = ref('')

function handleFileUpload(event) {
  const input = event.target
  selectedFile.value = input.files?.[0]?.name ?? ''
}
</script>

<template>
  <article class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h3 class="text-base font-bold text-slate-950">{{ documentType }}</h3>
        <p class="mt-2 break-all text-sm text-slate-500">
          {{ selectedFile || props.fileName || 'No file selected' }}
        </p>
      </div>
      <StatusBadge :status="selectedFile ? 'Uploaded' : status" />
    </div>

    <label class="mt-5 inline-flex cursor-pointer items-center justify-center rounded-md bg-indigo-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-800">
      Upload
      <input class="sr-only" type="file" @change="handleFileUpload" />
    </label>
  </article>
</template>
