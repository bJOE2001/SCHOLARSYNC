<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import ChartPlaceholder from '../../components/charts/ChartPlaceholder.vue'
import StatCard from '../../components/ui/StatCard.vue'
import { adminNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const currentUser = getCurrentUser()
const loading = ref(true)
const errorMessage = ref('')
const dashboard = ref({
  stats: [],
})

const adminDashboardStats = computed(() => dashboard.value.stats ?? [])

async function loadDashboard() {
  loading.value = true
  errorMessage.value = ''

  try {
    dashboard.value = await api.getAdminDashboard()
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    :user-name="currentUser?.name || 'Scholarship Officer'"
    context="Administrator Dashboard"
    role-label="Scholarship Officer"
    :notification-count="0"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section v-if="loading" class="mb-5 rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
      Loading dashboard...
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
      <StatCard
        v-for="stat in adminDashboardStats"
        :key="stat.title"
        :title="stat.title"
        :value="stat.value"
        :subtitle="stat.subtitle"
        :tone="stat.tone"
      />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
      <ChartPlaceholder title="Applicant Trends" subtitle="Monthly application volume" variant="bars" />
      <ChartPlaceholder title="Approval Rate" subtitle="Approved versus reviewed" variant="donut" />
      <ChartPlaceholder title="Compliance Overview" subtitle="Scholar monitoring snapshot" variant="line" />
    </section>
  </DashboardLayout>
</template>
