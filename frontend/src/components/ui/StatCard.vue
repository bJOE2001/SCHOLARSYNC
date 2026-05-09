<script setup>
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
        {{ title.slice(0, 2).toUpperCase() }}
      </div>
    </div>
    <p v-if="subtitle" class="mt-4 text-sm text-slate-500">{{ subtitle }}</p>
  </article>
</template>
