<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'
import { apiLoadingState } from '../../services/api'

const visible = ref(false)
const minimumVisibleMs = 550
let visibleStartedAt = 0
let hideTimer = null

watch(
  () => apiLoadingState.active,
  (active) => {
    if (active) {
      if (hideTimer) {
        window.clearTimeout(hideTimer)
        hideTimer = null
      }

      visibleStartedAt = Date.now()
      visible.value = true
      return
    }

    const elapsed = Date.now() - visibleStartedAt
    const remaining = Math.max(minimumVisibleMs - elapsed, 0)

    hideTimer = window.setTimeout(() => {
      visible.value = false
      hideTimer = null
    }, remaining)
  },
  { immediate: true }
)

onBeforeUnmount(() => {
  if (hideTimer) {
    window.clearTimeout(hideTimer)
  }
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="visible"
      class="fixed left-0 top-0 z-[90] h-1 w-full overflow-hidden bg-indigo-100"
      role="progressbar"
      aria-label="Loading"
    >
      <div class="loading-line absolute left-0 top-0 h-full bg-indigo-700"></div>
    </div>
  </Teleport>
</template>

<style scoped>
.loading-line {
  width: 42%;
  animation: loading-line 0.9s ease-in-out infinite;
}

@keyframes loading-line {
  0% {
    transform: translateX(-110vw);
  }

  55% {
    transform: translateX(35vw);
  }

  100% {
    transform: translateX(115vw);
  }
}
</style>
