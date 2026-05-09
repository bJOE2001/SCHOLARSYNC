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

function isActive(item) {
  return route.path === item.to || (item.to !== '/login' && route.path.startsWith(`${item.to}/`))
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
          <span class="min-w-0 truncate">{{ item.label }}</span>
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
