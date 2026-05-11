<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    required: true,
  },
  message: {
    type: String,
    default: '',
  },
  confirmLabel: {
    type: String,
    default: 'Confirm',
  },
  cancelLabel: {
    type: String,
    default: 'Cancel',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  confirmDisabled: {
    type: Boolean,
    default: false,
  },
  tone: {
    type: String,
    default: 'primary',
  },
  showDelay: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['confirm', 'cancel', 'close'])
const titleId = `confirmation-dialog-title-${Math.random().toString(36).slice(2, 9)}`
const isVisible = ref(false)
let showTimer = null

const confirmButtonClass = computed(() => {
  if (props.tone === 'danger') {
    return 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-200 disabled:bg-slate-300'
  }

  if (props.tone === 'warning') {
    return 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-200 disabled:bg-slate-300'
  }

  return 'bg-indigo-700 hover:bg-indigo-800 focus:ring-indigo-200 disabled:bg-slate-300'
})

function closeDialog() {
  if (props.loading) {
    return
  }

  emit('close')
}

function cancelDialog() {
  if (props.loading) {
    return
  }

  emit('cancel')
  emit('close')
}

function handleEscape(event) {
  if (event.key === 'Escape' && isVisible.value) {
    closeDialog()
  }
}

function clearShowTimer() {
  if (!showTimer) {
    return
  }

  window.clearTimeout(showTimer)
  showTimer = null
}

watch(() => props.show, (show) => {
  clearShowTimer()

  if (!show) {
    isVisible.value = false
    return
  }

  if (props.showDelay <= 0) {
    isVisible.value = true
    return
  }

  showTimer = window.setTimeout(() => {
    isVisible.value = true
    showTimer = null
  }, props.showDelay)
}, { immediate: true })

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  clearShowTimer()
  window.removeEventListener('keydown', handleEscape)
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isVisible"
        tabindex="-1"
        class="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-950/50 p-4"
        aria-modal="true"
        role="dialog"
        :aria-labelledby="titleId"
        @click.self="closeDialog"
      >
        <Transition
          appear
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="translate-y-3 scale-95 opacity-0"
          enter-to-class="translate-y-0 scale-100 opacity-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="translate-y-0 scale-100 opacity-100"
          leave-to-class="translate-y-2 scale-95 opacity-0"
        >
          <div class="relative max-h-full w-full max-w-md">
            <div class="relative rounded-md bg-white shadow-xl">
              <button
                type="button"
                class="absolute end-2.5 top-3 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-md bg-transparent text-sm text-slate-400 hover:bg-slate-100 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-50"
                aria-label="Close modal"
                :disabled="loading"
                @click="closeDialog"
              >
                <svg class="h-3 w-3" aria-hidden="true" viewBox="0 0 14 14" fill="none">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
              </button>

              <div class="p-4 text-center md:p-5">
                <svg class="mx-auto mb-4 h-12 w-12 text-amber-500" aria-hidden="true" viewBox="0 0 20 20" fill="none">
                  <path
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M10 7v3.5m0 3h.01M8.3 3.4 2.2 14a1.7 1.7 0 0 0 1.5 2.5h12.6a1.7 1.7 0 0 0 1.5-2.5L11.7 3.4a1.95 1.95 0 0 0-3.4 0Z"
                  />
                </svg>
                <h3 :id="titleId" class="mb-2 text-lg font-bold text-slate-950">
                  {{ title }}
                </h3>
                <p v-if="message" class="mb-5 text-sm leading-6 text-slate-500">
                  {{ message }}
                </p>
                <div v-if="$slots.default" class="mb-5 text-left">
                  <slot></slot>
                </div>
                <div class="flex flex-col justify-center gap-2 sm:flex-row">
                  <button
                    type="button"
                    class="rounded-md border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring-4 focus:ring-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="loading"
                    @click="cancelDialog"
                  >
                    {{ cancelLabel }}
                  </button>
                  <button
                    type="button"
                    class="rounded-md px-5 py-2.5 text-sm font-bold text-white focus:outline-none focus:ring-4 disabled:cursor-not-allowed"
                    :class="confirmButtonClass"
                    :disabled="loading || confirmDisabled"
                    @click="emit('confirm')"
                  >
                    {{ loading ? 'Please wait...' : confirmLabel }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
