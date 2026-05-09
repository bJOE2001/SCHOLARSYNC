<script setup>
const props = defineProps({
  status: {
    type: String,
    required: true,
  },
})

function badgeClass(status) {
  const normalized = status.toLowerCase()

  if (normalized.includes('approved') || normalized.includes('verified') || normalized.includes('completed') || normalized === 'low') {
    return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
  }

  if (normalized.includes('review') || normalized.includes('uploaded') || normalized.includes('current') || normalized === 'medium') {
    return 'bg-blue-50 text-blue-700 ring-blue-200'
  }

  if (normalized.includes('revision') || normalized.includes('pending') || normalized.includes('monitoring')) {
    return 'bg-amber-50 text-amber-700 ring-amber-200'
  }

  if (normalized.includes('rejected') || normalized.includes('risk') || normalized === 'high') {
    return 'bg-rose-50 text-rose-700 ring-rose-200'
  }

  return 'bg-slate-50 text-slate-700 ring-slate-200'
}
</script>

<template>
  <span
    class="inline-flex items-center rounded-md px-3 py-1 text-xs font-bold ring-1"
    :class="badgeClass(props.status)"
  >
    {{ status }}
  </span>
</template>
