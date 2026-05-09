<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { api, getCurrentUser } from '../../services/api'

const searchQuery = ref('')
const selectedStatus = ref('All')
const selectedRisk = ref('All')
const complianceRecords = ref([])
const loading = ref(true)
const errorMessage = ref('')
const currentUser = getCurrentUser()

const statusOptions = ['All', 'Compliant', 'Needs Monitoring', 'At Risk']
const riskOptions = ['All', 'Low', 'Medium', 'High']

const columns = [
  { key: 'scholarName', label: 'Scholar Name' },
  { key: 'gpa', label: 'GPA' },
  { key: 'complianceScore', label: 'Compliance Score' },
  { key: 'complianceStatus', label: 'Status' },
  { key: 'riskLevel', label: 'Risk Level' },
]

const filteredScholars = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()

  return complianceRecords.value.filter((record) => {
    const matchesSearch = record.scholarName.toLowerCase().includes(query)
    const matchesStatus = selectedStatus.value === 'All' || record.complianceStatus === selectedStatus.value
    const matchesRisk = selectedRisk.value === 'All' || record.riskLevel === selectedRisk.value

    return matchesSearch && matchesStatus && matchesRisk
  })
})

const averageGpa = computed(() => {
  if (!filteredScholars.value.length) {
    return '0.00'
  }

  const total = filteredScholars.value.reduce((sum, record) => sum + Number(record.gpa), 0)

  return (total / filteredScholars.value.length).toFixed(2)
})

const highRiskCount = computed(() => {
  return filteredScholars.value.filter((record) => record.riskLevel === 'High').length
})

async function loadScholars() {
  loading.value = true
  errorMessage.value = ''

  try {
    complianceRecords.value = await api.listComplianceRecords()
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}

onMounted(loadScholars)
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    :user-name="currentUser?.name || 'Scholarship Officer'"
    context="Scholars"
    role-label="Scholarship Officer"
    :notification-count="0"
  >
    <section v-if="errorMessage" class="mb-5 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
      {{ errorMessage }}
    </section>

    <section class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
      <div class="grid gap-4 lg:grid-cols-[1fr_220px_180px]">
        <label class="block">
          <span class="mb-2 block text-sm font-bold text-slate-700">Search scholars</span>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search by scholar name"
            class="w-full rounded-md border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
          />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-bold text-slate-700">Compliance Status</span>
          <select
            v-model="selectedStatus"
            class="w-full rounded-md border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
          >
            <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
          </select>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-bold text-slate-700">Risk Level</span>
          <select
            v-model="selectedRisk"
            class="w-full rounded-md border border-slate-200 px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
          >
            <option v-for="risk in riskOptions" :key="risk" :value="risk">{{ risk }}</option>
          </select>
        </label>

      </div>
    </section>

    <section class="mt-6">
      <p v-if="loading" class="rounded-md border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-500 shadow-sm">
        Loading scholars...
      </p>

      <DataTable
        v-else
        :columns="columns"
        :rows="filteredScholars"
        :initial-per-page="2"
        :per-page-options="[2, 4, 6]"
        empty-text="No scholars found."
      >
        <template #cell-complianceStatus="{ value }">
          <StatusBadge :status="String(value)" />
        </template>
        <template #cell-riskLevel="{ value }">
          <StatusBadge :status="String(value)" />
        </template>
        <template #footer-summary>
          <span>
            Average GPA: <strong class="text-slate-950">{{ averageGpa }}</strong>
            · High Risk: <strong class="text-slate-950">{{ highRiskCount }}</strong>
          </span>
        </template>
      </DataTable>
    </section>
  </DashboardLayout>
</template>
