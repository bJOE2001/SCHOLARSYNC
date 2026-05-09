<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

type ApiPayload = {
  status: string
  message: string
}

const configuredApiUrl = import.meta.env.VITE_API_URL?.replace(/\/$/, '')
const apiUrl = computed(() => {
  if (!configuredApiUrl && import.meta.env.DEV) {
    return '/api/test'
  }

  return `${configuredApiUrl || 'http://127.0.0.1:8000'}/api/test`
})

const isLoading = ref(false)
const apiResponse = ref<ApiPayload | null>(null)
const error = ref('')
const lastChecked = ref('')

const formattedResponse = computed(() => {
  if (error.value) {
    return error.value
  }

  return apiResponse.value ? JSON.stringify(apiResponse.value, null, 2) : 'Waiting for the first API check.'
})

const connectionState = computed(() => {
  if (error.value) {
    return 'Error'
  }

  return apiResponse.value?.status === 'ok' ? 'Connected' : 'Checking'
})

async function checkApi() {
  isLoading.value = true
  error.value = ''

  try {
    const response = await fetch(apiUrl.value, {
      headers: {
        Accept: 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`)
    }

    apiResponse.value = await response.json()
    lastChecked.value = new Date().toLocaleTimeString()
  } catch (requestError) {
    apiResponse.value = null
    lastChecked.value = new Date().toLocaleTimeString()
    error.value = requestError instanceof Error ? requestError.message : 'Unable to reach the API.'
  } finally {
    isLoading.value = false
  }
}

onMounted(checkApi)
</script>

<template>
  <main class="shell">
    <header class="topbar">
      <div class="brand">
        <div class="brand-mark" aria-hidden="true">SS</div>
        <div>
          <h1>ScholarSync</h1>
          <p>Laravel API plus Vue frontend</p>
        </div>
      </div>

      <div class="status-pill">
        <span
          class="status-dot"
          :class="{ ok: apiResponse?.status === 'ok', error: Boolean(error) }"
          aria-hidden="true"
        ></span>
        {{ connectionState }}
      </div>
    </header>

    <section class="dashboard" aria-label="Application status">
      <div class="panel panel-main">
        <p class="eyebrow">Local Stack</p>
        <h2 class="headline">Your ScholarSync starter is ready to talk to Laravel.</h2>
        <p class="lead">
          The Vue app checks the Laravel test endpoint and shows the live JSON response here.
        </p>

        <div class="actions">
          <button class="button" type="button" :disabled="isLoading" @click="checkApi">
            {{ isLoading ? 'Checking...' : 'Check API' }}
          </button>
          <span class="endpoint">{{ apiUrl }}</span>
        </div>

        <div class="response" :class="{ error: Boolean(error) }">
          <h2>API Response</h2>
          <pre>{{ formattedResponse }}</pre>
        </div>
      </div>

      <aside class="panel panel-side" aria-label="Connection details">
        <div>
          <p class="metric-label">Backend</p>
          <p class="metric-value">Laravel 12</p>
        </div>
        <div>
          <p class="metric-label">Frontend</p>
          <p class="metric-value">Vue 3</p>
        </div>
        <div>
          <p class="metric-label">Last Checked</p>
          <p class="metric-value">{{ lastChecked || 'Not yet' }}</p>
        </div>
      </aside>
    </section>
  </main>
</template>
