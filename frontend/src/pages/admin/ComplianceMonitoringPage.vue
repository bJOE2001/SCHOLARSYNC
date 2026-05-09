<script setup>
import { computed, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import DataTable from '../../components/ui/DataTable.vue'
import StatCard from '../../components/ui/StatCard.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { adminNavigation } from '../../data/navigation'
import { complianceRecords } from '../../data/sampleData'

const columns = [
  { key: 'scholarName', label: 'Scholar Name' },
  { key: 'gpa', label: 'GPA' },
  { key: 'complianceScore', label: 'Attendance / Compliance Score' },
  { key: 'complianceStatus', label: 'Compliance Status' },
  { key: 'riskLevel', label: 'Risk Level' },
]

const searchQuery = ref('')
const selectedStatus = ref('All')
const selectedRisk = ref('All')

const statusOptions = ['All', 'Compliant', 'Needs Monitoring', 'At Risk']
const riskOptions = ['All', 'Low', 'Medium', 'High']

const filteredComplianceRecords = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()

  return complianceRecords.filter((record) => {
    const matchesSearch = record.scholarName.toLowerCase().includes(query)
    const matchesStatus = selectedStatus.value === 'All' || record.complianceStatus === selectedStatus.value
    const matchesRisk = selectedRisk.value === 'All' || record.riskLevel === selectedRisk.value

    return matchesSearch && matchesStatus && matchesRisk
  })
})

const summaryCards = [
  { title: 'Compliant Scholars', value: '594', subtitle: 'Good academic standing', tone: 'green' },
  { title: 'Needs Monitoring', value: '43', subtitle: 'Watchlist this month', tone: 'amber' },
  { title: 'High Risk', value: '18', subtitle: 'Requires intervention', tone: 'red' },
]
</script>

<template>
  <DashboardLayout
    sidebar-title="ScholarSync"
    sidebar-subtitle="Officer Portal"
    :sidebar-items="adminNavigation"
    user-name="Dr. Camille Navarro"
    context="Compliance Monitoring"
    role-label="Scholarship Officer"
    :notification-count="8"
  >
    <section class="grid gap-5 md:grid-cols-3">
      <StatCard
        v-for="card in summaryCards"
        :key="card.title"
        :title="card.title"
        :value="card.value"
        :subtitle="card.subtitle"
        :tone="card.tone"
      />
    </section>

    <section class="mt-6 rounded-md border border-slate-200 bg-white p-5 shadow-sm">
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
      <DataTable
        :columns="columns"
        :rows="filteredComplianceRecords"
        :initial-per-page="2"
        :per-page-options="[2, 4, 6]"
        empty-text="No compliance records found."
      >
        <template #cell-complianceStatus="{ value }">
          <StatusBadge :status="String(value)" />
        </template>
        <template #cell-riskLevel="{ value }">
          <StatusBadge :status="String(value)" />
        </template>
      </DataTable>
    </section>
  </DashboardLayout>
</template>
