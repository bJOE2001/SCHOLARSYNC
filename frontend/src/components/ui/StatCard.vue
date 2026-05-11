<script setup>
import { computed } from 'vue'

const toneClasses = {
  blue: 'bg-blue-50 text-blue-700 ring-blue-100',
  indigo: 'bg-indigo-50 text-indigo-700 ring-indigo-100',
  green: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
  amber: 'bg-amber-50 text-amber-700 ring-amber-100',
  red: 'bg-rose-50 text-rose-700 ring-rose-100',
  gray: 'bg-slate-50 text-slate-700 ring-slate-100',
}

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  tone: {
    type: String,
    default: 'blue',
  },
})

const iconPaths = {
  applicants: [
    'M16 11a4 4 0 1 0-8 0',
    'M4.5 20a7.5 7.5 0 0 1 15 0',
    'M19 8.5a3 3 0 0 1 0 5.8',
    'M22 20a5.5 5.5 0 0 0-3-4.9',
  ],
  application: [
    'M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z',
    'M14 3v5h5',
    'M8 13h8',
    'M8 17h5',
  ],
  documents: [
    'M7 3h7l4 4v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z',
    'M14 3v5h4',
    'M8 13h8',
    'M8 16h6',
  ],
  compliance: [
    'M12 3 19 6v5c0 4.4-2.8 8.2-7 9.5C7.8 19.2 5 15.4 5 11V6l7-3Z',
    'm9 12 2 2 4-5',
  ],
  announcement: [
    'M4 11v4a2 2 0 0 0 2 2h2l4 3v-3h3l5 2V7l-5 2H6a2 2 0 0 0-2 2Z',
    'M8 9v8',
  ],
  scholars: [
    'M12 4 21 9l-9 5-9-5 9-5Z',
    'M6 12v3.5c0 1.7 2.7 3 6 3s6-1.3 6-3V12',
    'M21 9v6',
  ],
  risk: [
    'M10.3 4.4 2.7 17.6A2.3 2.3 0 0 0 4.7 21h14.6a2.3 2.3 0 0 0 2-3.4L13.7 4.4a2 2 0 0 0-3.4 0Z',
    'M12 9v4',
    'M12 17h.01',
  ],
  default: [
    'M4 19V5',
    'M4 19h16',
    'M8 15l3-4 3 2 4-6',
  ],
}

const cardIconPaths = computed(() => {
  const title = props.title.toLowerCase()

  if (title.includes('risk')) {
    return iconPaths.risk
  }

  if (title.includes('applicant')) {
    return iconPaths.applicants
  }

  if (title.includes('scholar')) {
    return iconPaths.scholars
  }

  if (title.includes('document')) {
    return iconPaths.documents
  }

  if (title.includes('compliance')) {
    return iconPaths.compliance
  }

  if (title.includes('announcement')) {
    return iconPaths.announcement
  }

  if (title.includes('application') || title.includes('status')) {
    return iconPaths.application
  }

  return iconPaths.default
})
</script>

<template>
  <article class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-sm font-semibold text-slate-500">{{ title }}</p>
        <p class="mt-3 text-2xl font-bold text-slate-950">{{ value }}</p>
      </div>
      <div
        class="grid h-11 w-11 place-items-center rounded-md text-sm font-bold ring-1"
        :class="toneClasses[props.tone] ?? toneClasses.blue"
      >
        <svg
          class="h-5 w-5"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <path
            v-for="path in cardIconPaths"
            :key="path"
            :d="path"
          />
        </svg>
      </div>
    </div>
    <p v-if="subtitle" class="mt-4 text-sm text-slate-500">{{ subtitle }}</p>
  </article>
</template>
