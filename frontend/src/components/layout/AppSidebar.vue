<script setup>
import { RouterLink, useRoute } from 'vue-router'

defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    required: true,
  },
  items: {
    type: Array,
    required: true,
  },
  isOpen: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits(['close'])

const route = useRoute()

const iconPaths = {
  dashboard: [
    'M3 10.5 12 3l9 7.5',
    'M5 9.5V20h5v-6h4v6h5V9.5',
  ],
  application: [
    'M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z',
    'M14 3v5h5',
    'M8 13h8',
    'M8 17h6',
  ],
  upload: [
    'M12 16V7',
    'M8.5 10.5 12 7l3.5 3.5',
    'M5 17.5a4 4 0 0 1 1.3-7.8A6 6 0 0 1 18 11a3.5 3.5 0 0 1 1 6.8',
    'M8 21h8',
  ],
  status: [
    'M9 11.5 11 13.5 15.5 9',
    'M5 4h14v16H5z',
    'M8 17h8',
  ],
  scholarship: [
    'M12 3 20 7l-8 4-8-4 8-4Z',
    'M6 9.5V14c0 2 2.7 3.5 6 3.5s6-1.5 6-3.5V9.5',
    'M20 7v5',
  ],
  documents: [
    'M7 3h7l4 4v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z',
    'M14 3v5h4',
    'M8 13h8',
    'M8 16h6',
  ],
  scholars: [
    'M12 4 21 9l-9 5-9-5 9-5Z',
    'M6 12v3.5c0 1.7 2.7 3 6 3s6-1.3 6-3V12',
    'M21 9v6',
  ],
  compliance: [
    'M12 3 19 6v5c0 4.4-2.8 8.2-7 9.5C7.8 19.2 5 15.4 5 11V6l7-3Z',
    'm9 12 2 2 4-5',
  ],
  analytics: [
    'M4 19V5',
    'M4 19h16',
    'M8 15l3-4 3 2 4-6',
  ],
  reports: [
    'M6 3h9l3 3v15H6z',
    'M15 3v4h4',
    'M9 17v-4',
    'M12 17v-7',
    'M15 17v-3',
  ],
  announcements: [
    'M4 11v4a2 2 0 0 0 2 2h2l4 3v-3h3l5 2V7l-5 2H6a2 2 0 0 0-2 2Z',
    'M8 9v8',
  ],
}

function isActive(item) {
  return route.path === item.to || (item.to !== '/login' && route.path.startsWith(`${item.to}/`))
}

function navigationIconPaths(item) {
  return iconPaths[item.icon] ?? iconPaths.dashboard
}
</script>

<template>
  <div class="lg:sticky lg:top-0 lg:h-screen lg:w-72 lg:shrink-0">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-30 bg-slate-950/40 lg:hidden"
      aria-hidden="true"
      @click="emit('close')"
    ></div>

    <aside
      class="fixed inset-y-0 left-0 z-40 flex w-72 transform flex-col border-r border-slate-200 bg-white shadow-2xl shadow-slate-950/10 transition duration-200 lg:static lg:h-screen lg:translate-x-0 lg:shadow-none"
      :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
      aria-label="Sidebar navigation"
    >
      <div class="flex min-h-24 items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
        <div class="flex min-w-0 items-center gap-3">
          <div class="grid h-11 w-11 shrink-0 place-items-center rounded-md bg-indigo-700 text-sm font-bold text-white shadow-sm">
            SS
          </div>
          <div class="min-w-0">
            <p class="truncate text-base font-bold text-slate-950">{{ title }}</p>
            <p class="truncate text-xs font-medium text-slate-500">{{ subtitle }}</p>
          </div>
        </div>

        <button
          type="button"
          class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:hidden"
          aria-label="Close navigation"
          @click="emit('close')"
        >
          <span class="relative h-5 w-5" aria-hidden="true">
            <span class="absolute left-1/2 top-1/2 h-0.5 w-5 -translate-x-1/2 -translate-y-1/2 rotate-45 rounded-md bg-current"></span>
            <span class="absolute left-1/2 top-1/2 h-0.5 w-5 -translate-x-1/2 -translate-y-1/2 -rotate-45 rounded-md bg-current"></span>
          </span>
        </button>
      </div>

      <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-4 py-5">
        <RouterLink
          v-for="item in items"
          :key="item.label"
          :to="item.to"
          class="flex min-h-11 items-center justify-between gap-3 rounded-md px-4 py-3 text-sm font-semibold transition"
          :class="[
            isActive(item)
              ? 'bg-indigo-700 text-white shadow-sm shadow-indigo-700/20'
              : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950',
          ]"
          @click="emit('close')"
        >
          <span class="flex min-w-0 items-center gap-3">
            <svg
              class="h-5 w-5 shrink-0"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              aria-hidden="true"
            >
              <path
                v-for="path in navigationIconPaths(item)"
                :key="path"
                :d="path"
              />
            </svg>
            <span class="min-w-0 truncate">{{ item.label }}</span>
          </span>
          <span
            v-if="isActive(item)"
            class="h-2 w-2 shrink-0 rounded-md bg-white"
            aria-hidden="true"
          ></span>
        </RouterLink>
      </nav>

      <div class="shrink-0 border-t border-slate-200 p-5">
        <div class="rounded-md bg-slate-50 p-4">
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">ScholarSync</p>
          <p class="mt-2 text-sm text-slate-600">
            Scholarship management, monitoring, and reporting workspace.
          </p>
        </div>
      </div>
    </aside>
  </div>
</template>
